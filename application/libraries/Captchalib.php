<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Captcha library for CodeIgniter
 * 
 * It can generate two types of captcha:
 *   a) "type exactly what you see in image" - noise added
 *   b) "solve the maths equation seen in image" - noise removed
 * It uses session and not the database.
 * You can heavily customize the captcha generation by setting options
 * via its initialize() method that accepts assoc array of params.
 * Please note that this lib requires image GD functions supported by
 * your PHP installation. See http://www.php.net/manual/en/ref.image.php
 * 
 * @author adhocore | Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @copyright (c) 2012 - 2013, Jitendra Adhikari
 * @license null
 * @link https://github.com/adhocore/ci-captcha The main repo of this lib
 */
class Captchalib {

    private $CI;
    private $type = 'image';
    
    # image options	
    private $image_width = 120;
    private $image_height = 40;
    private $characters_length = 6;
    private $font = './assets/fonts/font.ttf'; // relative to FCPATH
    private $random_fonts = FALSE;
    private $random_dots = 40;
    private $random_lines = 8;
    private $text_color = "0x123456";
    private $noise_color = "0x192864";
    
    # equation options
    private $operand_count = 2; // 2 recommended
    private $operators = array('+', '-'); // append '/', or '*' to add complexity (not recommended)

    public function __construct($config = array()) {

        $this->CI = & get_instance();
        $this->CI->load->library('session');
        
        if (count($config) > 0) {
            $this->initialize($config);
        }
        if (!is_file($this->font) or $this->random_fonts) {
            $fonts = glob('./assets/fonts/*.ttf');
            if (empty($fonts)) {
                show_error("No fonts in path: './assets/fonts/'");
            }
            $this->font = count($fonts) > 1 ? $fonts[array_rand($fonts)] : $fonts[0];
        }
    }

    function render() {
        $this->type == 'equation' ? $this->equation() : $this->image();
    }

    function initialize($config = array()) {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }

    private function image($code = '') {
        if (!$code) {
            $i = 0;
            $sess_write = true;
            $pool = '23456789bcdfghjkmnpqrstvwxyzABCDEFGHJKLMNQRTVWXYZ'; // graphically similar chars are excluded            
            while ($i++ < $this->characters_length) {
                $code .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
            }
        } else {
            $sess_write = false;
        }
        $font_size = $this->image_height * 0.75;
        $image = @imagecreate($this->image_width, $this->image_height);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $arr_text_color = $this->hexrgb($this->text_color);
        $text_color = imagecolorallocate($image, $arr_text_color['red'], $arr_text_color['green'], $arr_text_color['blue']);

        $arr_noice_color = $this->hexrgb($this->noise_color);
        $image_noise_color = imagecolorallocate($image, $arr_noice_color['red'], $arr_noice_color['green'], $arr_noice_color['blue']);
        for ($i = 0; $i < $this->random_dots; $i++) {
            imagefilledellipse($image, mt_rand(0, $this->image_width), mt_rand(0, $this->image_height), 2, 3, $image_noise_color);
        }
        for ($i = 0; $i < $this->random_lines; $i++) {
            imageline($image, mt_rand(0, $this->image_width), mt_rand(0, $this->image_height), mt_rand(0, $this->image_width), mt_rand(0, $this->image_height), $image_noise_color);
        }
        $textbox = imagettfbbox($font_size, 0, $this->font, $code);
        $x = ($this->image_width - $textbox[4]) / 2;
        $y = ($this->image_height - $textbox[5]) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $this->font, $code);
        if ($sess_write) {
            $this->CI->session->set_userdata('captcha_code', $code);
        }
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);        
    }

    private function hexrgb($hexstr) {
        $int = hexdec($hexstr);
        return array(
            "red" => 0xFF & ($int >> 0x10),
            "green" => 0xFF & ($int >> 0x8),
            "blue" => 0xFF & $int
        );
    }

    private function equation() {
        $equation = '';
        $operators = $this->operators;
        for ($i = 1; $i <= $this->operand_count; $i++) {
            $equation .= mt_rand(10, 100);
            if ($i < $this->operand_count)
                $equation .= $operators[array_rand($operators)];
        }
        $ans = '';
        eval("\$ans" . '=' . $equation . ';');
        $this->CI->session->set_userdata('captcha_code', $ans);
        if ($ans and $this->CI->session->userdata('captcha_code') == $ans) {
            $this->random_dots = 0;
            $this->random_lines = 0;
            $this->image($equation . '=');
        }
    }

}