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

    protected $group;
    protected $group_name;
    protected $group_span;
    protected $count;
    protected $start_date;
    protected $end_date;

    protected $total;
    protected $total_count;

    protected $settled_total;
    protected $settled_count;
    protected $void_total;
    protected $void_count;
    protected $return_total;
    protected $return_count;

    protected $convenience_fee_total;
    protected $convenience_fee_count;

    const SQL_GROUP_BY = "\n\tGROUP BY DATE_FORMAT(oi.date, '%Y%m')";
    const SQL_ORDER_BY = "\n\tORDER BY `date` ASC";
    const SQL_SELECT = "
SELECT
  count(*) as count,

  DATE_FORMAT(oi.date, '%Y/%m') as 'group',
  DATE_FORMAT(oi.date, '%b %y') as 'group_name',
  CONCAT(DATE_FORMAT(MIN(oi.date), '%b %D %Y'), ' - ', DATE_FORMAT(DATE_ADD(MAX(oi.date), INTERVAL 1 DAY), '%b %D %Y')) as 'group_span',
  DATE_FORMAT(MIN(oi.date), '%Y-%m-%d') as start_date,
  DATE_FORMAT(DATE_ADD(MAX(oi.date), INTERVAL 1 DAY), '%Y-%m-%d') as end_date,

  sum(CASE WHEN oi.status IN ('Authorized', 'Settled') THEN oi.amount ELSE 0 END) as total,
  sum(CASE WHEN oi.status IN ('Authorized', 'Settled') THEN 1 ELSE 0 END) as total_count,

  sum(CASE WHEN oi.status = 'Settled' THEN oi.amount ELSE 0 END) as settled_total,
  sum(CASE WHEN oi.status = 'Settled' THEN 1 ELSE 0 END) as settled_count,

  sum(CASE WHEN oi.status = 'Void' THEN oi.amount ELSE 0 END) as void_total,
  sum(CASE WHEN oi.status = 'Void' THEN 1 ELSE 0 END) as void_count,

  sum(CASE WHEN oi.status = 'Return' THEN oi.amount ELSE 0 END) as return_total,
  sum(CASE WHEN oi.status = 'Return' THEN 1 ELSE 0 END) as return_count,

  sum(CASE WHEN oi.status IN ('Authorized', 'Settled') THEN oi.convenience_fee ELSE 0 END) as convenience_fee_total,
  sum(CASE WHEN oi.status IN ('Authorized', 'Settled') THEN oi.convenience_fee>0 ELSE 0 END) as convenience_fee_count

FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
LEFT JOIN subscription s on oi.subscription_id = s.id

";

    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }

    public function getCount() { return $this->count; }
    public function getGroup() { return $this->group; }
    public function getGroupName() { return $this->group_name; }
    public function getGroupSpan() { return $this->group_span; }

    public function getTotal() { return $this->total; }
    public function getTotalCount() { return $this->total_count; }

    public function getSettledTotal() { return $this->settled_total; }
    public function getSettledCount() { return $this->settled_count; }

    public function getVoidTotal() { return $this->void_total; }
    public function getVoidCount() { return $this->void_count; }

    public function getReturnTotal() { return $this->return_total; }
    public function getReturnCount() { return $this->return_count; }

    public function getConvenienceFeeCount() { return $this->convenience_fee_count; }
    public function getConvenienceFeeTotal() { return $this->convenience_fee_total; }

}