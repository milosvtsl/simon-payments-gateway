<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:26 PM
 */

use System\Config\SiteConfig;
use System\Config\DBConfig;

// Database Config
DBConfig::$DB_HOST = 'localhost';
DBConfig::$DB_NAME = 'spgdemo'; // 'paylogic';
DBConfig::$DB_USERNAME = 'paylogic2';
DBConfig::$DB_PASSWORD = 'eVw{P7mphBn';

// Site Config
SiteConfig::$SITE_NAME = "Simon Payments Gateway";
SiteConfig::$DEFAULT_THEME = 'View\Theme\SPG\SPGViewTheme';

// Email Config
SiteConfig::$EMAIL_SERVER_HOST = 'relay-hosting.secureserver.net'; // smtpout.secureserver.net
SiteConfig::$EMAIL_SERVER_PORT = 465; // 3535   80  25
SiteConfig::$EMAIL_SMTP_AUTH = false; // true;
SiteConfig::$EMAIL_SMTP_SECURE = 'ssl'; // 'tls';
SiteConfig::$EMAIL_USERNAME = 'support@simonpayments.com';
SiteConfig::$EMAIL_PASSWORD = 's1m0np4ss18';
