<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Merchant\Model;


use Model\AbstractQueryStats;

class MerchantQueryStats extends AbstractQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM merchant m
";
//sum(action = 'Settled') settled,
//sum(action = 'Authorized') authorized,
//sum(action = 'Void') void

    public function getCount() {
        return $this->count;
    }
}