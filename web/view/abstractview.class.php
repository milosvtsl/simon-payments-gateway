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

    protected function getTitle()       { return $this->_title ?: static::DEFAULT_TITLE ?: SiteConfig::$SITE_NAME; }

    public function setException($ex)   { $this->_exception = $ex; }
    public function getException()      { return $this->_exception; }
    public function hasException()      { return $this->_exception !== null; }

    public function setTheme(AbstractViewTheme $Theme) { $this->_theme = $Theme; }

    /**
     * @return AbstractViewTheme
     */
    public function getTheme()          { return $this->_theme ?: SiteConfig::getDefaultViewTheme(); }

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
}
