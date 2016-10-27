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

        <?php if($SessionUser->getID() !== $User->getID()) { ?>

            <a href="<?php echo $action_url; ?>view" class="button current">View User<div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button">Edit User<div class="submenu-icon submenu-icon-edit"></div></a>
            <a href="<?php echo $action_url; ?>delete" class="button">Delete User<div class="submenu-icon submenu-icon-delete"></div></a>
        <?php } else { ?>

            <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
                <a href="transaction/charge.php" class="button">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
            <?php } ?>
            <a href="user/account.php" class="button current">My Account <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="user/account.php?action=edit" class="button">Edit Account <div class="submenu-icon submenu-icon-account"></div></a>
        <?php } ?>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="user?" class="button">Users <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user/add.php" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
            <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="integration?" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>

            <a href="order?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>

        <?php if($SessionUser->getID() === $User->getID()) { ?>
            <a href="user/logout.php" class="button">Log out<div class="submenu-icon submenu-icon-logout"></div></a>
        <?php } ?>

        </nav>

        <article class="themed">
            <section class="content">
                <!-- Bread Crumbs -->
                <aside class="bread-crumbs">
                    <a href="user" class="nav_user">Users</a>
                    <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
                </aside>

                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form class="form-view-user themed" method="POST">
                    <fieldset style="display: inline-block;">
                        <legend>User Information</legend>
                        <table class="table-user-info themed striped-rows">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">ID</td>
                                <td class="value"><?php echo $User->getID(); ?></td>
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
                                    foreach($User->queryUserMerchants() as $Merchant) {
                                        /** @var \Merchant\Model\MerchantRow $Merchant */
                                        echo "<a href='merchant?id=" . $Merchant->getID() . "'>"
                                            . $Merchant->getShortName()
                                            . "</a><br/>";
                                    } ?>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Roles</td>
                                <td class="value">
                                    <table class="themed striped-rows ">
                                        <tbody>
                                        <tr>
                                            <th>Auth</th>
                                            <th>Name</th>
                                            <th>Revoke</th>
                                        </tr>
                                        <?php
                                        foreach($User->getAuthorityList() as $auth=>$name)
                                            echo "<tr><td>", $auth, "</td><td>", $name, "</td><td><button disabled='disabled'>X</button></td></th>";
                                        ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN') && $SessionUser->getID() !== $User->getID()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Admin Access</td>
                                <td class="value"><input type="submit" value="Login" name="action" /></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>