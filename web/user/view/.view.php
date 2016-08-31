<?php
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 **/
$User = $this->getUser();
$odd = false;
$action_url = 'user?id=' . $User->getID() . '&action=';
?>
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
        <form class="form-view-user themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="user?" class="button">User List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
                <a href="<?php echo $action_url; ?>delete" class="button">Delete</a>
                <a href="<?php echo $action_url; ?>change" class="button">Change Password</a>

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
                            /** @var \Merchant\Model\MerchantRow $Merchant */
                            foreach($User->queryMerchants() as $Merchant) {
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