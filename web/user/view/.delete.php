<?php
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;

/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/


$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();
$User = $this->getUser();

$odd = false;
$action_url = 'user/index.php?uid=' . $User->getUID() . '&action=';
$category = 'user-delete';

$Theme = $this->getTheme();
$Theme->addPathURL('user',          'Users');
$Theme->addPathURL($action_url.'delete',     'Delete');
$Theme->addPathURL($action_url,     $User->getUsername());
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


                    <div class="page-buttons order-page-buttons hide-on-print">
                        <a href="<?php echo $action_url; ?>view" class="page-button page-button-view">
                            <div class="app-button large app-button-view" ></div>
                            View
                        </a>
                        <a href="<?php echo $action_url; ?>edit" class="page-button page-button-edit">
                            <div class="app-button large app-button-edit" ></div>
                            Edit
                        </a>
                        <?php if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) { ?>
                            <a href="<?php echo $action_url; ?>delete" class="page-button page-button-delete disabled">
                                <div class="app-button large app-button-delete" ></div>
                                Delete
                            </a>
                        <?php } ?>
                    </div>


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

                        <?php if($User->getMerchantID()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant</td>
                            <td class="value"><?php
                                echo "<a href='merchant?uid=" . $User->getMerchantUID() . "'>"
                                    . $User->getMerchantName()
                                    . "</a><br/>";
                                ?>
                            </td>
                        </tr>
                        <?php } ?>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Roles</td>
                            <td class="value"><?php
                                foreach($User->getAuthorityList() as $auth) {
                                    $name = ucwords(str_replace('_', ' ', strtolower($auth)));
                                    echo $name, " &nbsp;(", $auth, ")<br/>";
                                }
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
                            <td>
                                <button type="submit" class="themed" value="delete" name="action" onclick="if(!confirm('Are you sure you want to delete this user: <?php echo $User->getEmail(); ?>?')) return false;">Delete</button>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>