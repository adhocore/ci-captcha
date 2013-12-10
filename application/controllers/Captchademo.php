<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Captcha library for CodeIgniter
 * 
 * It can generate two types of captcha:
 *   a) "type exactly what you see in image" - noise added
 *   b) "solve the maths equation seen in image" - noise removed
 * It uses session and not the database.
 * Please note that this lib requires image GD functions supported by
 * your PHP installation. See http://www.php.net/manual/en/ref.image.php
 * 
 * @author adhocore | Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @copyright (c) null
 * @license null
 * @link https://github.com/adhocore/ci-captcha The main repo of this lib
 */
class Captchademo extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $viewdata = array();
        if ($post = $this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('captcha_input', 'Captcha', 'required|callback__checkCaptcha');
            if ($this->form_validation->run($this)) {
                $viewdata['response'] = 'Captcha correct.';
            } else {
                $viewdata['response'] = form_error('captcha_input');
            }
        }
        $this->load->view('captchademo', $viewdata);
    }

    // move this method to base controller so it is always available for $this scope 
    public function _checkCaptcha($input) {
        $code = $this->session->userdata('captcha_code');
        if (strcasecmp($code, $input) == 0) {
            return true;
        }
        $this->form_validation->set_message(__FUNCTION__, 'Captcha incorrect.');
        return false;
    }

}