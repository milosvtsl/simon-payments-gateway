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
        <a href="integration?" class="button">Integrations</a>
        <a href="integration/request?" class="button">Requests</a>
        <a href="<?php echo $action_url; ?>view" class="button current">View #<?php echo $Integration->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit #<?php echo $Integration->getID(); ?></a>
    </nav>
    
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="integration" class="nav_integration">Integrations</a>
        <a href="<?php echo $action_url; ?>view" class="nav_integration_view"><?php echo $Integration->getName(); ?></a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-integration themed" onsubmit="return false;">
            <fieldset>
                <legend>Integration Information</legend>
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
                        <td><?php echo $Integration->getName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Class Path</td>
                        <td><?php echo $Integration->getClassPath(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API Username</td>
                        <td><?php echo $Integration->getAPIUsername(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API Password</td>
                        <td><?php echo $Integration->getAPIPassword(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>API URL Base</td>
                        <td><?php echo $Integration->getAPIURLBase(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td colspan="2">
                            <pre><?php echo $Integration->getNotes() ?: "No Notes"; ?></pre>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>