<?php /** @var \User\View\LoginView $this  **/
$odd = true;
// Render Header
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader(\View\Theme\AbstractViewTheme::FLAG_HEADER_MINIMAL);
?>



<article class="themed">



    <section class="content login-section">

        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='form-login'>
            <fieldset style="display: inline-block; padding: 1em 1.5em;">
                <legend>LOGIN</legend>

                <table class="table-user-info themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Username</td>
                        <td>
                            <input type="text" name="username" id="username"  placeholder="Username"  value="" autofocus class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Password</td>
                        <td>
                            <input type="password" name="password" id="password"  placeholder="Password" autocomplete="off" class="themed" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" value="SUBMIT" />
                            <br>
                            PASSWORD RESET
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
