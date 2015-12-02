<?php

// SITE Settings
define('SITE_URL', 'https://yourdomain/WorkTimeEditor/');
define('BRAND', 'WorkTimeEditor');

// Cookie Settings
session_set_cookie_params(0, '/WorkTimeEditor/');

// Domain Settings
define('APPS_DOMAIN','your company domain');
define('COMPANY','your company name');

// Google Authentication Settings
define('CLIENT_ID', '********');
define('CLIENT_SECRET', '********');

// AWS Settings
define('DynamoDB_WORKTIME_TABLE', '********');
define('DynamoDB_CONFIG_TABLE', '********');
define('DynamoDB_REGION', '********'); // e.g. ap-northeast-1
define('AWS_ACCESS_KEY_ID','********');
define('AWS_SECRET_ACCESS_KEY','********');
define('OpenSSL_ENCRYPT_KEY','********');

// PHP error reporting
error_reporting(E_ALL &~E_NOTICE);
//ini_set( 'display_errors', 1 );

// Server Locale
setlocale(LC_ALL, 'ja_JP.UTF-8');

// timezone
date_default_timezone_set('Asia/Tokyo');
