<?php
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;
?>

<!-- Page Navigation -->
<nav class="page-menu hide-on-print">
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
        <a href="user?" class="button">Users <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="user/add.php" class="button current">Add User <div class="submenu-icon submenu-icon-add"></div></a>
    <?php } ?>
    <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
    <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
    <a href="order?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
        <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="integration?" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
    <?php } ?>
</nav>

    <article class="themed">

        <section class="content">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="user" class="nav_user">Users</a>
                <a href="user/add.php" class="nav_user_add">Add New User</a>
            </aside>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-add-user themed" method="POST" action="user/add.php">
                <input type="hidden" name="action" value="edit" />
                <fieldset style="display: inline-block;">
                    <legend>New User Fields</legend>
                    <table class="table-user-info themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Username</td>
                            <td><input type="text" name="username" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Password</td>
                            <td><input type="password" name="password" value="" autocomplete="off" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Password Confirm</td>
                            <td><input type="password" name="password_confirm" value="" autocomplete="off" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><input type="email" name="email" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">First Name</td>
                            <td><input type="text" name="fname" value="" required/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Last Name</td>
                            <td><input type="text" name="lname" value="" required/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Create User</td>
                            <td><input type="submit" value="Create" /></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>