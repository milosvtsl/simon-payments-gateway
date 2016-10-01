<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 12:56 PM
 */
namespace Integration\Mock\Test;

use Integration\Mock\MockIntegration;
use Integration\Model;
use Integration\Model\IntegrationRow;

class TestMockIntegrationRow extends IntegrationRow
{

    public function __construct(Array $params = array()) {
        parent::__construct(array(
            'id' => 1,
            'uid' => '459ae330-e892-43ce-a3f2-3bae893df26380',
            'name' => 'Mock Integration',
            'class_path' => MockIntegration::_CLASS,
            'api_url_base' => null,
            'api_username' => null,
            'api_password' => null,
            'api_app_id' => null,
            'notes' => null,
        ) + $params);
    }

}