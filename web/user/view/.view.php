<?php
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
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="user?" class="button">User List <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="<?php echo $action_url; ?>view" class="button current">View <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
        <?php } else { ?>
            <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
        <?php } ?>

        <a href="user/logout.php" class="button">Log Out <div class="submenu-icon submenu-icon-logout"></div></a>
    </nav>
    
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_home">Home</a>
        <a href="user" class="nav_user">Users</a>
        <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-user themed" onsubmit="return false;">
            <fieldset style="display: inline-block;">
                <legend>User Information</legend>
                <table class="table-user-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $User->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Username</td>
                        <td><?php echo $User->getUsername(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Name</td>
                        <td><?php echo $User->getFullName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $User->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchants</td>
                        <td><?php
                            $MerchantQuery = \Merchant\Model\MerchantRow::queryAll();
                            foreach($User->queryUserMerchants() as $Merchant) {
                                /** @var \Merchant\Model\MerchantRow $Merchant */
                                echo "<a href='merchant?id=" . $Merchant->getID() . "'>"
                                    . $Merchant->getShortName()
                                    . "</a><br/>";
                            } ?>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Roles</td>
                        <td><?php
                            /** @var \User\Model\UserAuthorityRow $Role */
                            foreach($User->queryRoles() as $Role) {
                                echo "<a href='role.php?id=" . $Role->getID() . "'>"
                                    . $Role->getAuthority()
                                    . "</a><br/>";
                            } ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>