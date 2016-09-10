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
        <h1>View Integration</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View an Integration...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-integration themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="integration?" class="button">Integration List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit <?php echo $Integration->getName(); ?></a>
            </fieldset>
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
                            Notes<br/>
                            <p><?php echo $Integration->getNotes(); ?></p>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>