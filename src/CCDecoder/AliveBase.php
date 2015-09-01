<?php

namespace limitium\TAPD\CCDecoder;


class AliveBase extends Base
{
    public $_id;
    public $isAttacked;
    public $isLocked;
    public $isAlerted;
    public $isDefenseDamaged;
    public $supportAlertStartStep;
    public $supportAlertEndStep;
    public $conditionBuildings;
    public $conditionDefense;
    public $lockdownEndStep;
}
