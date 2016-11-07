<?php /** @var \User\View\LoginView $this  **/
$odd = true;
// Render Header
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader(\View\Theme\AbstractViewTheme::FLAG_HEADER_MINIMAL);
?>



<article class="themed">


    <section class="not-content login-section">
        <img src="view/theme/spg/assets/img/logo.png" alt="Simon Payments Gateway" style="display: block; margin: auto; ">


        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='form-login'>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <fieldset style="display: inline-block; padding: 0.5em; margin: 0.3em;">
                <legend>Login Access</legend>

                <table class="table-user-info themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Username</td>
                        <td>
                            <input type="text" name="username" id="username"  placeholder="Username"  value="" autofocus required class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Password</td>
                        <td>
                            <input type="password" name="password" id="password"  placeholder="Password" autocomplete="off" required class="themed" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Login</td>
                        <td>
                            <input type="submit" value="SUBMIT" class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Reset</td>
                        <td style="font-size: smaller;">
                            <a href="/reset.php">Password Reset</a>
                        </td>
                    </tr>

                </table>
            </fieldset>
        </form>
    </section>

</article>

<?php
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader(\View\Theme\AbstractViewTheme::FLAG_FOOTER_MINIMAL);
?>
