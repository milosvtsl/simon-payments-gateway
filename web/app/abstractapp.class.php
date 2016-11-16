<?php
namespace App;
use User\Session\SessionManager;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */


abstract class AbstractApp {

    /**
     * Print an HTML representation of this app
     * @param array $params
     * @return mixed
     */
    abstract function printAppHTML(Array $params=array());

    /**
     * Get App Name
     * @return string
     */
    public function getAppName() {
        $class = get_class($this);
        return
            ucwords(
                str_replace('\\', ' ',
                    substr($class, strlen(__NAMESPACE__)+1)
                )
            );
    }

    /**
     * Get App Key
     * @return string
     */
    protected function getAppKey() {
        $class = get_class($this);
        return
            strtolower(
                str_replace('\\', '_',
                    substr($class, strlen(__NAMESPACE__)+1)
                )
            );
    }


    /**
     * @return \User\Model\UserRow
     */
    function getSessionUser() {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        return $SessionUser;
    }
}

