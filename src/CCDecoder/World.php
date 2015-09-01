<?php

namespace limitium\TAPD\CCDecoder;


use limitium\TAPD\Util\Curler;
use stdClass;

class World
{
    public $players = array();
    public $alliances = array();
    public $bases = array();
    public $pois = array();
    public $serverTime = array();
    public $endgames = array();
    private $server;

    public function __construct($server)
    {
        $this->server = $server;
    }

    public function request($sx, $sy, $ex, $ey)
    {
        $result = "";
        for ($y = $sy; $y <= $ey; $y++) {
            for ($x = $sx; $x <= $ex; $x++) {
                $data = $y << 8 | $x;
                $version = 0;
                $data |= 0 << 16;
                $result = $result . Base91::Encode19Bit($data) . Base91::EncodeFlexInt($version);
            }
        }
        return $result;
    }


    public function addSquare(Square $square)
    {
        foreach ($square->alliances as $alliance) {
            if (!isset($this->alliances[$alliance['_id']])) {
                $this->alliances[$alliance['_id']] = [
                    'a' => $alliance['_id'],
                    'an' => $alliance['name'],
                    'p' => $alliance['points'],
                    'c' => 0,
                ];

            }
        }

        foreach ($square->players as $player) {
            if (!isset($this->players[$player['_id']])) {
                //absolute alliance ID
                $pa = $player['alliance'];
                $a = $square->alliances[$pa]['_id'];
                $player['alliance'] = !$a ? 0 : $a;
                $this->alliances[$player['alliance']]['c']++;

                $this->players[$player['_id']] = [
                    'i' => $player['_id'],
                    'p' => $player['points'],
                    'a' => $player['alliance'],
                    'n' => $player['name'],
                    'f' => $player['faction'],
                    'ps' => $player['peaceStart'],
                    'pd' => $player['peaceDuration'],
                    'bc' => 0
                ];
            }
        }

        foreach ($square->marks as $mark) {
            if ($mark instanceof City) {
                //absolute player ID
                $mark->playerId = $square->players[$mark->playerId]['_id'];

                $this->players[$mark->playerId]['bc']++;

                $this->bases[] = [
                    'pi' => $mark->playerId,
                    'y' => $mark->y,
                    'x' => $mark->x,
                    'n' => $mark->name,
                    'i' => $mark->_id,
                    'l' => $mark->level,
//                        'at' => $mark->isAttacked,
//                        'dd' => $mark->isDefenseDamaged,
//                        'lo' => $mark->isLocked,
                    'al' => $mark->isAlerted,
                    'pr' => $mark->isProtected,
                    'cb' => $mark->conditionBuildings,
                    'cd' => $mark->conditionDefense,
//                        'mc' => $mark->moveCooldownEndStep,
//                        'ml' => $mark->moveLockdownEndStep,
                    'ps' => $mark->protectionEndStep,
//                        'ct' => $mark->codeTime
                ];
            }
            if ($mark instanceof POI) {
                $poiAllianceId = "";
                if ($mark->OwnerAllianceId != 0) {
                    $poiAllianceId = $square->alliances[$mark->OwnerAllianceId]['_id'];
                }
                $this->pois[] = [
                    'x' => $mark->x,
                    'y' => $mark->y,
                    't' => $mark->type,
                    'l' => $mark->level,
                    'a' => $poiAllianceId,
                ];
            }
        }
        //        foreach ($this->players as $p => $player) {
        //            if ($player['bc'] == 0) {
        //                unset($this->players[$p]);
        //            }
        //        }
        //        foreach ($this->alliances as $a => $alliance) {
        //            if ($alliance['c'] == 0) {
        //                unset($this->alliances[$a]);
        //            }
        //        }

    }

    public function prepareData()
    {
        uasort($this->alliances, function ($a, $b) {
            return (strtolower($a['an']) > strtolower($b['an'])) ? 1 : -1;
        });

        $data = array('bases' => $this->bases,
            'players' => array_values($this->players),
            'alliances' => array_values($this->alliances),
            'pois' => array_values($this->pois),
            'timestamp' => "_%timestamp%_",
            'server_time' => $this->serverTime,
            'endgames' => $this->endgames
        );
        return $data;
    }

    public function toServer()
    {
        $zip = gzencode(json_encode($this->prepareData()));
        $curler = Curler::create()
            ->setUrl("http://data.tiberium-alliances.com/savedata")
            ->setPostData(Curler::encodePost(
                array(
                    'key' => "wohdfo97wg4iurvfdc t7yaigvrufbs",
                    'world' => $this->server,
                    'data' => $zip)
            )
            )
            ->withHeaders(false);
        $curler->post();
        $curler->close();
        return $zip;
    }

    public function setServerTime($data, $requestTime)
    {
        $this->serverTime = array(
            "d" => $data->d,
            "o" => $data->o,
            "r" => sprintf("%.0f", $data->r),
            "s" => $data->s,
            "rt" => $requestTime
        );
    }

    public function setEndGame($data)
    {
        foreach ($data as $endgame) {
            $point = array();

            $pos = 0;
            $out = new stdClass();
            $id = Base91::DecodeFlexInt($endgame, $pos, $out);
            $pos += $out->size;
            $version = Base91::DecodeFlexInt($endgame, $pos, $out);
            $pos += $out->size;
            $coordId = Base91::Decode26Bits($endgame, $pos);
            $pos += 4;
            $details = Base91::Decode13Bits($endgame, $pos);

            $point['type'] = ($details & 0x3);
            if ($point['type'] == 2) {
                $point['step'] = ($details >> 2);
            }
            $point['x'] = ($coordId & 0x1fff);
            $point['y'] = ($coordId >> 13);
            $this->endgames[] = $point;
        }
    }
}
