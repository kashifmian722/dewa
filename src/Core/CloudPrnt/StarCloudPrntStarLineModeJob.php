<?php

namespace Appflix\DewaShop\Core\CloudPrnt;

class StarCloudPrntStarLineModeJob extends StarCloudPrntJob
{

    public function add_nv_logo($keycode)
    {
        $this->printJobBuilder .= "1B1C70".$keycode."00".self::SLM_NEW_LINE_HEX;
    }

    public function sound_buzzer($circuit, $pulse_ms, $delay_ms)
    {
        $circuit = intval($circuit);
        if($circuit < 1) $circuit = 1;
        if($circuit > 2) $circuit = 2;

        $pulse_param = $pulse_ms / 20;
        $delay_param = $delay_ms / 20;

        if($pulse_param <= 0) $pulse_param = 0;
        if($pulse_param >= 255) $pulse_param = 255;

        if($delay_param <= 0) $delay_param = 0;
        if($delay_param >= 255) $pulse_param = 255;

        $command = sprintf("1B1D07%02X%02X%02X", $circuit, $pulse_param, $delay_param);
        $this->printJobBuilder .= $command;
    }

}