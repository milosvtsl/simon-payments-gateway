<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 11:04 PM
 */

namespace View;


use System\Config\SiteConfig;
use View\Theme\AbstractViewTheme;

abstract class AbstractView
{
    const VIEW_PATH = '?';
    const VIEW_NAME = 'Unnamed View';

    const SESSION_MESSAGE_KEY = 'session-message';
    const DEFAULT_TITLE = null;

    /** @var \Exception */
    private $_exception = null;
    private $_message = null;
    private $_theme = null;
    private $_title = null;

    public function __construct($title=null, AbstractViewTheme $Theme=null) {
        $this->_theme = $Theme;
        $this->_title = $title;
    }

    abstract protected function renderHTMLBody(Array $params);

    abstract public function processFormRequest(Array $post);

    protected function getTitle()       { return $this->_title ?: static::DEFAULT_TITLE ?: SiteConfig::$SITE_NAME; }

    public function setException(\Exception $ex)       {
        $this->_exception = $ex;
    }
    /** @return String
     * @deprecated
     */
    public function getMessage() {
        if ($this->_exception)
            return $this->_exception->getMessage();
        if ($this->_message !== null)
            return $this->_message;
        if (!$this->hasSessionMessage())
            return NULL;
        return $this->popSessionMessage();
    }
    public function hasMessage() {
        if ($this->_exception !== null)
            return true;
        if ($this->_message !== null)
            return true;
        if ($this->hasSessionMessage())
            return true;
        return false;
    }



    /**
     * @deprecated
     */
    public function setSessionMessage($message) {
        $_SESSION[static::SESSION_MESSAGE_KEY] = $message;
    }

    /**
     * @deprecated
     */
    public function hasSessionMessage() {
        return isset($_SESSION, $_SESSION[static::SESSION_MESSAGE_KEY]);
    }

    public function popSessionMessage() {
        $message = $_SESSION[static::SESSION_MESSAGE_KEY];
        unset($_SESSION[static::SESSION_MESSAGE_KEY]);
        return $message;
    }


    /** @return AbstractViewTheme */
    public function getTheme()          { return $this->_theme ?: SiteConfig::getDefaultViewTheme(); }
    public function setTheme(AbstractViewTheme $Theme) { $this->_theme = $Theme; }

    private static $rendered_head = false;

    public function renderHTML($params=null) {
        if(!$params)
            $params = $_GET;

        if(!self::$rendered_head) {
            if($this->_exception)
                header('HTTP/1.1 400 ' . $this->_exception->getMessage());

            echo "<!DOCTYPE html>\n";
            echo "<html lang='en'>\n";
            $this->renderHTMLHead();
        }
        self::$rendered_head = true;
        $this->renderHTMLBody($params);
        echo "</html>";
    }

    protected function renderHTMLHead()
    {
        echo "\t<head>\n";
        echo "\t\t<title>", $this->getTitle(), "</title>\n";
        echo "\t\t<base href='", SiteConfig::$BASE_HREF, "'>\n";
        $this->renderHTMLMetaTags();
        $this->renderHTMLHeadLinks();
        $this->renderHTMLHeadScripts();
        echo "\n\t</head>\n";
    }


    protected function renderHTMLHeadLinks() {
//        echo "\t\t<link href='assets/css/general.css' type='text/css' rel='stylesheet' />\n";
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
                $this->processFormRequest($_POST);
                break;

            default:
                throw new \InvalidArgumentException("Unknown method: " . $_SERVER['REQUEST_METHOD']);
        }

    }


    public function redirectRequest() {
        header("Location: " . static::VIEW_PATH);
    }
}
