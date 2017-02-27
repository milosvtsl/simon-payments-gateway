<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 11:04 PM
 */

namespace User\Session;


use System\Config\DBConfig;
use System\Config\SiteConfig;
use User\Model\GuestUser;
use User\Model\UserRow;

class SessionManager
{
    const SESSION_ID = 'id';
    const SESSION_IS_GUEST = 'is_guest';
    const SESSION_KEY = '_spg';
    const SESSION_OLD = '_old';
    const SESSION_MESSAGE_KEY = __CLASS__;

    private static $_session_user = null;


    public function isLoggedIn() {
        if (isset(
            $_SESSION,
            $_SESSION[self::SESSION_KEY],
            $_SESSION[self::SESSION_KEY][self::SESSION_ID]))
                return true;

        if(SiteConfig::$SITE_AUTO_LOGIN_ENABLED) {
            $this->loginGuestAccount(SiteConfig::$SITE_AUTO_LOGIN_ACCOUNT);
            return true;
        }

        return false;
    }

    public function isGuestAccount() {
        if (isset(
            $_SESSION,
            $_SESSION[self::SESSION_KEY],
            $_SESSION[self::SESSION_KEY][self::SESSION_IS_GUEST]))
            return true;

        return false;
    }

    /**
     * @param $username
     * @param $password
     * @return UserRow
     */
    public function login($username, $password) {
        $User = UserRow::fetchByUsernameOrEmail($username);
        if(!$User)
            throw new \InvalidArgumentException("Username not found: " . $username);

        $User->validatePassword($password);

        self::$_session_user = $User;

        session_regenerate_id(true);
        session_write_close();
        session_start();

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
        session_destroy();
        return true;
    }

    function loginGuestAccount($username) {
        $User = UserRow::fetchByUsername($username);
        self::$_session_user = $User;
        @session_regenerate_id(true);
        @session_write_close();
        @session_start();
        // Reset login session data
        $_SESSION[static::SESSION_KEY] = array (
            static::SESSION_ID => $User->getID(),
            static::SESSION_IS_GUEST => true,
        );

        return $User;
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

        $DB = DBConfig::getInstance();
        $tz = $User->getTimeZone();
        $ret = $DB->exec("SET time_zone='{$tz}'");

        return $User;
    }


    public function adminLoginAsUser(UserRow $User) {
        if($User->hasAuthority('ADMIN'))
            throw new \Exception("Only non-admin accounts may be logged into");

        $SessionUser = $this->getSessionUser();
        if(!$SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN'))
            throw new \Exception("Only admins and sub-admins may log in as other users");

        self::$_session_user = $User;

        // Reset login session data
        $old = $_SESSION[static::SESSION_KEY];
        $_SESSION = array(
            static::SESSION_KEY => array (
                static::SESSION_ID => $User->getID(),
                static::SESSION_OLD => $old
            )
        );

        return $User;
    }

    public function setMessage($message) {
        $_SESSION[static::SESSION_MESSAGE_KEY] = $message;
    }

    public function hasMessage() {
        return isset($_SESSION, $_SESSION[static::SESSION_MESSAGE_KEY]);
    }


    public function popMessage() {
        $message = $_SESSION[static::SESSION_MESSAGE_KEY];
        unset($_SESSION[static::SESSION_MESSAGE_KEY]);
        return $message;
    }

    // Static

    public static function get() {
        static $inst = null;
        return $inst ?: $inst = new SessionManager();
    }

    public function isDebugMode() {
        if(SiteConfig::$DEBUG_MODE)
            return true;

        $SessionUser = $this->getSessionUser();
        if($SessionUser->hasAuthority("DEBUG"))
            return true;

        return false;
    }

}


