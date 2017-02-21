<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/2017
 * Time: 10:16 AM
 */
namespace Integration\Mock;

use Integration\Model\IntegrationRow;

class MockIntegrationRow extends IntegrationRow
{
    protected $id = -1;
    protected $uid = 'TEST-TEST';
    protected $name = "Test Integration Row";
    protected $class_path = MockIntegration::_CLASS;
//    protected $api_url_base;
//    protected $api_username;
//    protected $api_password;
//    protected $api_app_id;
//    protected $api_type;
//    protected $notes;

    // Calculated

    protected $request_success = -1;
    protected $request_fail = -1;
    protected $request_total = -1;
}