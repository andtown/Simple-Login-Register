<?php

defined('SLR_PLUGIN_PATH') || define('SLR_PLUGIN_PATH', dirname(plugin_dir_path( __FILE__ )) );

defined('SLR_TABLE_NAME_PREFIX') || define('SLR_TABLE_NAME_PREFIX', 'SLR_' );

defined('SLR_IDLE_LOGGED_EXPIRATION_TIME') || define('SLR_IDLE_LOGGED_EXPIRATION_TIME', 86400);


defined('SLR_COOKIE_EXPIRATION_TIME') || define('SLR_COOKIE_EXPIRATION_TIME', 157680000);

defined('SLR_COOKIE_OVER_HTTPS') || define('SLR_COOKIE_OVER_HTTPS', is_ssl() || ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) ) );

defined('SLR_COOKIE_DISALLOW_JS_ACCESS') || define('SLR_COOKIE_DISALLOW_JS_ACCESS', true);

defined('SLR_COOKIE_NAME') || define('SLR_COOKIE_NAME','simple');


defined('SLR_NONCE_PREFIX') || define('SLR_NONCE_PREFIX','simple_');

defined('SLR_NONCE_EXPIRATION_TIME') || define('SLR_NONCE_EXPIRATION_TIME', 900);


defined('SLR_TEMPLATE_DEFAULT_FILE_NAME') || define('SLR_TEMPLATE_DEFAULT_FILE_NAME', 'template');

defined('SLR_TEMPLATE_DASHBOARD_ID') || define('SLR_TEMPLATE_DASHBOARD_ID','simple-dashboard');

defined('SLR_TEMPLATE_LOGIN_REGISTER_FORM_ID') || define('SLR_TEMPLATE_LOGIN_REGISTER_FORM_ID','simple-login-register-form');

defined('SLR_TEMPLATE_CONTAINER_ID') || define('SLR_TEMPLATE_CONTAINER_ID','simple-login-register');


defined('SLR_MODEL_NAME') || define('SLR_MODEL_NAME','loginRegisterForm');


defined('SLR_FIELD_EMAIL') || define('SLR_FIELD_EMAIL','simple_email');

defined('SLR_FIELD_PASSWORD') || define('SLR_FIELD_PASSWORD','simple_password');

defined('SLR_FIELD_FULL_NAME') || define('SLR_FIELD_FULL_NAME','simple_fullname');

defined('SLR_FIELD_RE_PASSWORD') || define('SLR_FIELD_RE_PASSWORD','simple_repassword');

defined('SLR_FIELD_NONCE') || define('SLR_FIELD_NONCE','_wpnonce');

defined('SLR_FIELD_HTTP_REFERER') || define('SLR_FIELD_HTTP_REFERER','_wp_http_referer');

defined('SLR_FORCE_PAGE_REFRESH') || define('SLR_FORCE_PAGE_REFRESH', false);