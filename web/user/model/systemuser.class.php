<?php
/**
 * Created by PhpStorm.
 * User: Nobi
 * Date: 12/17/2016
 * Time: 11:38 PM
 */
namespace User\Model;

class SystemUser extends UserRow
{
    protected $id = -2;
    protected $email = 'system@email.com';
    protected $enabled = 0;
    protected $fname = 'System';
    protected $lname = 'User';
    protected $username = 'system';
}