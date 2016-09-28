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
    abstract public function renderHTMLHeadScripts();
    abstract public function renderHTMLHeadLinks();
    abstract public function renderHTMLMetaTags();

    abstract public function renderHTMLBodyHeader();
    abstract public function renderHTMLBodyFooter();

}

