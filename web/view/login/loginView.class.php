<?php
namespace View\Login;

use View\AbstractView;


class LoginView extends AbstractView {

    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='assets/css/main.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

    public function validateUsername($post)
    {
        if(!isset($post['username']))
            throw new \InvalidArgumentException("Missing field: username");
        return $post['username'];
    }

    public function validatePassword($post)
    {
        if(!isset($post['password']))
            throw new \InvalidArgumentException("Missing field: password");
        return $post['password'];
    }

    protected function renderHTMLBody(Array $params) {
        include ('.login.form.php');
    }

}

