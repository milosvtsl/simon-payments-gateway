<?php
$button_current = 'integration';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';
    ?>

    <article class="themed">

        <section class="content logout-section">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="/" class="nav_home">Home</a>
                <a href="user/logout.php" class="nav-log-out">Log Out</a>
            </aside>



                <div class="box-login">

                    <form name="form-login" action='user/logout.php?action=logout' method='POST' class="themed">

                        <fieldset style="display: inline-block;">
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