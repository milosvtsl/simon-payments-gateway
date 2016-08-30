<?php
/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = '?id=' . $Merchant->getID() . '&action=';
?>
    <section class="message">
        <h1>View Merchant</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View a Merchant Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-merchant themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>

                <input type="submit" value="Merchant List" onclick="document.location.href = '?';" />
                <input type="submit" value="Edit" onclick="document.location.href = '<?php echo $action_url; ?>edit';"/>
                <input type="submit" value="Delete" onclick="document.location.href = '<?php echo $action_url; ?>delete';"/>
                <input type="submit" value="Change Password" onclick="document.location.href = '<?php echo $action_url; ?>change';"/>
            </fieldset>
            <fieldset>
                <legend>Merchant Information</legend>
                <table class="table-merchant-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Merchant->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchantname</td>
                        <td><?php echo $Merchant->getMerchantname(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Name</td>
                        <td><?php echo $Merchant->getFullName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><?php echo $Merchant->getEmail(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Merchant->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchants</td>
                        <td><?php
                            /** @var \Merchant\MerchantRow $Merchant */
                            foreach($Merchant->queryMerchants() as $Merchant) {
                                echo "<a href='merchant.php?id=" . $Merchant->getID() . "'>"
                                    . $Merchant->getShortName()
                                    . "</a><br/>";
                            } ?>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Roles</td>
                        <td><?php
                            /** @var \Merchant\MerchantAuthorityRow $Role */
                            foreach($Merchant->queryRoles() as $Role) {
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