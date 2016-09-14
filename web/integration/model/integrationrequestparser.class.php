<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Model;

use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;

// TODO: dumb idea
abstract class IntegrationRequestParser
{
    private $row;
    private $parsed_response = null;

    abstract protected function parseResponse();

    abstract public function requestIsSuccessful();

    public function __construct(IntegrationRequestRow $RequestRow) {
        if($RequestRow->getIntegrationType() !== IntegrationRequestRow::ENUM_TYPE_MERCHANT)
            throw new \InvalidArgumentException("Only merchant requests are allowed in this class");
        $this->row = $RequestRow;
    }

    function getRequestRow() {
        return $this->row;
    }

    function getParsedResponseData() {
        if($this->parsed_response)
            return $this->parsed_response;

        $this->parsed_response = $this->parseResponse();
        if(!$this->parsed_response)
            throw new IntegrationException("Failed to parse response");
        return $this->parsed_response;
    }
}