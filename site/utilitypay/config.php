<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:26 PM
 */

use System\Config\DBConfig;
use System\Config\SiteConfig;

// Database Config
//DBConfig::$DB_HOST = 'localhost';
DBConfig::$DB_NAME = 'utilitypay';
DBConfig::$DB_USERNAME = 'paylogic2';
DBConfig::$DB_PASSWORD = 'eVw{P7mphBn';

// Site Config
SiteConfig::$SITE_UID_PREFIX = "UP";
SiteConfig::$SITE_NAME = "UtilityPay.org";
SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Resident";
SiteConfig::$SITE_URL = "https://" . $host;
SiteConfig::$DEFAULT_THEME = 'View\Theme\UtilityPay\UtilityPayViewTheme';

// Email Config
SiteConfig::$EMAIL_SERVER_HOST = 'relay-hosting.secureserver.net'; // smtpout.secureserver.net
SiteConfig::$EMAIL_SERVER_PORT = 465; // 3535   80  25
SiteConfig::$EMAIL_SMTP_AUTH = false; // true;
SiteConfig::$EMAIL_SMTP_SECURE = 'ssl'; // 'tls';
SiteConfig::$EMAIL_USERNAME = 'support@utilitypay.org';
SiteConfig::$EMAIL_PASSWORD = 's1m0np4ss18';
SiteConfig::$EMAIL_FROM_ADDRESS = 'support@utilitypay.org';
