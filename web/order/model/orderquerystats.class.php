<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Order\Model;



class OrderQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
";

    public function getCount() {
        return $this->count;
    }


}