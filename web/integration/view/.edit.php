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
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <a href="integration#content" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="integration/request#content" class="button">Requests <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="<?php echo $action_url; ?>view" class="button">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
    </nav>

    <article id="article" class="themed">
        <section id="content" class="content">
            <a name='content'></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="integration" class="nav_integration">Integration</a>
                <a href="<?php echo $action_url; ?>view" class="nav-integration-view"><?php echo $Integration->getName(); ?></a>
                <a href="<?php echo $action_url; ?>edit" class="nav-integration-edit">Edit</a>
            </aside>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-integration themed" method="POST">
                <fieldset>
                    <legend>Edit Integration Fields</legend>
                    <table class="table-integration-info themed striped-rows" style="width: 98%;">
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