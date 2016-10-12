<h3>Forget Password?</h3>
        <div class="main-login col-sm-4 col-sm-offset-4">
            <div
                class="box-login                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              ">


                <p>
                    Enter your registered E-mail Id to get PIN to reset password
                </p>

                <form action="login.php?action=reset" method="post" class="form-forgot">
                    <div class="errorHandler alert alert-danger no-display">
                        <i class="fa fa-remove-sign"></i> You have some form errors. Please check below.
                    </div>
                    <fieldset style="display: inline-block;">
                        <div class="form-group">
                        <span class="input-icon">
                          <input type="text" class="form-control" name="email" placeholder="Email" value="" id="email"/>
                          <i class="fa fa-envelope"></i></span>
                        </div>

                        <div class="form-actions">
                            <a href="login.php" class="btn btn-light-grey go-back"><i
                                    class="fa fa-circle-arrow-left"></i> Back</a>

                            <input type="submit" name="_action_forgotPassword" value="Submit" class="btn btn-primary"/>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
