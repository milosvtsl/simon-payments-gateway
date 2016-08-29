<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 11:04 PM
 */

namespace View;


use Config\SiteConfig;
use View\Theme\AbstractViewTheme;
use View\Theme\SPG\DefaultViewTheme;

abstract class AbstractView
{
    const SESSION_MESSAGE_KEY = 'session-message';
    const DEFAULT_TITLE = null;

    /** @var \Exception */
    private $_exception = null;
    private $_theme = null;
    private $_title = null;

    public function __construct($title=null, AbstractViewTheme $Theme=null) {
        $this->_theme = $Theme;
        $this->_title = $title;
    }

    abstract protected function renderHTMLBody(Array $params);

    abstract protected function processRequest(Array $post);

    protected function getTitle()       { return $this->_title ?: static::DEFAULT_TITLE ?: SiteConfig::$SITE_NAME; }

    public function setException($ex)   { $this->_exception = $ex; }
    public function getException()      { return $this->_exception; }
    public function hasException()      { return $this->_exception !== null; }

    public function setSessionMessage($message) {
        $_SESSION[static::SESSION_MESSAGE_KEY] = $message;
    }

    public function hasSessionMessage() {
        return isset($_SESSION, $_SESSION[static::SESSION_MESSAGE_KEY]);
    }

    public function popSessionMessage() {
        $message = $_SESSION[static::SESSION_MESSAGE_KEY];
        unset($_SESSION[static::SESSION_MESSAGE_KEY]);
        return $message;
    }

    public function processAndRedirect($post) {
        try {
            $this->processRequest($post);
        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }


    /** @return AbstractViewTheme */
    public function getTheme()          { return $this->_theme ?: SiteConfig::getDefaultViewTheme(); }
    public function setTheme(AbstractViewTheme $Theme) { $this->_theme = $Theme; }

    public function renderHTML($params=null) {
        if(!$params)
            $params = $_GET;

        if($this->_exception)
            header('HTTP/1.1 400 ' . $this->_exception->getMessage());

        echo "<!DOCTYPE html>\n";
        echo "<html lang='en'>\n";
        $this->renderHTMLHead();
        $this->renderHTMLBody($params);
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
        echo "\t\t<link href='assets/css/general.css' type='text/css' rel='stylesheet' />\n";
        $this->getTheme()->renderHTMLHeadLinks();
    }

    protected function renderHTMLHeadScripts() {
//        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
//        <!--[if lt IE 9]>
//        echo "\t\t<script src='https://html5shim.googlecode.com/svn/trunk/html5.js'></script>\n";
//        <![endif]-->
        $this->getTheme()->renderHTMLHeadScripts();
    }

    protected function renderHTMLMetaTags() {
        echo "\t\t<meta charset='utf-8' />\n";
        $this->getTheme()->renderHTMLMetaTags();
    }

    public function handleRequest() {
        switch(strtoupper($_SERVER['REQUEST_METHOD'])) {

            // Handle GET Requests
            case 'GET':
                $this->renderHTML($_GET);
                break;

            // Handle POST Requests
            case 'POST':
                $this->processAndRedirect($_POST);
                break;

            default:
                throw new \InvalidArgumentException("Unknown method: " . $_SERVER['REQUEST_METHOD']);
        }

    }
}
