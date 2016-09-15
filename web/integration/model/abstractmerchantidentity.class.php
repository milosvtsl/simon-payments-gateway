<?php
namespace Integration\Model;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/14/2016
 * Time: 8:14 PM
 */
abstract class AbstractMerchantIdentity {
    private $merchant;

    abstract function getID();
    abstract function getCreateDate();

    public function __construct(MerchantRow $Merchant, IntegrationRequestRow $Request) {
        if(!$Request->isRequestSuccessful())
            throw new IntegrationException("Merchant Request was not successful");
        $this->request_data = $Request->parseResponseData();
        $this->merchant = $Merchant;
    }

    public function getRequestData() { return $this->request_data; }

    public function getMerchant() { return $this->merchant; }
}