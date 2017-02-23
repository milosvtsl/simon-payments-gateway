<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:26 PM
 */

use System\Config\DBConfig;
use System\Config\SiteConfig;


// Per Git Branch Config

$GIT_BRANCH = @file('.git/HEAD', FILE_USE_INCLUDE_PATH);
$GIT_BRANCH = @$GIT_BRANCH[0] ?: null;
if($GIT_BRANCH) {
    $GIT_BRANCH = explode("/", $GIT_BRANCH, 3);
    $GIT_BRANCH = @$GIT_BRANCH[2] ?: null;
}

switch(trim($GIT_BRANCH)) {
    case 'dev':
        SiteConfig::$DEBUG_MODE = true;
        break;
}

// Per Domain Config

$host = parse_url('http://' . (@$_SERVER['HTTP_HOST']), PHP_URL_HOST);

switch($host) {
    // Simon Payments Gateway
    default:
    case 'access.simonpayments.com':
        include __DIR__ . '/site/spg/config.php';
        SiteConfig::$SITE_LIVE = TRUE;
        break;

    case 'demo.simonpayments.com':
        include __DIR__ . '/site/spg/config.php';
        break;

    case 'localhost':
    case 'dev.simonpayments.com':
        SiteConfig::$DEBUG_MODE = true;
        include __DIR__ . '/site/spg/config.php';
        break;

    // Court Pay
    case 'dev.courtpay.org':
        include __DIR__ . '/site/courtpay/config.php';
        SiteConfig::$DEBUG_MODE = true;
        break;

    case 'courtpay.org':
    case 'access.courtpay.org':
    case 'demo.courtpay.org':
        include __DIR__ . '/site/courtpay/config.php';
        break;


    // Utility Pay
    case 'dev.utilitypay.org':
        include __DIR__ . '/site/utilitypay/config.php';
        SiteConfig::$DEBUG_MODE = true;
        break;

    case 'utilitypay.org':
    case 'access.utilitypay.org':
    case 'demo.utilitypay.org':
        include __DIR__ . '/site/utilitypay/config.php';
        break;
}
