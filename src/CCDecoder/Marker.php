<?php

namespace limitium\TAPD\CCDecoder;


class Marker
{
    public $x;
    public $y;

    public function setXY($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    public function __toString()
    {
        print_r($this);
        print_r("\r\n");
    }
}
