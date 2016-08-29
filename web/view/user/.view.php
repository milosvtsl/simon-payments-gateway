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
        <form class="form-view-user themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <input type="submit" value="User List" />
                <input type="submit" value="Edit" />
                <input type="submit" value="Delete" />
                <input type="submit" value="Change Password" />
            </fieldset>
            <fieldset>
                <legend>User Information</legend>
                <?php
                $User = $this->getUser();
                $odd = false;
                ?>
                <table class="table-user-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
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
                        <td>Time Zone</td>
                        <td></td>
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