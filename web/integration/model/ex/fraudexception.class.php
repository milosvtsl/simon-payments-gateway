<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:59 PM
 */
namespace Integration\Model\Ex;

use Order\Model\OrderRow;

class FraudException extends \Exception
{
    private $order;
    public function __construct($message, $code=null, OrderRow $Order=null)
    {
        $this->order = $Order;
        parent::__construct($message, $code);
    }

    /**
     * @return OrderRow
     */
    public function getOrder()
    {
        return $this->order;
    }
    
}