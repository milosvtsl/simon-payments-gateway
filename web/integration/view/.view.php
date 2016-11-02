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
        <a href="integration?" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="integration/request?" class="button">Requests <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="<?php echo $action_url; ?>view" class="button current">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
    </nav>


    <article class="themed">
        <section class="content">

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="integration" class="nav_integration">Integration</a>
                <a href="<?php echo $action_url; ?>view" class="nav_integration_view"><?php echo $Integration->getName(); ?></a>
            </aside>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-integration themed" onsubmit="return false;">
                <fieldset>
                    <legend>Integration Information</legend>
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
                            <td><?php echo $Integration->getName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Class Path</td>
                            <td><?php echo $Integration->getClassPath(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API Username</td>
                            <td><?php echo $Integration->getAPIUsername(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API Password</td>
                            <td><?php echo $Integration->getAPIPassword(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">API URL Base</td>
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
    </article>