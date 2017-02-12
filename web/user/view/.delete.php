<?php
use Merchant\Model\MerchantRow;

/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/

$odd = false;
$action_url = '/user/index.php?uid=' . $User->getUID() . '&action=';
$category = 'user-delete';

$Theme = $this->getTheme();
$Theme->addPathURL('user',          'Users');
$Theme->addPathURL($action_url,     $User->getUsername());
$Theme->addPathURL($action_url.'delete',     'Delete');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu($category,    $action_url);


?>
<article class="themed">

    <section class="content">
            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>delete">
                <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
                <input type="hidden" name="action" value="delete" />
                <fieldset>
                    <div class="legend">Delete User: <?php echo $User->getFullName(); ?></div>
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