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
    protected $amount;
    protected $max_date;
    protected $min_date;

    const SQL_GROUP_BY = "\n\tGROUP BY oi.batch_id";
    const SQL_ORDER_BY = "\n\tORDER BY oi.batch_id desc";
    const SQL_SELECT = "
SELECT count(*) count,
batch_id,
sum(amount) amount,
MAX(oi.date) max_date,
MIN(oi.date) min_date
FROM order_item oi
WHERE oi.status IN ('Settled')
";

    public function getCount()      { return $this->count; }
    public function getBatchId()    { return $this->batch_id; }
    public function getAmount()     { return $this->amount; }
    public function getMaxDate()    { return $this->max_date; }
    public function getMinDate()    { return $this->min_date; }
}