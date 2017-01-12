<?php
use Integration\Request\View\IntegrationRequestView;
/**
 * @var IntegrationRequestView $this
 **/
$Request = $this->getRequest();
$odd = false;
$action_url = 'integration/request?id=' . $Request->getID() . '&action=';
$Theme = $this->getTheme();
$Theme->addPathURL('integration',                   'Integration');
$Theme->addPathURL('integration/request',           'Requests');
$Theme->addPathURL($action_url,                     $Request->getID());
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('integration-request-view',    $action_url);
?>
    <article class="themed">

        <section class="content">

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-integration-request themed" onsubmit="return false;">
                <fieldset>
                    <div class="legend">Request Information</div>
                    <table class="table-integration-request-info themed striped-rows">
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
                            <td><?php echo $Request->getIntegrationType(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Date</td>
                            <td><?php echo date("M dS Y G:i:s", strtotime($Request->getDate())); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>URL</td>
                            <td><a href="<?php echo $Request->getRequestURL(); ?>" target="_blank"><?php echo $Request->getRequestURL(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Result</td>
                            <td><?php echo $Request->getResult(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Code</td>
                            <td><?php echo $Request->getResponseCode(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Message</td>
                            <td><?php echo $Request->getResponseMessage(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Merchant</td>
                            <td><a href='merchant?id=<?php echo $Request->getMerchantID(); ?>'><?php echo $Request->getMerchantName(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Order</td>
                            <td><a href='order?id=<?php echo $Request->getOrderItemID(); ?>'><?php echo $Request->getOrderItemID(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Transaction</td>
                            <td><a href='transaction?id=<?php echo $Request->getTransactionID(); ?>'><?php echo $Request->getTransactionID(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>User</td>
                            <td class="hide-on-layout-narrow"><a href='user?uid=<?php echo $Request->getUserUID(); ?>'><?php echo $Request->getUserID(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Request</td>
                            <td>
                                <textarea rows="30" cols="58" onclick="this.rows++; this.cols+=3;"><?php echo $Request->getRequest(); ?></textarea>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Response</td>
                            <td>
                                <textarea rows="30" cols="58" onclick="this.rows++; this.cols+=3;"><?php echo $Request->getResponse(); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>