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

    abstract public function renderHTMLBodyHeader();
    abstract public function renderHTMLBodyFooter();

    public function addNavLink($url, $name) {
        $this->_nav_links[] = array($url, $name);
    }
}

