<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\SPG;

use Config\SiteConfig;
use View\Theme\AbstractViewTheme;

class DefaultViewTheme extends AbstractViewTheme
{

    public function renderHTMLBodyHeader()
    {
        ?>
        <body>
        <header>
            <a href="/">
                <h1><?php echo SiteConfig::$SITE_NAME; ?></h1>
            </a>

        </header>
        <nav>
            <a href="home.php">Home</a>
            <a href="merchant.php">Merchant</a>
            <a href="user.php">User</a>
            <a href="news.php">News</a>
            <a href="charge.php">Charge</a>
            <a href="search.php">Search</a>
        </nav>
        <article>
        <?php
    }

    public function renderHTMLBodyFooter()
    {
        ?>
        </article>
        </body>
        <?php
    }

    // Static

    public static function get() {
        static $inst = null;
        return $inst ?: $inst = new static();
    }

    public function renderHTMLHeadScripts() {
        // TODO: Implement renderHTMLHeadScripts() method.
    }

    public function renderHTMLHeadLinks() {
        // TODO: Implement renderHTMLHeadLinks() method.
    }

    public function renderHTMLMetaTags() {
        // TODO: Implement renderHTMLMetaTags() method.
    }
}