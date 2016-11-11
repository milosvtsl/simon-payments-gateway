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
    const FLAG_HEADER_MINIMAL = 0x01;
    const FLAG_FOOTER_MINIMAL = 0x02;

    abstract public function renderHTMLHeadScripts($flags=0);
    abstract public function renderHTMLHeadLinks($flags=0);
    abstract public function renderHTMLMetaTags($flags=0);

    abstract public function renderHTMLBodyHeader($flags=0);
    abstract public function renderHTMLBodyFooter($flags=0);

    abstract public function printHTMLMenu($category, $action_url=null);
}

