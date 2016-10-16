<?php /** @var \User\View\LoginView $this  **/ ?>


    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_user">Login to your account</a>
    </aside>

    <section class="content" style="margin:6em auto; width: 400px;">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='loginForm' style="display: block;">
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
