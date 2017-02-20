<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Exception;

use View\Error\ErrorView;
use View\Error\Mail\ErrorEmail;

class ExceptionHandler
{
    const _CLASS = __CLASS__;

    public function handleException($ex) {
        $ErrorView = new ErrorView($ex);
        $ErrorView->renderHTML();

        if(!$ex instanceof \Exception)
            $ex = new \Exception($ex);
        $ErrorEmail = new ErrorEmail($ex);
        $ErrorEmail->send();
    }

    // Static

    public static function register() {
        $Handler = new ExceptionHandler();
        return set_exception_handler(array($Handler, 'handleException'));
    }

}

