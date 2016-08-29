<?php /**
 * @var \View\User\UserView $this
 * @var PDOStatement $UserQuery
 **/?>
    <section class="message">
        <h1>View User</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View a User Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <?php
        $User = $this->getUser();
        $odd = false;
        $action_url = '?id=' . $User->getID() . '&action=';
        ?>
        <form class="form-view-user themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>

                <input type="submit" value="User List" onclick="document.location.href = '?';" />
                <input type="submit" value="Edit" onclick="document.location.href = '<?php echo $action_url; ?>edit';"/>
                <input type="submit" value="Delete" onclick="document.location.href = '<?php echo $action_url; ?>delete';"/>
                <input type="submit" value="Change Password" onclick="document.location.href = '<?php echo $action_url; ?>change';"/>
            </fieldset>
            <fieldset>
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
                        <td><?php echo $User->getEmail(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $User->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchants</td>
                        <td><?php
                            /** @var \Merchant\MerchantRow $Merchant */
                            foreach($User->queryMerchants() as $Merchant) {
                                echo "<a href='merchant.php?id=" . $Merchant->getID() . "'>"
                                    . $Merchant->getShortName()
                                    . "</a><br/>";
                            } ?>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Password Expired</td>
                        <td></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Account Expired</td>
                        <td></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Account Locked</td>
                        <td></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Account Enabled</td>
                        <td></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Roles</td>
                        <td></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>