<?php /** @var \User\View\LoginView $this  **/ ?>


    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/login.php" class="button current">Login <div class="submenu-icon submenu-icon-login"></div></a>
        <a href="/reset.php" class="button">Reset <div class="submenu-icon submenu-icon-reset"></div></a>
    </nav>


    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_home">Home</a>
        <a href="/login.php" class="nav-log-out">Log In</a>
    </aside>


    <section class="content login-section">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='form-login'>
            <fieldset style="display: inline-block; padding: 1em 1.5em;">
                <legend>Sign In to your account</legend>
                User Name:<br />
                <input type="text" name="username" id="username"  placeholder="Username"  value="" autofocus class="themed"/>

                <br />
                <br />Password:<br />
                <input type="password" name="password" id="password"  placeholder="Password" autocomplete="off" class="themed" />

                <br />
                <br />Login:<br />
                <input type="submit" value="SUBMIT" class="themed" />
            </fieldset>
        </form>
    </section>
