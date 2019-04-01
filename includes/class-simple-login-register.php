<?php

class Simple_Login_Register {

	protected static $instance;

	protected $sc_atts = array();

	protected $template = null;

	protected $frontend_script = array();

	protected $l10n = array();

	protected $authorized = false;

	private function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	private function is_coming_through_admin_ajax_php() {
		$admin_ajax = parse_url(strtolower(admin_url('admin-ajax.php')));
		$current = parse_url(untrailingslashit(strtolower(is_ssl() ? 'https://' : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])));
		return $admin_ajax['host'] == $current['host'] && $admin_ajax['path'] == $current['path'];
	}

	private function i18n() {

		load_plugin_textdomain('simple-login-register', false,  dirname ( dirname( plugin_basename( __FILE__ ) ) ) . '/languages');

	}

	private function login_register_template($template) { 

		if ( ($this->template = SLR_PLUGIN_PATH . '/templates/'.$this->sc_atts['template_file'].'.php') && file_exists($this->template) ) {			
			$this->i18n();			
			add_action( 'wp_enqueue_scripts', array($this, 'template_scripts') );
			add_action( 'wp_print_footer_scripts', array($this, 'wp_print_footer_scripts') );
		} elseif ( ($this->template = STYLESHEETPATH . '/simple-login-register/'.$this->sc_atts['template_file'].'.php') && file_exists($this->template) ) {} 

		add_shortcode('simple_login_register', array($this, file_exists($this->template)?'render_shortcode':'template_not_found'));
	}	

	protected function email_exists( $email ) {
		return (apply_filters('simple_login_register_check_email', false, $email) || ('test@test.com' == $email));
	}

	protected function validate_user( $email, $password) {
		return ( $this->email_exists($email) && (apply_filters('simple_login_register_validate_user', false, $email, $password) || ('test' == $password)) );
	}	

	public static function get_instance() {
		if ( !isset(static::$instance) ) new static;
		return static::$instance;
	}

	public static function activate_plugin() {	
		flush_rewrite_rules();
	}

	public static function deactivate_plugin() {
		flush_rewrite_rules();
	}

	public function get_transient_nonce() {
		$nonce = '';
		if ( !empty($_COOKIE[SLR_COOKIE_NAME]) && (($nonce = get_transient(SLR_COOKIE_NAME."_{$_COOKIE[SLR_COOKIE_NAME]}_nonce")) === false || empty($nonce)) )  {
			$nonce = md5($this->hash);
			set_transient(SLR_COOKIE_NAME."_{$_COOKIE[SLR_COOKIE_NAME]}_nonce", $nonce, SLR_NONCE_EXPIRATION_TIME );
			return $nonce;
		}
		return $nonce;	
	}

	public function __construct() {

		if ( session_status() == PHP_SESSION_NONE || empty(session_id()) ) session_start();

		$this->l10n = require_once plugin_dir_path(__FILE__).'simple-login-register-l10n.php';

		static::$instance = $this;

		if ( wp_doing_ajax() ) {
			add_action('wp_ajax_nopriv_simple_init', array($this, 'simple_init'));
			add_action('wp_ajax_simple_init', array($this, 'simple_init'));
			return;
		}

		add_action('init', array($this,'simple_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this,'admin_init'));
		add_action('template_redirect', array($this,'template_redirect'));

	}

	public function simple_init() {

		$this->hash = sha1(site_url().session_id().$this->microtime_float());	

		if ( empty($_COOKIE[SLR_COOKIE_NAME]) ) {
			setcookie(SLR_COOKIE_NAME, $this->hash, time()+SLR_COOKIE_EXPIRATION_TIME, COOKIEPATH, COOKIE_DOMAIN, SLR_COOKIE_OVER_HTTPS, SLR_COOKIE_DISALLOW_JS_ACCESS);
		}	

		$this->is_user_logged_in = ( !empty($_COOKIE[SLR_COOKIE_NAME]) && ((int) get_transient(SLR_COOKIE_NAME."_{$_COOKIE[SLR_COOKIE_NAME]}_logged_in") === 1) );

		$this->sc_atts = array(
			'template_file' => SLR_TEMPLATE_DEFAULT_FILE_NAME,
			'template_dashboard_id' => SLR_TEMPLATE_DASHBOARD_ID,
			'template_login_register_form_id' => SLR_TEMPLATE_LOGIN_REGISTER_FORM_ID,
			'template_container_id' => SLR_TEMPLATE_CONTAINER_ID,
			'model_name' => SLR_MODEL_NAME,
			'field_name' => SLR_FIELD_FULL_NAME,
			'field_email' => SLR_FIELD_EMAIL,
			'field_password' => SLR_FIELD_PASSWORD,
			'field_repassword' => SLR_FIELD_RE_PASSWORD,
			'field_nonce' => SLR_FIELD_NONCE,
			'field_http_referer' => SLR_FIELD_HTTP_REFERER,
			// force_page_refresh attribute is needed for testing purpose so that we can easily switch the mode to see how the process is going between the two			
			'force_page_refersh' => SLR_FORCE_PAGE_REFRESH
		);

		$this->frontend_script = array(
			'response' => array(
				'template' => ( !$this->is_user_logged_in ) ? $this->sc_atts['template_login_register_form_id']: $this->sc_atts['template_dashboard_id'], 
				SLR_FIELD_NONCE => (!$this->is_user_logged_in)?wp_create_nonce(SLR_NONCE_PREFIX.'login_'.$this->get_transient_nonce()):wp_create_nonce(SLR_NONCE_PREFIX.'logged_in_'.$this->get_transient_nonce()),
				'form_title' => $this->l10n['login_title']
			)
		);	

		if ( !( ( $is_logging_in = check_ajax_referer( SLR_NONCE_PREFIX.'login_'.$this->get_transient_nonce(), $_REQUEST[SLR_FIELD_NONCE] ?? '', false ) !== false ) || 
		( ($is_logging_in = check_ajax_referer(  SLR_NONCE_PREFIX.'register_'.$this->get_transient_nonce(), $_REQUEST[SLR_FIELD_NONCE] ?? '', false ) !== false) && !($is_logging_in = !$is_logging_in) ) ) ) {
			return;
		}

		$data = stripslashes_deep($_REQUEST);

		$data[SLR_FIELD_EMAIL] = sanitize_email($data[SLR_FIELD_EMAIL]);		

		if ( $is_logging_in ) {

			$this->frontend_script['response'][SLR_FIELD_NONCE] = wp_create_nonce(SLR_NONCE_PREFIX.'login_'.$this->get_transient_nonce());

			if ( !empty($data[SLR_FIELD_EMAIL]) && !empty($data[SLR_FIELD_PASSWORD]) && !$this->validate_user($data[SLR_FIELD_EMAIL], $data[SLR_FIELD_PASSWORD])  ) {
				$this->frontend_script['response']['notice'] = 'password_doesnt_match';
				$this->frontend_script['response'][SLR_FIELD_EMAIL] = $data[SLR_FIELD_EMAIL];
				$this->frontend_script['response']['form_title'] = $this->l10n['login_password_title'];
			} 

			elseif ( !empty($data[SLR_FIELD_EMAIL]) && !$this->email_exists($data[SLR_FIELD_EMAIL]) ) {
				$this->frontend_script['response'][SLR_FIELD_EMAIL] = $data[SLR_FIELD_EMAIL];
				$this->frontend_script['response'][SLR_FIELD_NONCE] = wp_create_nonce(SLR_NONCE_PREFIX.'register_'.$this->get_transient_nonce());
				$this->frontend_script['response']['form_title'] = $this->l10n['signup_title'];
			}

			elseif ( empty($data[SLR_FIELD_PASSWORD]) && !empty($data[SLR_FIELD_EMAIL]) && $this->email_exists($data[SLR_FIELD_EMAIL]) ) { 
				$this->frontend_script['response'][SLR_FIELD_EMAIL] = $data[SLR_FIELD_EMAIL];
				$this->frontend_script['response']['form_title'] = $this->l10n['login_password_title'];
			}

			elseif ( !empty($data[SLR_FIELD_EMAIL]) && empty($data[SLR_FIELD_PASSWORD]) ) { 
				$this->frontend_script['response']['notice'] = 'blank_password';
			} 								

			elseif ( empty($data[SLR_FIELD_EMAIL]) ) {
				$this->frontend_script['response']['notice'] = 'blank_email';
			}
	
			else {
				$this->authorized = true;
			}

		} else {  // registering			

			$this->frontend_script['response'][SLR_FIELD_NONCE] = wp_create_nonce(SLR_NONCE_PREFIX.'register_'.$this->get_transient_nonce());

			$this->frontend_script['response']['form_title'] = $this->l10n['signup_title'];			

 			$data[SLR_FIELD_FULL_NAME] = sanitize_text_field($data[SLR_FIELD_FULL_NAME]);

 			if ( !empty($data[SLR_FIELD_EMAIL]) && $this->email_exists($data[SLR_FIELD_EMAIL]) ) {
 				$this->frontend_script['response']['notice'] = 'email_in_use';
 			}

			elseif ( empty($data[SLR_FIELD_EMAIL]) ) {
				$this->frontend_script['response']['notice'] = 'blank_email';
			}

			elseif ( empty($data[SLR_FIELD_FULL_NAME]) ) {
				$this->frontend_script['response']['notice'] = 'blank_fullname';		
			} 

			elseif ( empty($data[SLR_FIELD_PASSWORD]) ) {
				$this->frontend_script['response']['notice'] = 'blank_password';		
			}

			elseif ( empty($data[SLR_FIELD_RE_PASSWORD]) ) {
				$this->frontend_script['response']['notice'] = 'blank_repassword';			
			} 								

			elseif ( $data[SLR_FIELD_RE_PASSWORD] != $data[SLR_FIELD_PASSWORD] ) {
				$this->frontend_script['response']['notice'] = 'password_doesnt_match';
			}

			else {
				$this->authorized = true;
			}

			$this->frontend_script['response'] = wp_parse_args($this->frontend_script['response'], $data);

		}

		if ( $this->authorized ) { 
			$this->frontend_script['response']['template'] = $this->sc_atts['template_dashboard_id'];
			$this->frontend_script['response'][SLR_FIELD_NONCE] = wp_create_nonce(SLR_NONCE_PREFIX.'logged_in_'.$this->get_transient_nonce());
			$_SESSION[SLR_COOKIE_NAME][SLR_FIELD_NONCE] = $this->frontend_script['response'][SLR_FIELD_NONCE];
			set_transient(SLR_COOKIE_NAME."_{$_COOKIE[SLR_COOKIE_NAME]}_logged_in", 1, SLR_IDLE_LOGGED_EXPIRATION_TIME ); 
		}

		if ( $this->is_coming_through_admin_ajax_php() && wp_doing_ajax() ) {

			@header('Content-Type: application/json');

			echo json_encode($this->frontend_script['response']);

			wp_die();

		} else { 
			// page refresh
		}

	} 

	public function admin_init() {

		global $post, $hook_suffix, $page_hook, $plugin_page, $pagenow, $typenow, $taxnow;
 
	}

	public function admin_menu() {

		global $admin_page_hooks;

	}

	public function template_redirect() {

		global $post;

		if ( !( $post instanceof WP_POST && preg_match('/'.get_shortcode_regex(['simple_login_register']).'/',$post->post_content,$matches)) ) return;

		$this->sc_atts = wp_parse_args(shortcode_parse_atts($matches[3]), $this->sc_atts);

		add_filter('template_include', array($this,'template_include'));

	}

	public function render_shortcode( $atts ) {

	    $this->sc_atts = shortcode_atts( $this->sc_atts, $atts, 'simple_login_register' );

		wp_localize_script( 'slr-js', '_simple', wp_parse_args($this->frontend_script ,array(
			'url' => ($this->sc_atts['force_page_refersh'] !='true')?admin_url('admin-ajax.php'):'',	
			'http_referer' => wp_unslash( $_SERVER['REQUEST_URI'] ),		
			'template_container_id' => $this->sc_atts['template_container_id'],
			'model_name' => $this->sc_atts['model_name'],
			'action' => 'simple_init',			
			'force_page_refersh' => ($this->sc_atts['force_page_refersh'] = ("true"==$this->sc_atts['force_page_refersh'])?true:false),
			'field_name' => $this->sc_atts['field_name'],
			'field_email' => $this->sc_atts['field_email'],
			'field_password' => $this->sc_atts['field_password'],
			'field_repassword' => $this->sc_atts['field_repassword'],
			'field_nonce' => $this->sc_atts['field_nonce'],
			'field_http_referer' => $this->sc_atts['field_http_referer'],			
			'l10n' => $this->l10n
		)));

	    return '<div id="'.$this->sc_atts['template_container_id'].'" class="wrapper"></div>';

	}

	public function template_include($template) {

		$this->login_register_template($template);

		return $template;

	}
	
	public function template_scripts() {
		wp_enqueue_script('simple',plugin_dir_url( dirname(__FILE__) ).'public/js/simple.js', array('jquery','backbone','underscore','wp-util'), null, true );
		wp_enqueue_script('slr-js',plugin_dir_url( dirname(__FILE__) ).'public/js/'.$this->sc_atts['template_file'].'.js', array('simple'), null, true );
		ob_start(); 
	?>
		(function($) {
			'use strict';		
	        $(window).load(function() { 
	            if ( !( window.simple && wp && _simple) ) return; 
	            new (simple.models[_simple.model_name].extend({
	                url: _simple.url || ''
	            }))({_wpnonce: _simple.response.<?=SLR_FIELD_NONCE?> || '', action: _simple.action, _http_referer: _simple.http_referer},{params: _simple});
	        });
		})(jQuery);
	<?php
		wp_add_inline_script('slr-js', ob_get_clean());
		wp_enqueue_style('olr', plugin_dir_url( dirname(__FILE__) ).'public/css/'.$this->sc_atts['template_file'].'.css', array(), null, 'all' );

	}	

	public function template_not_found( $atts ) {

		return $this->l10n()['template_not_found'];

	}

	public static function load_template($template, $params) {
		require_once $template;
	}

	public function wp_print_footer_scripts() {
		if ( $this->template ) static::load_template($this->template, $this->sc_atts);
	}


}