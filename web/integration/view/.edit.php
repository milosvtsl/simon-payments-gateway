<?php
use Integration\View\IntegrationView;
/**
 * @var IntegrationView $this
 **/
$Integration = $this->getIntegration();
$odd = false;
$action_url = 'integration?id=' . $Integration->getID() . '&action=';
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="integration?" class="button">Integrations <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="integration/request?" class="button">Requests <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="<?php echo $action_url; ?>view" class="button">View #<?php echo $Integration->getID(); ?> <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Configure #<?php echo $Integration->getID(); ?> <div class="submenu-icon submenu-icon-edit"></div></a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="integration" class="nav_integration">Integrations</a>
        <a href="<?php echo $action_url; ?>view" class="nav_integration_view"><?php echo $Integration->getName(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="nav_integration_edit">Configure</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-integration themed" method="POST">
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