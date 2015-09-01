<?php

namespace limitium\TAPD\CCDecoder;


use Exception;
use stdClass;

class Square
{
    public $_id;
    public $sx;
    public $sy;
    public $marks = array();
    public $alliances = array(0 => array(
        '_id' => 0,
        'name' => "No Alliance",
        'points' => 0,
    ));
    public $players = array();

    /**
     * @static
     * @param $data
     * @return Square
     */
    public static function decode($data)
    {
        //        "a" - contains all alliances in the sector
        //        "d" - contains all Objects
        //        "i" - is used as the id of the sector
        //        "p" - are the players in this sector
        //        "t" - is the terrain
        //        "u" - are terrain details
        //        "v" - version of the world sector

        $sqare = new self();

        $id = (($data->i & 0xff) | (($data->i >> 8) << 0x10));
        $sqare->_id = $id;
        $sqare->sx = ($id & 0xffff);
        $sqare->sy = ($id >> 0x10);

        $sqare->parseAlliances($data->a);
        $sqare->parsePlayers($data->p);
        $sqare->parseObjects($data->d);

        return $sqare;
    }

    private function parseAlliances($data)
    {
        foreach ($data as $allianceData) {
            $id = Base91::Decode13Bits($allianceData, 0);
            $out = new stdClass();
            $pos = 2;
            $aId = Base91::DecodeFlexInt($allianceData, $pos, $out);
            $pos += $out->size;
            $points = Base91::DecodeFlexInt($allianceData, $pos, $out);
            $pos += $out->size;
            $name = substr($allianceData, $pos);

            $this->alliances[$id] = array(
                '_id' => $aId,
                'name' => $name,
                'points' => $points,
            );
        }
    }

    private function parsePlayers($data)
    {
        foreach ($data as $playerData) {
            $id = Base91::Decode13Bits($playerData, 0);
            $out = new stdClass();
            $pos = 2;
            $pid = Base91::DecodeFlexInt($playerData, $pos, $out);
            $pos += $out->size;
            $points = Base91::DecodeFlexInt($playerData, $pos, $out);
            $pos += $out->size;
            $state = Base91::Decode13Bits($playerData, $pos);
            $faction = (($state >> 1) & 3);
            $alliance = ($state >> 3);

            $pos += 2;
            if (($state & 1) != 0) {
                $peaceStart = Base91::DecodeFlexInt($playerData, $pos, $out);
                $pos += $out->size;
                $peaceDuration = Base91::DecodeFlexInt($playerData, $pos, $out);
                $pos += $out->size;
            } else {
                $peaceStart = 0;
                $peaceDuration = 0;
            }
            $name = substr($playerData, $pos);

            $this->players[$id] = array(
                "_id" => $pid,
                "peaceStart" => $peaceStart,
                "peaceDuration" => $peaceDuration,
                "name" => $name,
                "faction" => $faction,
                "alliance" => $alliance,
                "points" => $points,
            );

        }
    }

    private function parseObjects($data)
    {
        foreach ($data as $chunk) {
            $pos = 0;
            $headData = Base91::Decode13Bits($chunk, $pos);
            $x = ($headData & 0x1f);
            $y = (($headData >> 5) & 0x1f);
            $type = ($headData >> 10);

            $pos += 2;
            switch ($type) {
                case 0:
                    //None
                    //Remove objects $this->sx * 32 + x, $this->sy * 32 + y
                    break;
                case 1:
                    //City
                    // +2 because of the earlier decoding of the 13 bits of the Base91
                    $this->marks[] = City::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 2:
                    //NPCBase
//                    $this->marks[] = NPCBase::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 3:
                    //NPCCamp
                    break;
                case 4:
                    //PointOfInterst
                    $this->marks[] = POI::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                case 5:
                    //NewPlayerSLot
                    break;
                case 7:
                    //Ruin
//                    $this->marks[] = Ruin::decode($chunk, $pos)->setXY($this->sx * 32 + $x, $this->sy * 32 + $y);
                    break;
                default:
                    throw new Exception('Undefined type ' . $type);
            }
        }
    }

}
