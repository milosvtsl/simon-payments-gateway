<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 1:25 PM
 */
namespace Integration\Model;

use Merchant\Model\MerchantRow;

abstract class AbstractIntegration
{
    protected $row;

    /**
     * Get or create a Merchant Identity
     * @param MerchantRow $Merchant
     * @return IntegrationRequestParser
     */
    abstract public function createMerchantIdentity(MerchantRow $Merchant);

    public function __construct(IntegrationRow $integrationRow) {
        $this->row = $integrationRow;
    }

    /**
     * @return IntegrationRow
     */
    public function getIntegrationRow() {
        return $this->row;
    }
}