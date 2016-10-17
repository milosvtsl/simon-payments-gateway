<?php /** @var \User\View\LoginView $this  **/
$odd = true;
// Render Header
/** @var \View\AbstractView $this */
$this->getTheme()->renderHTMLBodyHeader(\View\Theme\AbstractViewTheme::FLAG_HEADER_MINIMAL);
?>

<article class="themed">

    <section class="content login-section">

        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-reset" class="themed" action='reset.php?action=reset' method='POST' id='form-reset'>
            <input type="hidden" name="action" value="reset" />
            <fieldset style="display: inline-block; padding: 1em 1.5em;">
                <legend>RESET Password</legend>

                <h3>Please enter your email address to receive a password reset link</h3>

                <table class="table-user-info themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Email</td>
                        <td>
                            <input type="email" name="email" id="email"  placeholder="Recovery Email Address"  value="" autofocus class="themed"/>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Reset</td>
                        <td>
                            <input type="submit" value="SUBMIT" class="themed"/>
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
