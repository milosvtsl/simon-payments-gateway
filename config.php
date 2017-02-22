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
DBConfig::$DB_HOST = 'localhost';
DBConfig::$DB_NAME = 'paylogic';
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

// Per Domain Config
$domain = parse_url('http://' . (@$_SERVER['HTTP_HOST'] ?: 'localhost'));
$host = strtolower(@$domain['host'] ?: 'localhost');

switch($host) {
    default:
    case 'localhost':
    case 'access.simonpayments.com':
        SiteConfig::$SITE_LIVE = TRUE;
        break;

    case 'dev.simonpayments.com':
    case 'demo.simonpayments.com':
        break;

    case 'dev.courtpay.org':
        SiteConfig::$DEBUG_MODE = true;
        SiteConfig::$SITE_UID_PREFIX = "DCP";
        SiteConfig::$SITE_NAME = "CourtPay.org";
        SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Defendant";
        SiteConfig::$SITE_URL = "https://dev.courtpay.org";
        SiteConfig::$DEFAULT_THEME = 'View\Theme\CourtPay\CourtPayViewTheme';
        SiteConfig::$EMAIL_FROM_ADDRESS = 'support@courtpay.org';
        DBConfig::$DB_NAME = 'courtpay';
        break;

    case 'courtpay.org':
    case 'access.courtpay.org':
    case 'demo.courtpay.org':
        SiteConfig::$SITE_UID_PREFIX = "CP";
        SiteConfig::$SITE_NAME = "CourtPay.org";
        SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Defendant";
        SiteConfig::$SITE_URL = "https://www.courtpay.org";
        SiteConfig::$DEFAULT_THEME = 'View\Theme\CourtPay\CourtPayViewTheme';
        SiteConfig::$EMAIL_FROM_ADDRESS = 'support@courtpay.org';
        DBConfig::$DB_NAME = 'courtpay';
        break;

    case 'dev.utilitypay.org':
        SiteConfig::$DEBUG_MODE = true;
        SiteConfig::$SITE_UID_PREFIX = "DUP";
        SiteConfig::$SITE_NAME = "UtilityPay.org";
        SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Resident";
        SiteConfig::$SITE_URL = "https://dev.utilitypay.org";
        SiteConfig::$DEFAULT_THEME = 'View\Theme\UtilityPay\UtilityPayViewTheme';
        SiteConfig::$EMAIL_FROM_ADDRESS = 'support@utilitypay.org';
        DBConfig::$DB_NAME = 'utilitypay';
        break;

    case 'utilitypay.org':
    case 'access.utilitypay.org':
    case 'demo.utilitypay.org':
        SiteConfig::$SITE_UID_PREFIX = "UP";
        SiteConfig::$SITE_NAME = "UtilityPay.org";
        SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME = "Resident";
        SiteConfig::$SITE_URL = "https://www.utilitypay.org";
        SiteConfig::$DEFAULT_THEME = 'View\Theme\UtilityPay\UtilityPayViewTheme';
        SiteConfig::$EMAIL_FROM_ADDRESS = 'support@utilitypay.org';
        DBConfig::$DB_NAME = 'utilitypay';
        break;
}
