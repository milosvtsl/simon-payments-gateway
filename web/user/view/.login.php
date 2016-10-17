<?php /** @var \User\View\LoginView $this  **/
$odd = true;
?>


<!-- Page Navigation -->
<nav class="page-menu hide-on-print">
    <a href="/login.php" class="button current">Login <div class="submenu-icon submenu-icon-login"></div></a>
    <a href="/reset.php" class="button">Reset <div class="submenu-icon submenu-icon-reset"></div></a>
</nav>

<article class="themed">



    <section class="content login-section">

        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-login" class="themed" action='login.php?action=login' method='POST' id='form-login'>
            <fieldset style="display: inline-block; padding: 1em 1.5em;">
                <legend>Sign In to your account</legend>

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
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>

</article>