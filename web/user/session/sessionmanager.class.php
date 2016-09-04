<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 11:04 PM
 */

namespace User\Session;


use User\Model\GuestUser;
use User\Model\UserRow;

class SessionManager
{
    const SESSION_ID = 'id';
    const SESSION_KEY = '_spg';

    private static $_session_user = null;


    public function isLoggedIn() {
        return isset(
            $_SESSION,
            $_SESSION[self::SESSION_KEY],
            $_SESSION[self::SESSION_KEY][self::SESSION_ID]);
    }

    /**
     * @param $username
     * @param $password
     * @return UserRow
     */
    public function login($username, $password)
    {
        $User = UserRow::fetchByUsername($username);
        if(!$User)
            throw new \InvalidArgumentException("Username not found: " . $username);

        $User->validatePassword($password);
        self::$_session_user = $User;

        // Reset login session data
        $_SESSION[static::SESSION_KEY] = array (
             static::SESSION_ID => $User->getID()
        );

        return $User;
    }

    public function logout() {
        $_SESSION[static::SESSION_KEY] = null;
        self::$_session_user = null;
    }

    /**
     * @return UserRow
     */
    public function getSessionUser() {
        if(self::$_session_user)
            return self::$_session_user;

        if(!$this->isLoggedIn())
            return new GuestUser();

        $id = $_SESSION[self::SESSION_KEY][self::SESSION_ID];
        $User = UserRow::fetchByID($id);
        if(!$User)
            throw new \InvalidArgumentException("Session ID User not found: " . $id);

        self::$_session_user = $User;
        return $User;
    }

    // Static

    public static function get() {
        static $inst = null;
        return $inst ?: $inst = new SessionManager();
    }
}


