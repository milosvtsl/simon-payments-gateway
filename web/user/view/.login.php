<?php /** @var \User\View\LoginView $this  **/ ?>
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

                <form class="form-login" action='login.php?action=login' method='POST' id='loginForm'>

                    <?php if($this->hasException()) { ?>
                        <div class="errorHandler alert alert-danger">
                            <div><?php echo $this->getException()->getMessage(); ?></div>
                        </div>

                    <?php } else if ($this->hasSessionMessage()) { ?>
                        <div class="alert">
                            <div><?php echo $this->popSessionMessage(); ?></div>
                        </div>
                    <?php } ?>

                    <fieldset>
                        <div class="form-group">
                                    <span class="input-icon">
                                        <input type="text" name="username" id="username" class="form-control wdt03" placeholder="Username" clickev="true" value="" autofocus />
                                        <i class="fa fa-user"></i>
                                    </span>
                        </div>

                        <div class="form-group form-actions">
                                    <span class="input-icon">
                                        <input type="password" name="password" id="password" class="form-control password wdt03" placeholder="Password" autocomplete="off" />
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
                                <a href="login.php?action=reset" class="forgot-password">Forgot Your Password?</a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="copyright">
                &copy; 2016 Simon Payments, LLC. All Rights Reserved
            </div>
        </div>
    </body>
