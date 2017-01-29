<?php
use Integration\View\IntegrationView;

/**
 * @var IntegrationView $this
 **/
$Integration = $this->getIntegration();
$odd = false;
$action_url = 'integration?id=' . $Integration->getID() . '&action=';
$this->getTheme()->printHTMLMenu('integration-edit', $action_url);
?>


    <article class="themed">

        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="integration" class="nav_integration">Integration</a>
            <a href="<?php echo $action_url; ?>view" class="nav-integration-view"><?php echo $Integration->getName(); ?></a>
            <a href="<?php echo $action_url; ?>edit" class="nav-integration-edit">Edit</a>
        </aside>
        <section class="content">


            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-integration themed" method="POST">
                <fieldset>
                    <div class="legend">Edit Integration Fields</div>
                    <table class="table-integration-info themed striped-rows">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">ID</td>
                            <td><?php echo $Integration->getID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">UID</td>
                            <td><?php echo $Integration->getUID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td><input type="text" name="name" size="32" value="<?php echo $Integration->getName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Class Path</td>
                            <td><input type="text" name="class_path" size="32" value="<?php echo $Integration->getClassPath(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API Username</td>
                            <td><input type="text" name="api_username" size="32" value="<?php echo $Integration->getAPIUsername(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API Password</td>
                            <td><input type="text" name="api_password" size="32" value="<?php echo $Integration->getAPIPassword(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API App ID</td>
                            <td><input type="text" name="api_app_id" size="32" value="<?php echo $Integration->getAPIAppID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API URL Base</td>
                            <td><input type="text" name="api_url_base" size="32" value="<?php echo $Integration->getAPIURLBase(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Notes</td>
                            <td><textarea name="notes" rows="16" cols="34" ><?php echo $Integration->getNotes(); ?></textarea></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Update</td>
                            <td><input type="submit" value="Update" /></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>