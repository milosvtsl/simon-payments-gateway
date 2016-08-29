<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:05 PM
 */
namespace View\Theme;

abstract class AbstractViewTheme
{
    private $_nav_links = array();
    private $_crumb_link = array();

    abstract public function renderHTMLHeadScripts();
    abstract public function renderHTMLHeadLinks();
    abstract public function renderHTMLMetaTags();

    abstract public function renderHTMLBodyHeader();
    abstract public function renderHTMLBodyFooter();

    public function addNavLink($url, $name) {
        $this->_nav_links[] = array($url, $name);
    }
    public function addCrumbLink($url, $name) {
        $this->_crumb_link[] = array($url, $name);
    }

    protected function getNavLinkHTML() {
        foreach($this->_nav_links as $arr) {
            list($url, $name) = $arr;
            $selected = '';
            if(basename($url) === basename($_SERVER["SCRIPT_FILENAME"]))
                $selected = " class='current'";
            yield "<a href='{$url}'" . $selected . ">{$name}</a>";
        }
    }

    protected function getCrumbLinkHTML() {
        foreach($this->_crumb_link as $arr) {
            list($url, $name) = $arr;
            yield "<a href='" . $url ."'>" . $name . "</a>";
        }
    }
}

