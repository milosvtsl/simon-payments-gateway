<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 11:04 PM
 */

namespace View;


abstract class AbstractView
{
    const DEFAULT_TITLE = 'Page Title';

    abstract protected function renderHTMLBody();

    protected function getTitle() { return static::DEFAULT_TITLE; }

    public function renderHTML() {

?><!DOCTYPE html>
<html lang="en">
    <?php $this->renderHTMLHead(); ?>
    <?php $this->renderHTMLBody(); ?>
</html>
<?php

}

    protected function renderHTMLHead()
    {

?>
    <head>
        <title><?php echo $this->getTitle(); ?></title>

        <?php $this->renderHTMLMetaTags(); ?>
        <?php $this->renderHTMLHeadLinks(); ?>



        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
        <script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

    </head>
<?php

    }


    protected function renderHTMLFooter($title=null)
    {

?>
<?php

    }

    protected function renderHTMLHeadLinks() {
        ?>
        <link href="assets/css/main.css" type="text/css" rel="stylesheet" media="screen, projection" />
        <link href="assets/css/main-responsive.css" type="text/css" rel="stylesheet" media="screen, projection" />
        <link href="assets/css/theme_light.css" type="text/css" rel="stylesheet" media="screen, projection" />
        <link href="assets/fonts/style.css" type="text/css" rel="stylesheet" media="screen, projection" />
        <link href="assets/css/login.css" type="text/css" rel="stylesheet" media="screen, projection" />
        <?php
    }
    protected function renderHTMLMetaTags() {
        ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <!-- start: META -->
        <meta charset="utf-8">
        <meta name="layout" content="login">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,IE=9,IE=8,chrome=1"/>
        <![endif]-->
        <?php
    }
}
