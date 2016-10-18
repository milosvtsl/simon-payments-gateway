<?php
use Merchant\Model\MerchantRow;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;
$action_url = 'user?id=' . $User->getID() . '&action=';
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="user?" class="button">Users <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="<?php echo $action_url; ?>view" class="button">View <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button current">Edit User<div class="submenu-icon submenu-icon-edit"></div></a>
            <a href="user/add.php" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
        <?php } else { ?>
            <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="user/account.php?action=edit" class="button">Edit Account <div class="submenu-icon submenu-icon-account"></div></a>
        <?php } ?>

        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <a href="order?" class="button">Orders <div class="submenu-icon submenu-icon-list"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="integration?" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>
    </nav>

    <article class="themed">

        <section class="content">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="/" class="nav_home">Home</a>
                <a href="user" class="nav_user">Users</a>
                <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
                <a href="<?php echo $action_url; ?>edit" class="nav_user_edit">Edit</a>
            </aside>
            <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

            <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>edit">
                <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                <input type="hidden" name="action" value="edit" />
                <fieldset style="display: inline-block;">
                    <legend>Edit User Fields</legend>
                    <table class="table-user-info themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>ID</td>
                            <td><?php echo $User->getID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>UID</td>
                            <td><?php echo $User->getUID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Username</td>
                            <td><input type="text" name="username" value="<?php echo @$_POST['username'] ?: $User->getUsername(); ?>" autofocus  /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Email</td>
                            <td><input type="text" name="email" value="<?php echo @$_POST['email'] ?: $User->getEmail(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>First Name</td>
                            <td><input type="text" name="fname" value="<?php echo @$_POST['fname'] ?: $User->getFirstName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Last Name</td>
                            <td><input type="text" name="lname" value="<?php echo @$_POST['lname'] ?: $User->getLastName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Change Password</td>
                            <td><input type="password" name="password" value="" autocomplete="off" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Confirm Password</td>
                            <td><input type="password" name="password_confirm" value="" autocomplete="off" /></td>
                        </tr>
                        <?php if(\User\Session\SessionManager::get()->getSessionUser()->hasAuthority("ROLE_ADMIN")) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Merchants</td>
                            <td>
                                <?php
                                $list = $User->getMerchantList();
                                if($SessionUser->hasAuthority('ROLE_ADMIN'))
                                    $MerchantQuery = MerchantRow::queryAll();
                                else
                                    $MerchantQuery = $SessionUser->queryUserMerchants();
                                foreach($MerchantQuery as $Merchant)
                                    /** @var \Merchant\Model\MerchantRow $Merchant */
                                    echo "<label>",
                                    "\n\t<input type='hidden' name='merchants[", $Merchant->getID(), "]' value='0' />",
                                    "\n\t<input type='checkbox' name='merchants[", $Merchant->getID(), "]' value='1'",
                                    (in_array($Merchant->getID(), $list) ? ' checked="checked"' : ''),
                                    "/>", $Merchant->getName(), "</label><br/>\n";
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Update</td>
                            <td><input type="submit" value="Update" /></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>