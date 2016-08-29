<?php
namespace View\Login;

use View\AbstractView;


class LogoutView extends AbstractView {

    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='assets/css/main.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

    protected function renderHTMLBody(Array $params) {
        include ('.logout.form.php');
    }

}

