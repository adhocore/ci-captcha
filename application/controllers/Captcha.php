<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Captcha library for CodeIgniter
 * 
 * It can generate two types of captcha:
 *   a) "type exactly what you see in image" - noise added
 *   b) "solve the maths equation seen in image" - noise removed
 * Please note that this lib requires image GD functions supported by
 * your PHP installation. See http://www.php.net/manual/en/ref.image.php
 * 
 * @author adhocore | Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @copyright (c) null
 * @license null
 * @link https://github.com/adhocore/ci-captcha The main repo of this lib
 */

class Captcha extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->library('captchalib', array(
            'type' => 'image',
            'image_width' => 180,
            'image_height' => 50,
        ));
        
        $this->captchalib->render();
    }

}