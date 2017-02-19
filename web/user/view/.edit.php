<?php
use Merchant\Model\MerchantRow;
use User\Model\AuthorityRow;
use User\Model\UserAuthorityRow;
use User\Model\UserRow;
use User\Session\SessionManager;

/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 **/

$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();
$User = $this->getUser();

$odd = false;
$action_url = '/user/index.php?uid=' . $User->getUID() . '&action=';
$category = $User->getID() == $SessionUser->getID() ? 'user-account-edit' : 'user-edit';

$Theme = $this->getTheme();
$Theme->addPathURL('user',          'Users');
$Theme->addPathURL($action_url,     $User->getUsername());
$Theme->addPathURL($action_url.'edit',     'Edit');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu($category,    $action_url);
?>
        <article class="themed">

            <section class="content">


                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

                <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>edit">
                    <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                    <input type="hidden" name="action" value="edit" />
                    <fieldset>
                        <div class="legend">Edit User Fields</div>


                        <div class="page-buttons order-page-buttons hide-on-print">
                            <a href="<?php echo $action_url; ?>view" class="page-button page-button-view">
                                <div class="app-button large app-button-view" ></div>
                                View
                            </a>
                            <a href="<?php echo $action_url; ?>edit" class="page-button page-button-edit disabled">
                                <div class="app-button large app-button-edit" ></div>
                                Edit
                            </a>
                        </div>

                        <hr/>


                        <table class="table-user-info themed striped-rows" style="width: 100%;">
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


<?php $Theme->renderHTMLBodyFooter(); ?>