<?php
namespace View\Login;

use View\AbstractView;


class Auth extends AbstractView {

    protected function renderHTMLBody()
    {

?>
    <body class="login">

        <div class="main-login col-sm-4 col-sm-offset-4">
            <div class="logo">

                <img src="assets/images/paylogic-logo.png" width="173" height="64" alt="PayLogic Networks Inc." />
            </div>

            <div class="box-login">
                <h3>Sign in to your account</h3>

                <p>
                    Please enter your name and password to log in.
                </p>

                <form class="form-login" action='/paylogic-web/j_spring_security_check' method='POST' id='loginForm'>


                    <fieldset>
                        <div class="form-group">
        <span class="input-icon">
          <input type="text" name="j_username" id="username" class="form-control wdt03" placeholder="Username" clickev="true" value="" />
          <i class="fa fa-user"></i>
        </span>
                        </div>

                        <div class="form-group form-actions">
        <span class="input-icon">
          <input type="password" name="j_password" id="password" class="form-control password wdt03" placeholder="Password" clickev="true" value="" />
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
        <script>
            jQuery(document).ready(function() {
                Main.init();
                Login.init();
            });
        </script>
</body>
    <?php

    }
}

