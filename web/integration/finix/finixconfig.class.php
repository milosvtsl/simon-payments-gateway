<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Finix;

use Integration\Model;

class FinixConfig
{
    static $UID = "24e82235-9756-4c61-abf2-be7f317f57fb";
//    static $API_BASE_URL = "https://simonpay-staging.finix.io/";
//    static $API_USERNAME = "USeP8hmJVHgE2ctoMbMxPPRv";
//    static $API_PASSWORD = "ff2b5459-4b0a-4273-91b2-b4f46a060c0c";
//    static $API_APP_ID = "APeALXKsYEYgsn9QBdHmy9hP";
}

/**

Authentication
To communicate with the SimonPay API you’ll need to authenticate your requests with a username and password. To test the API against the sandbox environment feel free to use the credentials below:

Username: USeP8hmJVHgE2ctoMbMxPPRv

Password: ff2b5459-4b0a-4273-91b2-b4f46a060c0c

You should also know your Application ID. An Application, also referred as an “App”, is a resource that represents your web app. In other words, any web service that connects buyers (i.e. customers) and sellers (i.e. merchants). This guide uses the following sandbox Application ID:

App ID: APeALXKsYEYgsn9QBdHmy9hP

 */