<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Order\Model;

class BatchQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;
    protected $batch_id;
    protected $merchant_id;
    protected $merchant_short_name;
    protected $amount;
    protected $max_date;
    protected $min_date;

    const SQL_GROUP_BY = "\n\tGROUP BY oi.batch_id";
    const SQL_ORDER_BY = "\n\tORDER BY oi.batch_id desc";
    const SQL_SELECT = "
SELECT count(*) count,
    oi.batch_id,
    oi.merchant_id,
    m.short_name as merchant_short_name,
    sum(amount) amount,
    MAX(oi.date) max_date,
    MIN(oi.date) min_date
FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
WHERE oi.status IN ('Settled')
";

    public function getCount()              { return $this->count; }
    public function getBatchID()            { return $this->batch_id; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShrtName()   { return $this->merchant_short_name; }
    public function getAmount()             { return $this->amount; }
    public function getStartDate()            { return $this->max_date; }
    public function getEndDate()            { return $this->min_date; }
}