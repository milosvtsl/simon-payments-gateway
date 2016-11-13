<?php
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader();

$this->getTheme()->printHTMLMenu('user-logout');
?>
    <article class="themed">
        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="user/logout.php" class="nav-log-out">Log Out</a>
        </aside>


        <section class="content logout-section">


                <div class="box-login">

                    <form name="form-login" action='user/logout.php?action=logout' method='POST' class="themed">

                        <fieldset>
                            <legend>LOGOUT</legend>
                            <input type="hidden" name="action" value="logout" />
                            <div class="form-actions">
                                <div class="clearfix">
                                    <input type="submit" class="themed" value="Logout" />
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
        </section>
    </article>

<?php
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyFooter();
?>