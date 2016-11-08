<?php
use Merchant\Model\MerchantRow;
use User\Model\UserRow;
use User\Model\AuthorityRow;
use User\Model\UserAuthorityRow;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var UserRow $User
 **/
$odd = false;
$action_url = '/user/index.php?id=' . $User->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_POST_CHARGE', 'ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="transaction/charge.php?" class="button">Charge  <div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>

        <?php if($SessionUser->getID() !== $User->getID()) { ?>
            <a href="<?php echo $action_url; ?>view" class="button">View User<div class="submenu-icon submenu-icon-view"></div></a>
            <a href="<?php echo $action_url; ?>edit" class="button current">Edit User<div class="submenu-icon submenu-icon-edit"></div></a>
            <a href="<?php echo $action_url; ?>delete" class="button">Delete User<div class="submenu-icon submenu-icon-delete"></div></a>
        <?php } else { ?>

            <a href="user/account.php#content" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
            <a href="user/account.php?action=edit" class="button current">Edit Account <div class="submenu-icon submenu-icon-edit"></div></a>
        <?php } ?>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="user#content" class="button">Users <div class="submenu-icon submenu-icon-user"></div></a>
            <a href="user/add.php#content" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
            <a href="merchant#content" class="button">Merchants <div class="submenu-icon submenu-icon-merchant"></div></a>
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
                    <a href="<?php echo $action_url; ?>edit" class="nav_user_edit">Edit</a>
                </aside>
                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>edit">
                    <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                    <input type="hidden" name="action" value="edit" />
                    <fieldset>
                        <legend>Edit User Fields</legend>
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
                                <td><input type="text" disabled="disabled" name="username" value="<?php echo @$_POST['username'] ?: $User->getUsername(); ?>" autofocus  /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Email</td>
                                <td><input type="text" name="email" value="<?php echo @$_POST['email'] ?: $User->getEmail(); ?>" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">First Name</td>
                                <td><input type="text" name="fname" value="<?php echo @$_POST['fname'] ?: $User->getFirstName(); ?>" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Last Name</td>
                                <td><input type="text" name="lname" value="<?php echo @$_POST['lname'] ?: $User->getLastName(); ?>" /></td>
                            </tr>


                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">User Timezone</td>
                                <td>
                                    <select name="timezone" required>
                                        <?php
                                        $curtimezone = date_default_timezone_get();
                                        foreach(\System\Arrays\TimeZones::$TimeZones as $timezone => $name) {
                                            try {
                                                $time = new \DateTime(NULL, new \DateTimeZone($timezone));
                                                $name .= " (" . $time->format('g:i A') . ")";
                                                $selected = $timezone === $User->getTimeZone() ? ' selected="selected"' : '';
                                                echo "\n\t\t\t<option value='{$timezone}'{$selected}>{$name}</option>";
                                            } catch (Exception $ex) {
                                                // Only show available timezones. Where did greenland go anyway
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <?php if($SessionUser->hasAuthority("ROLE_ADMIN")) { ?>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Admin</td>
                                <td>
                                    <select name="admin_id" required>
                                        <?php
                                        $SQL = UserRow::SQL_SELECT
                                            . "\n\tLEFT JOIN user_authorities ua on u.id = ua.id_user"
                                            . "\n\tLEFT JOIN authority a on a.id = ua.id_authority"
                                            . "\n\tWHERE a.authority IN ('ROLE_ADMIN', 'ROLE_SUB_ADMIN')"
                                            . "\n\tORDER BY a.authority ASC";
                                        $DB = \System\Config\DBConfig::getInstance();
                                        $stmt = $DB->prepare($SQL);
                                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                                        $stmt->setFetchMode(\PDO::FETCH_CLASS, UserRow::_CLASS);
                                        $stmt->execute();
                                        foreach($stmt as $AdminUser) {
                                            /** @var UserRow $AdminUser */
                                            $selected = $AdminUser->getID() === $User->getAdminID() ? ' selected="selected"' : '';
                                            echo "\n\t\t\t<option value='{$AdminUser->getID()}'{$selected}>{$AdminUser->getFullName()}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <?php } ?>


                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Change Password</td>
                                <td><input type="password" name="password" value="" autocomplete="off" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Confirm Password</td>
                                <td><input type="password" name="password_confirm" value="" autocomplete="off" /></td>
                            </tr>

                            <?php if($SessionUser->hasAuthority("ROLE_ADMIN", "ROLE_SUB_ADMIN")) { ?>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Authorities</td>
                                <td class="value">
                                    <?php
                                    $AuthQuery = AuthorityRow::queryAll();
                                    foreach($AuthQuery as $Authority) {
                                        if(in_array($Authority->getAuthority(), array('ROLE_ADMIN', 'ROLE_SUB_ADMIN'))
                                            && !$SessionUser->hasAuthority("ROLE_ADMIN"))
                                            continue;
                                        /** @var UserAuthorityRow $Authority */
                                        echo "<label>",
                                        "\n\t<input type='hidden' name='authority[", $Authority->getAuthority(), "]' value='0' />",
                                        "\n\t<input type='checkbox' name='authority[", $Authority->getAuthority(), "]' value='1'",
                                        ($User->hasAuthority($Authority->getAuthority()) ? ' checked="checked"' : ''),
                                        "/>", $Authority->getName(), "</label><br/>\n";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Merchants</td>
                                <td class="value">
                                    <?php
                                    $list = $User->getMerchantList();
                                    if($SessionUser->hasAuthority('ROLE_ADMIN'))
                                        $MerchantQuery = MerchantRow::queryAll();
                                    else
                                        $MerchantQuery = $SessionUser->queryUserMerchants();
                                    foreach($MerchantQuery as $Merchant)
                                        /** @var \Merchant\Model\MerchantRow $Merchant */
                                        echo "<label>",
                                        "\n\t<input type='hidden' name='merchant[", $Merchant->getID(), "]' value='0' />",
                                        "\n\t<input type='checkbox' name='merchant[", $Merchant->getID(), "]' value='1'",
                                        (in_array($Merchant->getID(), $list) ? ' checked="checked"' : ''),
                                        "/>", $Merchant->getName(), "</label><br/>\n";
                                    ?>
                                </td>
                            </tr>
                                <?php if($SessionUser->getID() != $User->getID()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name"><?php echo $SessionUser->getUsername(); ?> Password</td>
                                <td><input type="password" name="admin_password" value="" required autocomplete="on" /></td>
                            </tr>
                                <?php } ?>
                            <?php } ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Update</td>
                                <td><input type="submit" value="Update" class="themed"/></td>
                            </tr>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>