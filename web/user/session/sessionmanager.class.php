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
    const SESSION_OLD = '_old';

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
        if(!$this->isLoggedIn())
            return false;

        self::$_session_user = null;
        if(isset($_SESSION[self::SESSION_KEY][self::SESSION_OLD])) {
            $_SESSION[self::SESSION_KEY] = $_SESSION[self::SESSION_KEY][self::SESSION_OLD];
            return true;
        }
        $_SESSION[static::SESSION_KEY] = null;
        return true;
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

    public function adminLoginAsUser(UserRow $User) {
        $SessionUser = $this->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            throw new \Exception("Only admins may log in as other users");

        self::$_session_user = $User;

        // Reset login session data
        $old = $_SESSION[static::SESSION_KEY];
        $_SESSION[static::SESSION_KEY] = array (
            static::SESSION_ID => $User->getID(),
            static::SESSION_OLD => $old
        );

        return $User;
    }

    // Static

    public static function get() {
        static $inst = null;
        return $inst ?: $inst = new SessionManager();
    }

}


