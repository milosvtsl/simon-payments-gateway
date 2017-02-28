<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use System\Config\DBConfig;

class Authority
{
    const _CLASS = __CLASS__;

    const ADMIN = 'admin';
    const SUB_ADMIN = 'sub_admin';
    const DEBUG = 'debug';
    const POST_CHARGE = 'post_charge';
    const VOID_CHARGE = 'void_charge';
    const RETURN_CHARGE = 'return_charge';

    // Static

    static $AUTHORITIES = array(
        self::ADMIN         => 'Super Admin',
        self::SUB_ADMIN     => 'Sub Admin',
        self::POST_CHARGE   => 'Post Charge',
        self::VOID_CHARGE   => 'Void Charge',
        self::RETURN_CHARGE => 'Return Charge',
        self::DEBUG         => 'Debug',
    );

}

