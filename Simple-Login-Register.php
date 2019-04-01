<?php
/*
  Plugin Name: Simple Login Register
  Plugin URI: 
  Description: Simple Login Register
  Version: 1.0.0
  Author: Andtown
  Author URI: 
  Text Domain: simple-login-register
  Domain Path: /languages  
 */

if ( !defined('WPINC') ) {
	die;
}

register_activation_hook( __FILE__, array('Simple_Login_Register', 'activate_plugin') );

register_deactivation_hook( __FILE__, array('Simple_Login_Register', 'deactivate_plugin') );

require plugin_dir_path( __FILE__ ). 'includes/simple-login-register-constants.php';

require plugin_dir_path( __FILE__ ). 'includes/class-simple-login-register.php';

Simple_Login_Register::get_instance();