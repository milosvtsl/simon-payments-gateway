<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Batch\Model;


use Model\AbstractQueryStats;

class BatchQueryStats extends AbstractQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM batch b
LEFT JOIN merchant m on b.merchant_id = m.id
";
//sum(action = 'Settled') settled,
//sum(action = 'Authorized') authorized,
//sum(action = 'Void') void

    public function getCount() {
        return $this->count;
    }


}