<?php
use Integration\View\IntegrationView;
/**
 * @var IntegrationView $this
 **/
$Integration = $this->getIntegration();
$odd = false;
$action_url = 'integration?id=' . $Integration->getID() . '&action=';
?>
    <section class="message">
        <h1>Edit <?php echo $Integration->getName(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Edit this Integration Entry...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-integration themed" method="POST">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="integration?" class="button">Integration List</a>
                <a href="<?php echo $action_url; ?>view" class="button">View</a>
            </fieldset>
            <fieldset>
                <legend>Edit Integration Fields</legend>
                <table class="table-integration-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Integration->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Integration->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Name</td>
                        <td><input type="text" name="name" size="32" value="<?php echo $Integration->getName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Class Path</td>
                        <td><input type="text" name="class_path" size="32" value="<?php echo $Integration->getClassPath(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API Username</td>
                        <td><input type="text" name="api_username" size="32" value="<?php echo $Integration->getAPIUsername(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API Password</td>
                        <td><input type="text" name="api_password" size="32" value="<?php echo $Integration->getAPIPassword(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API App ID</td>
                        <td><input type="text" name="api_app_id" size="32" value="<?php echo $Integration->getAPIAppID(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API URL Base</td>
                        <td><input type="text" name="api_url_base" size="32" value="<?php echo $Integration->getAPIURLBase(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Notes</td>
                        <td><textarea name="notes" rows="16" cols="34" ><?php echo $Integration->getNotes(); ?></textarea></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>