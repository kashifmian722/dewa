<?php

namespace Appflix\DewaShop\Core\CloudPrnt;

class StarCloudPrntTextPlainJob extends StarCloudPrntJob
{

    public function set_text_emphasized()
    {
    }

    public function cancel_text_emphasized()
    {
    }

    public function set_text_left_align()
    {
    }

    public function set_text_center_align()
    {
    }

    public function set_text_right_align()
    {
    }

    public function set_codepage($codepage = "UTF-8")
    {
    }

    public function add_nv_logo($keycode)
    {
    }

    public function set_font_magnification($width, $height)
    {
    }

    public function set_text_highlight()
    {
    }

    public function cancel_text_highlight()
    {
    }

    public function add_qr_code($error_correction, $cell_size, $data)
    {
    }

    public function add_barcode($type, $module, $hri, $height, $data)
    {
    }

    public function cut()
    {
    }

    public function getPrintContent()
    {
        return hex2bin($this->printJobBuilder.self::SLM_NEW_LINE_HEX);
    }
}