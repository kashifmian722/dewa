<?php

namespace Appflix\DewaShop\Core\CloudPrnt;

class StarCloudPrntJob
{
    const SLM_NEW_LINE_HEX = "0A";
    const SLM_SET_EMPHASIZED_HEX = "1B45";
    const SLM_CANCEL_EMPHASIZED_HEX = "1B46";
    const SLM_SET_LEFT_ALIGNMENT_HEX = "1B1D6100";
    const SLM_SET_CENTER_ALIGNMENT_HEX = "1B1D6101";
    const SLM_SET_RIGHT_ALIGNMENT_HEX = "1B1D6102";
    const SLM_FEED_FULL_CUT_HEX = "1B6402";
    const SLM_FEED_PARTIAL_CUT_HEX = "1B6403";
    const SLM_CODEPAGE_HEX = "1B1D74";

    protected string $printJobBuilder = "";

    // 'STAR_CLOUDPRNT_MAX_CHARACTERS_THREE_INCH', 48 
    // 'STAR_CLOUDPRNT_MAX_CHARACTERS_TWO_INCH', 32 
    // 'STAR_CLOUDPRNT_MAX_CHARACTERS_DOT_THREE_INCH', 42 
    // 'STAR_CLOUDPRNT_MAX_CHARACTERS_FOUR_INCH', 69 
    public $columns = 48;

    public function __construct($columns = 48)
    {
        $this->columns = $columns;
        $this->set_codepage();
    }

    public function str_to_hex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++)
        {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }

    public function print_wrapped_line($text)
    {
        $this->add_text_line(wordwrap($text, $this->columns, "\n", true));
    }

    public function set_text_emphasized()
    {
        $this->printJobBuilder .= self::SLM_SET_EMPHASIZED_HEX;
    }

    public function cancel_text_emphasized()
    {
        $this->printJobBuilder .= self::SLM_CANCEL_EMPHASIZED_HEX;
    }

    public function set_text_left_align()
    {
        $this->printJobBuilder .= self::SLM_SET_LEFT_ALIGNMENT_HEX;
    }

    public function set_text_center_align()
    {
        $this->printJobBuilder .= self::SLM_SET_CENTER_ALIGNMENT_HEX;
    }

    public function set_text_right_align()
    {
        $this->printJobBuilder .= self::SLM_SET_RIGHT_ALIGNMENT_HEX;
    }

    public function set_codepage($codepage = "UTF-8")
    {
        if($codepage == "UTF-8") {
            $this->printJobBuilder .= "1b1d295502003001"."1b1d295502004000";
        } elseif ($codepage == "1252") {
            $this->printJobBuilder .= self::SLM_CODEPAGE_HEX."20";
        } else {
            $this->printJobBuilder .= self::SLM_CODEPAGE_HEX.$codepage;
        }
    }

    public function add_nv_logo($keycode)
    {
    }

    public function set_font_magnification($width, $height)
    {
        if($width <= 1) {
            $w = 0;
        } elseif ($width >= 6) {
            $w = 5;
        } else {
            $w = $width - 1;
        }

        if($height <= 1) {
            $h = 0;
        } elseif ($height >= 6) {
            $h = 5;
        } else {
            $h = $height - 1;
        }

        $this->printJobBuilder .= "1B69"."0".$h."0".$w;
    }

    public function add_hex($hex)
    {
        $this->printJobBuilder .= $hex;
    }

    public function add_text($text)
    {
        $this->printJobBuilder .= $this->str_to_hex($text);
    }

    public function add_text_line($text)
    {
        $this->printJobBuilder .= $this->str_to_hex($text).self::SLM_NEW_LINE_HEX;
    }

    public function add_new_line($quantity)
    {
        for ($i = 0; $i < $quantity; $i++)
        {
            $this->printJobBuilder .= self::SLM_NEW_LINE_HEX;
        }
    }

    public function sound_buzzer($circuit, $pulse_ms, $delay_ms)
    {
    }

    public function set_text_highlight()
    {
        $this->printJobBuilder .= "1B34";
    }

    public function cancel_text_highlight()
    {
        $this->printJobBuilder .= "1B35";
    }

    public function add_qr_code($error_correction, $cell_size, $data)
    {
        $model = 2;
        if($error_correction < 0) $error_correction = 0;
        if($error_correction > 3) $error_correction = 3;
        if($cell_size < 1) $cell_size = 1;
        if($cell_size > 8) $cell_size = 8;
        $data_length = strlen($data);

        $set_model = sprintf("1B1D795330%02X", $model);
        $set_error_level = sprintf("1B1D795331%02X", $error_correction);
        $set_cell_size = sprintf("1B1D795332%02X", $cell_size);
        $set_data_prefix = sprintf("1B1D79443100%02X%02X", fmod($data_length, 256), intval($data_length / 256));
        $print = "1B1D7950";

        $this->printJobBuilder .= $set_model
            . $set_error_level
            . $set_cell_size
            . $set_data_prefix
            . $this->str_to_hex($data)
            . $print;
    }

    public function add_barcode($type, $module, $hri, $height, $data)
    {
        $n2 = 1;
        $n3 = 1;

        if($type < 0 || $type > 13)	return;							// Invalid barcode type
        if($hri) $n2 = 2;																// Print human-readable characters under the bacrode

        if($type == 0 || $type == 1 || $type == 3 || $type == 4 || $type == 6 || $type == 7)		// UPC-E, UPC-A, JAN/EAN8, JAN/EAN13, Code128, Code 93
        {
            $n3 = $module - 1;
            if($n3 < 1) $n3 = 1;
            if($n3 > 3) $n3 = 3;
        }
        elseif ($type == 4 || $type == 5 || $type == 8)		// Code 93, ITF, NW-7
        {
            $n3 = $module;
            if($n3 < 1) $n3 = 1;
            if($n3 > 9) $n3 = 9;
        }
        elseif ($type >= 9 && $type <= 13)		// GS1-128, GS1 DataBar
        {
            $n3 = $module;
            if($n3 < 1) $n3 = 1;
            if($n3 > 6) $n3 = 6;
        }

        if($height < 8) $height = 8;					// Minimum 1mm height
        if($height > 255) $height = 255;			// Max 32mm height

        $print_bc = sprintf("1B62%02X%02X%02X%02X", $type, $n2, $n3, $height)
            . $this->str_to_hex($data)
            . "1E";

        $this->printJobBuilder .= $print_bc;
    }

    public function cut()
    {
        $this->printJobBuilder .= self::SLM_FEED_PARTIAL_CUT_HEX;
    }

    public function getPrintContent()
    {
        return hex2bin($this->printJobBuilder);
    }

    public function add_column_separated_line($columns)
    {

        $total_columns = count($columns);
        
        if ($total_columns == 0) {
            return;
        }

        if ($total_columns == 1) {
            $this->add_text_line($columns[0]);
            return;
        }

        if ($total_columns == 2) {
            $total_characters = mb_strwidth($columns[0]) + mb_strwidth($columns[1]);
            $total_whitespace = $this->columns - $total_characters;
            $this->add_text_line($columns[0] . str_repeat(" ", $total_whitespace) . $columns[1]);
            return;
        }
        
        $total_characters = 0;
        foreach ($columns as $column) {
            $total_characters += strlen($column);
        }
        $total_whitespace = $this->columns - $total_characters;
        $total_spaces = $total_columns-1;
        $space_width = floor($total_whitespace / $total_spaces);
        $result = $columns[0].str_repeat(" ", $space_width);
        for ($i = 1; $i < ($total_columns-1); $i++) {
            $result .= $columns[$i].str_repeat(" ", $space_width);
        }
        $result .= $columns[$total_columns-1];
        $this->add_text_line($result);
  }
}