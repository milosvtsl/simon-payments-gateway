<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Subscription\Model;


class SubscriptionQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM subscription s
LEFT JOIN order_item oi on s.order_item_id = oi.id
LEFT JOIN merchant m on oi.merchant_id = m.id
";

    public function getCount() {
        return $this->count;
    }


}