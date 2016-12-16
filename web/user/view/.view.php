<?php
use Merchant\Model\MerchantRow;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;
$action_url = 'user?id=' . $User->getID() . '&action=';
$category = $User->getID() == $SessionUser->getID() ? 'user-account' : 'user-view';

$Theme = $this->getTheme();
$Theme->addPathURL('user',          'Users');
$Theme->addPathURL($action_url,     $User->getUsername());
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu($category,    $action_url);
?>

    <article class="themed">

            <section class="content">

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form class="form-view-user themed" method="POST">
                    <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                    <fieldset style="position: relative">

                        <a href="user?action=edit&id=<?php echo $User->getID(); ?>">
                            <div class="app-button app-button-edit app-button-top-right">
                            </div>
                        </a>

                        <div class="legend">User Information</div>
                        <?php $odd = true; ?>
                        <table class="table-user-info themed striped-rows" style="width: 100%;">
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

<?php $Theme->renderHTMLBodyFooter(); ?>