    <body class="login">
        <div class="main-login col-sm-4 col-sm-offset-4">
            <div class="logo">
                <img src="assets/images/logo-simon-payments.png" alt="PayLogic Networks Inc." />
            </div>

            <div class="box-login">
                <h3>Log out of your session</h3>

                <form class="form-login" action='?action=logout' method='POST' id='logoutForm'>

                    <fieldset>
                        <input type="hidden" name="action" value="logout" />
                        <div class="form-actions">
                            <div class="clearfix">
                                <button type="submit" class="btn btn-org pull-right">
                                    Logout <i class="fa fa-arrow-circle-right"></i>
                                </button>
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
