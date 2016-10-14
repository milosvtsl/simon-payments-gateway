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
    <a href="home?" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
        <a href="user?" class="button">User List <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="user/add.php" class="button">Add User<div class="submenu-icon submenu-icon-add"></div></a>
    <?php } else { ?>
        <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
    <?php } ?>

</nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="user" class="nav_user">Users</a>
        <a href="user/add.php" class="nav_user_add">Add New User</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-add-user themed" method="POST" action="user/add.php">
            <input type="hidden" name="action" value="edit" />
            <fieldset style="display: inline-block;">
                <legend>New User Fields</legend>
                <table class="table-user-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
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
                        <td><input type="text" name="email" value="" required /></td>
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