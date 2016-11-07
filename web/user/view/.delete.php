<?php
use Merchant\Model\MerchantRow;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;
$action_url = '/user/index.php?id=' . $User->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>

        <?php if($SessionUser->getID() !== $User->getID()) { ?>
            <a href="<?php echo $action_url; ?>view" class="button">View User<div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button">Edit User<div class="submenu-icon submenu-icon-edit"></div></a>
            <a href="<?php echo $action_url; ?>delete" class="button current">Delete User<div class="submenu-icon submenu-icon-delete"></div></a>
        <?php } else { ?>

            <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="user/account.php?action=edit" class="button">Edit Account <div class="submenu-icon submenu-icon-edit"></div></a>
        <?php } ?>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user?" class="button">Users <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user/add.php" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
        <?php } ?>

        <?php if($SessionUser->getID() === $User->getID()) { ?>
            <a href="user/logout.php" class="button">Log out<div class="submenu-icon submenu-icon-logout"></div></a>
        <?php } ?>

    </nav>


<article id="article" class="themed">

    <section id="content" class="content">
        <a name='content'></a>
        <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="user" class="nav_user">Users</a>
                <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
                <a href="<?php echo $action_url; ?>delete" class="nav_user_delete">Delete</a>
            </aside>
            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>delete">
                <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                <input type="hidden" name="action" value="delete" />
                <fieldset>
                    <legend>Delete User: <?php echo $User->getFullName(); ?></legend>
                    <table class="table-user-info themed striped-rows">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">ID</td>
                            <td class="value"><?php echo $User->getID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">UID</td>
                            <td class="value"><?php echo $User->getUID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Username</td>
                            <td class="value"><?php echo $User->getUsername(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td class="value"><?php echo $User->getFullName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td class="value"><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">UID</td>
                            <td class="value"><?php echo $User->getUID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchants</td>
                            <td class="value"><?php
                                if($SessionUser->hasAuthority('ROLE_ADMIN'))
                                    $MerchantQuery = MerchantRow::queryAll();
                                else
                                    $MerchantQuery = $SessionUser->queryUserMerchants();
                                foreach($User->queryUserMerchants() as $i=>$Merchant)
                                    /** @var \Merchant\Model\MerchantRow $Merchant */
                                    echo "<a href='merchant?id=" . $Merchant->getID() . "'>"
                                        . $Merchant->getShortName()
                                        . "</a><br/>";
                                ?>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Roles</td>
                            <td class="value"><?php
                                foreach($User->getAuthorityList() as $auth=>$name)
                                    echo $name, " &nbsp;(", $auth, ")<br/>";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><hr/>Are you sure you want to permanently delete this user?<hr/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name"><?php echo $SessionUser->getUsername(); ?> Password</td>
                            <td><input type="password" name="admin_password" value="" required autocomplete="on" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Delete</td>
                            <td><input type="submit" value="Delete" class="themed" /></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>