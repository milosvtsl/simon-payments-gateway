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
<!--        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>-->
        <?php if($SessionUser->hasAuthority('ROLE_POST_CHARGE', 'ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="transaction/charge.php?" class="button">Charge  <div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>

        <?php if($SessionUser->getID() !== $User->getID()) { ?>
            <a href="<?php echo $action_url; ?>view" class="button current">View User<div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button">Edit User<div class="submenu-icon submenu-icon-edit"></div></a>
            <a href="<?php echo $action_url; ?>delete" class="button">Delete User<div class="submenu-icon submenu-icon-delete"></div></a>
        <?php } else { ?>

            <a href="user/account.php#content" class="button current">My Account <div class="submenu-icon submenu-icon-account"></div></a>
            <a href="user/account.php?action=edit" class="button">Edit Account <div class="submenu-icon submenu-icon-edit"></div></a>
        <?php } ?>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="user#content" class="button">Users <div class="submenu-icon submenu-icon-user"></div></a>
            <a href="user/add.php#content" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
            <a href="merchant#content" class="button">Merchants <div class="submenu-icon submenu-icon-merchant"></div></a>
        <?php } ?>

        <?php if($SessionUser->getID() === $User->getID()) { ?>
            <a href="user/logout.php#content" class="button">Log out<div class="submenu-icon submenu-icon-logout"></div></a>
        <?php } ?>

    </nav>

        <article id="article" class="themed">
            <section id="content" class="content">
                <a name='content'></a>

                <!-- Bread Crumbs -->
                <aside class="bread-crumbs">
                    <a href="user" class="nav_user">Users</a>
                    <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
                </aside>

                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form class="form-view-user themed" method="POST">
                    <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                    <fieldset>
                        <legend>User Information</legend>
                        <?php $odd = true; ?>
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
                                <td class="name">TimeZone</td>
                                <td class="value"><?php echo str_replace('_', '', $User->getTimeZone()); ?></td>
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

                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN') && $SessionUser->getID() !== $User->getID()) { ?>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Roles</td>
                                <td class="value">
                                    <?php if(count($User->getAuthorityList()) > 0) { ?>
                                    <table class="themed striped-rows ">
                                        <tbody>
                                        <tr>
                                            <th>Auth</th>
                                            <th>Name</th>
                                            <th>Revoke</th>
                                        </tr>
                                        <?php
                                        foreach($User->getAuthorityList() as $auth=>$name)
                                            echo "<tr><td>", $auth, "</td><td>", $name, "</td><td><button><a href='/user/account.php?action=edit'>X</a></button></td></th>";
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php } else { ?>
                                    <a href='/user/account.php?action=edit'>Add Roles...</a>
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Admin</td>
                                <td class="value">
                                    <?php
                                    try {
                                        $AdminUser = \User\Model\UserRow::fetchByID($User->getAdminID());
                                        echo "<a href='/user?id=", $AdminUser->getID(), "'>", $AdminUser->getFullName(), "</a>";
                                    } catch (InvalidArgumentException $ex) {

                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Admin Access</td>
                                <td class="value"><input type="submit" class="themed" value="Login" name="action" /></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>