<?php
namespace View\Login;

use View\AbstractView;


class Auth extends AbstractView {

    public function validateUsername($post)
    {
        if(!isset($post['username']))
            throw new \InvalidArgumentException("Missing field: username");
        return $post['username'];
    }

    public function validatePassword($post)
    {
        if(!isset($post['password']))
            throw new \InvalidArgumentException("Missing field: password");
        return $post['password'];
    }

    protected function renderHTMLBody()
    {
        // Create Session class, attempt login
        // Process POST params. render error page or redirect
        // POST Processing is not handled in views, only rendering of POST requests
?>
    <body class="login">
        <div class="main-login col-sm-4 col-sm-offset-4">
            <div class="logo">
                <img src="assets/images/logo-simon-payments.png" alt="PayLogic Networks Inc." />
            </div>

            <div class="box-login">
                <h3>Sign in to your account</h3>
                <p>
                    Please enter your name and password to log in.
                </p>

                <form class="form-login" action='login.php' method='POST' id='loginForm'>

                    <?php if($this->hasException()) { ?>
                    <div class="errorHandler alert alert-danger">
                        <i class="fa fa-remove-sign"></i>
                        Sorry, we were not able to find a user with that username and password.
                        <div><?php // echo $this->getException()->getMessage(); ?></div>
                    </div>
                    <?php } ?>

                    <fieldset>
                        <div class="form-group">
                            <span class="input-icon">
                                <input type="text" name="username" id="username" class="form-control wdt03" placeholder="Username" clickev="true" value="" />
                                <i class="fa fa-user"></i>
                            </span>
                        </div>

                        <div class="form-group form-actions">
                            <span class="input-icon">
                                <input type="password" name="password" id="password" class="form-control password wdt03" placeholder="Password" clickev="true" value="" />
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>

                        <div class="form-actions">
                            <div class="clearfix">
                                <button type="submit" class="btn btn-org pull-right">
                                    Login <i class="fa fa-arrow-circle-right"></i>
                                </button>
                            </div>
                            <div class="margin-top_10 clearfix ">
                                <a href="login.php?forgot=1" class="forgot-password">Forgot Your Password?</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="copyright">
                &copy; 2014 PayLogic Network, LLC. All Rights Reserved
            </div>
        </div>
</body>
    <?php

    }

}

