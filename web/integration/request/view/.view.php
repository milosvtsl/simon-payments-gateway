<?php
use Integration\Request\View\IntegrationRequestView;
/**
 * @var IntegrationRequestView $this
 **/
$Request = $this->getRequest();
$odd = false;
$action_url = 'integration/request?id=' . $Request->getID() . '&action=';
?>
    <section class="message">
        <h1>View Integration Request</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View an integration request...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-integration-request themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="integration?" class="button">Integrations</a>
                <a href="integration/request?" class="button">Request List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
            </fieldset>
            <fieldset>
                <legend>Request Information</legend>
                <table class="table-integration-request-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Request->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Integration</td>
                        <td><a href='integration?id=<?php echo $Request->getIntegrationID(); ?>'><?php echo $Request->getIntegrationName(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Type</td>
                        <td><a href='integration/request?type_id=<?php echo $Request->getIntegrationTypeID(); ?>'><?php echo $Request->getIntegrationType(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Type ID</td>
                        <td>
                            <a href='<?php echo strtolower($Request->getIntegrationType()); ?>?id=<?php echo $Request->getIntegrationTypeID(); ?>'>
                                <?php echo $Request->getIntegrationTypeID(); ?>
                            </a>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Request</td>
                        <td><?php echo $Request->getRequest(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Response</td>
                        <td><?php echo $Request->getResponse(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Result</td>
                        <td><?php echo $Request->getResult(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Date</td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Request->getDate())); ?></td>
                    </tr>

                </table>
            </fieldset>
        </form>
    </section>