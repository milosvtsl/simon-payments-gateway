<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\SPG;

use View\Theme\AbstractViewTheme;

class SPGViewTheme extends AbstractViewTheme
{

    public function renderHTMLBodyHeader()
    {
?>
    <body>
        <header>
            <a href="/">
                <img src="assets/images/logo-simon-payments.png" alt="Simon Payments Gateway">
            </a>

            <img class="nav-user-photo" src="/paylogic-web/static/images/bg_3.png" alt="User Profile Image">
            <span class="user-info">Welcome, Sherlock Holmes</span>
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
}