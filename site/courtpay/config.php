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
DBConfig::$DB_NAME = 'courtpay';
DBConfig::$DB_USERNAME = 'paylogic2';
DBConfig::$DB_PASSWORD = 'eVw{P7mphBn';

// Site Config
SiteConfig::$SITE_UID_PREFIX = "CP";
SiteConfig::$SITE_NAME = "CourtPay.org";
SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Defendant";
SiteConfig::$SITE_DEFAULT_MERCHANT_NAME = "Client";
SiteConfig::$SITE_URL = "https://courtpay.org";
SiteConfig::$SITE_URL_LOGO = 'http://dev.courtpay.org/view/theme/courtpay/assets/img/logo.png';
SiteConfig::$DEFAULT_THEME = 'View\Theme\CourtPay\CourtPayViewTheme';

// Email Config
SiteConfig::$EMAIL_SERVER_HOST = 'relay-hosting.secureserver.net'; // smtpout.secureserver.net
SiteConfig::$EMAIL_SERVER_PORT = 465; // 3535   80  25
SiteConfig::$EMAIL_SMTP_AUTH = false; // true;
SiteConfig::$EMAIL_SMTP_SECURE = 'ssl'; // 'tls';
SiteConfig::$EMAIL_USERNAME = 'support@courtpay.org';
SiteConfig::$EMAIL_PASSWORD = 's1m0np4ss18';
SiteConfig::$EMAIL_FROM_ADDRESS = 'support@courtpay.org';
