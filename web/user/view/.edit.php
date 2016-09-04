<?php
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;
$action_url = 'user?id=' . $User->getID() . '&action=';
?>
    <section class="message">
        <h1>Edit <?php echo $User->getFullName(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Edit this User Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-user themed" method="POST" action="<?php echo $action_url; ?>edit">
            <input type="hidden" name="id" value="<?php echo $User->getID(); ?>" />
            <input type="hidden" name="action" value="edit" />
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="user?" class="button">User List</a>
                <a href="<?php echo $action_url; ?>view" class="button">View</a>
                <a href="<?php echo $action_url; ?>delete" class="button">Delete</a>
            </fieldset>
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
                        <td><input type="password" name="password" value="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Confirm Password</td>
                        <td><input type="password" name="password_confirm" value="" /></td>
                    </tr>
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
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>