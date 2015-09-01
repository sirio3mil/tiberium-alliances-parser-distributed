<?php

namespace limitium\TAPD\CCDecoder;


use stdClass;

class POI extends Marker
{
    public $_id;
    public $level;
    public $type;
    public $OwnerAllianceId;

    /**
     * @static
     * @param $details
     * @param $pos
     * @return POI
     */
    public static function decode($details, $pos)
    {
        $poi = new self();
        $poiData = Base91::Decode26Bits($details, $pos);
        $poi->level = $poiData & 255;
        $poi->type = $poiData >> 8 & 7; //ClientLib.Data.WorldSector.WorldObjectPointOfInterest.EPOIType.Defense;
        $poi->OwnerAllianceId = (($poiData >> 11) & 0x3ff);
        $pos += 4;
        $out = new stdClass();
        $poi->_id = Base91::DecodeFlexInt($details, $pos, $out);
        return $poi;
    }
}
