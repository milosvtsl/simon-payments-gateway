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

    /** @var \Exception */
    private $exception = null;

    abstract protected function renderHTMLBody();

    protected function getTitle()       { return static::DEFAULT_TITLE; }

    public function setException($ex)   { $this->exception = $ex; }
    public function getException()      { return $this->exception; }
    public function hasException()     { return $this->exception !== null; }

    public function renderHTML() {
        if($this->exception)
            header('HTTP/1.1 400 ' . $this->exception->getMessage());

        echo "<!DOCTYPE html>\n";
        echo "<html lang='en'>\n";
        $this->renderHTMLHead();
        $this->renderHTMLBody();
        echo "</html>";
    }

    protected function renderHTMLHead()
    {
        echo "\t<head>\n";
        echo "\t\t<title>", $this->getTitle(), "</title>\n";
        $this->renderHTMLMetaTags();
        $this->renderHTMLHeadLinks();
        $this->renderHTMLHeadScripts();
        echo "\t</head>\n";
    }


    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='assets/css/main.css' type='text/css' rel='stylesheet' />\n";
        echo "\t\t<link href='assets/css/main-responsive.css' type='text/css' rel='stylesheet' />\n";
        echo "\t\t<link href='assets/css/theme_light.css' type='text/css' rel='stylesheet' />\n";
        echo "\t\t<link href='assets/fonts/style.css' type='text/css' rel='stylesheet' />\n";
        echo "\t\t<link href='assets/css/login.css' type='text/css' rel='stylesheet' />\n";
        echo "\t\t<link href='assets/css/main.css' type='text/css' rel='stylesheet' />\n";
    }

    protected function renderHTMLHeadScripts() {
//        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
//        <!--[if lt IE 9]>
//        echo "\t\t<script src='https://html5shim.googlecode.com/svn/trunk/html5.js'></script>\n";
//        <![endif]-->
    }

    protected function renderHTMLMetaTags() {
        echo "\t\t<meta charset='utf-8' />\n";
    }
}
