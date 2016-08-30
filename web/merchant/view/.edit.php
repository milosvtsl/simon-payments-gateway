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
        <h1>Edit <?php echo $Merchant->getFullName(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Edit this Merchant Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-merchant themed" method="POST">
            <fieldset class="action-fields">
                <legend>Actions</legend>

                <input type="submit" value="Merchant List" onclick="document.location.href = '?';" />
                <input type="submit" value="View" onclick="document.location.href = '<?php echo $action_url; ?>view';"/>
                <input type="submit" value="Delete" onclick="document.location.href = '<?php echo $action_url; ?>delete';"/>
                <input type="submit" value="Change Password" onclick="document.location.href = '<?php echo $action_url; ?>change';"/>
            </fieldset>
            <fieldset>
                <legend>Edit Merchant Fields</legend>
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
                        <td>UID</td>
                        <td><?php echo $Merchant->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchantname</td>
                        <td><input type="text" name="merchantname" value="<?php echo $Merchant->getMerchantname(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>First Name</td>
                        <td><input type="text" name="fname" value="<?php echo $Merchant->getFirstName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Last Name</td>
                        <td><input type="text" name="lname" value="<?php echo $Merchant->getLastName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><input type="text" name="email" value="<?php echo $Merchant->getEmail(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>