<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Order\Model;


use Model\AbstractQueryStats;

class OrderQueryStats extends AbstractQueryStats
{
    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
";
//sum(action = 'Settled') settled,
//sum(action = 'Authorized') authorized,
//sum(action = 'Void') void

    public function getCount() {
        return $this->count;
    }


}