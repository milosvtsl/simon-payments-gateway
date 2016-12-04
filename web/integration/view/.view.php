<?php
use Integration\View\IntegrationView;
/**
 * @var IntegrationView $this
 **/
$Integration = $this->getIntegration();
$odd = false;
$action_url = 'integration?id=' . $Integration->getID() . '&action=';

$Theme = $this->getTheme();
$Theme->addPathURL('integration',                   'Integration');
$Theme->addPathURL($action_url,                     $Request->getID());
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('integration-view',    $action_url);
?>

    <article class="themed">

        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="integration" class="nav_integration">Integration</a>
            <a href="<?php echo $action_url; ?>view" class="nav_integration_view"><?php echo $Integration->getName(); ?></a>
        </aside>


        <section class="content">


            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-integration themed" onsubmit="return false;">
                <fieldset>
                    <div class="legend">Integration Information</div>
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