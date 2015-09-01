<?php

namespace limitium\TAPD\CCDecoder;


use stdClass;

class City extends AliveBase
{
    public $isProtected;
    public $hasCooldown;
    public $hasRecovery;
    public $playerId;
    public $protectionEndStep;
    public $cooldownEndStep;
    public $moveLockdownEndStep;
    public $name;
    public $hasMoveRecovery;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return City
     */
    public static function decode($details, $pos)
    {
        $base = new self();
        $cityData = Base91::Decode32Bits($details, $pos);
        $base->isAttacked = (($cityData & 1) != 0);
        $base->isLocked = ((($cityData >> 1) & 1) != 0);
        $base->isProtected = ((($cityData >> 2) & 1) != 0);
        $base->isAlerted = ((($cityData >> 3) & 1) != 0);
        $base->hasCooldown = ((($cityData >> 4) & 1) != 0);
        $base->hasRecovery = ((($cityData >> 5) & 1) != 0);
        $base->hasMoveRecovery = ((($cityData >> 6) & 1) != 0);
        $base->isDefenseDamaged = ((($cityData >> 7) & 1) != 0);


        $base->level = (($cityData >> 8) & 0xff);
        $base->radius = (($cityData >> 0x10) & 15);
        $base->playerId = (($cityData >> 0x16) & 0x3ff);
        $pos += 5;


        //???
        $out = new stdClass();
        $out->size = 0;
        if ($base->isLocked) {
            $base->lockdownEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->isProtected) {
            $base->protectionEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->isAlerted) {
            $base->supportAlertStartStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            $base->supportAlertEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasCooldown) {
            $base->cooldownEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasRecovery) {
            $base->recoveryEndStep = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
            Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        if ($base->hasMoveRecovery) {
            Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        }
        $base->conditionBuildings = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        if ($base->isDefenseDamaged) {
            $base->conditionDefense = Base91::DecodeFlexInt($details, $pos, $out);
            $pos += $out->size;
        } else {
            $base->conditionDefense = -1;
        }
        $base->defenceAutoRepairStartStep = Base91::DecodeFlexInt($details, $pos, $out);
        $pos += $out->size;
        $base->_id = Base91::DecodeFlexInt($details, $pos, $out);
        $base->name = substr($details, ($pos + $out->size));
        return $base;
    }


}
