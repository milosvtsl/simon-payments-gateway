<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:55 PM
 */
namespace User;

class GuestUser extends UserRow
{
    protected $id = -1;
    protected $email = 'guest@email.com';
    protected $enabled = 0;
    protected $fname = 'Guest';
    protected $lname = 'User';
    protected $username = 'guest';
}