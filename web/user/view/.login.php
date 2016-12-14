<?php /** @var \User\View\LoginView $this  **/
$odd = true;
// Render Header
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader(\View\Theme\AbstractViewTheme::FLAG_HEADER_MINIMAL);
?>

<article>

    <section class="not-content login-section">


        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='form-login'>
            <img src="view/theme/spg/assets/img/logo_full.png" alt="Simon Payments Gateway" style="display: block; margin: auto; padding: 0.5em; width: 18em;">

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <fieldset style="display: inline-block; padding: 0.5em; margin: 0.3em; text-align: left;">

                <table class="table-user-info themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>
                            <strong style="font-size: larger;">Sign in to your account</strong>

                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>
                            <input type="text" name="username" id="username"  placeholder="Username"  value="" autofocus required class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>
                            <input type="password" name="password" id="password"  placeholder="Password" autocomplete="off" required class="themed" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td style="text-align: right;">
                            <input type="submit" value="Login" class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="login-text">
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
