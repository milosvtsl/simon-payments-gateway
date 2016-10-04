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
    <nav class="page-menu">
        <a href="user?" class="button">User List</a>
        <a href="<?php echo $action_url; ?>view" class="button">View #<?php echo $User->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Edit #<?php echo $User->getID(); ?></a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="user" class="nav_user">Users</a>
        <a href="<?php echo $action_url; ?>view" class="nav_user_view"><?php echo $User->getUsername(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="nav_user_edit">Edit</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>edit">
            <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
            <input type="hidden" name="action" value="edit" />
            <fieldset>
                <legend>Edit User Fields</legend>
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
                        <td>UID</td>
                        <td><?php echo $User->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Username</td>
                        <td><input type="text" name="username" value="<?php echo $User->getUsername(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><input type="text" name="email" value="<?php echo $User->getEmail(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>First Name</td>
                        <td><input type="text" name="fname" value="<?php echo $User->getFirstName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Last Name</td>
                        <td><input type="text" name="lname" value="<?php echo $User->getLastName(); ?>" /></td>
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
                            $MerchantQuery = \Merchant\Model\MerchantRow::queryAll();
                            foreach($MerchantQuery as $Merchant)
                                /** @var \Merchant\Model\MerchantRow $Merchant */
                                echo "<label><input type='checkbox' value='", $Merchant->getID(), "'",
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