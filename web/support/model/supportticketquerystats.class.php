<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Support\Model;


class SupportTicketQueryStats
{
    const _CLASS = __CLASS__;

    protected $count;

    const SQL_SELECT = "
SELECT count(*) count
FROM support_ticket st
";
//sum(action = 'Settled') settled,
//sum(action = 'Authorized') authorized,
//sum(action = 'Void') void

    public function getCount() {
        return $this->count;
    }


}