<?php
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/


$Theme = $this->getTheme();
$Theme->addPathURL('integration',                   'Integration');
$Theme->addPathURL('integration/request',           'Requests');
$Theme->addPathURL('integration/request/list.php',    'Search');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('integration-request-list');
?>

    <article class="themed">

        <section class="content">

            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset class="search-fields">
                    <div class="legend">Search</div>
<!--                    <legend>Search</legend>-->
                    <table>
                        <tbody>
                            <tr>
                                <td class="name">From</td>
                                <td>
                                    <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
                                    <input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Limit</td>
                                <td>
                                    <select name="limit">
                                        <?php
                                        $limit = @$_GET['limit'] ?: 10;
                                        foreach(array(10,25,50,100,250) as $opt)
                                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                        ?>
                                    </select>
                                    <select name="type" style="min-width: 11.1em;" >
                                        <option value="">By Any Type</option>
                                        <option value="transaction">Transaction</option>
                                        <option value="merchant">Merchant</option>
                                    </select>
                                    <select name="integration_id" >
                                        <option value="">By Integration</option>
                                        <?php
                                        $IntegrationQuery = IntegrationRow::queryAll();
                                        foreach($IntegrationQuery as $Integration)
                                            /** @var IntegrationRow $Integration */
                                            echo "\n\t\t\t\t\t\t\t<option value='", $Integration->getID(), "' ",
                                            ($Integration->getID() == @$_GET['integration_id'] ? 'selected="selected" ' : ''),
                                            "'>", $Integration->getName(), "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Value</td>
                                <td>
                                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="All Fields" size="33" />

                                    <input type="submit" value="Search" class="themed" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <div class="legend">Search Results</div>
<!--                    <legend>Search Results</legend>-->
                    <table class="table-results themed small striped-rows" style="width: 100%;">
                        <tr>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_INTEGRATION_ID); ?>">Integration</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE); ?>">Type</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESULT); ?>">Result</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_DATE); ?>">Date</a></th>
                            <th class="hide-on-layout-narrow">Duration</th>
                            <th class="hide-on-layout-narrow"><a href="merchant?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                            <th><a href="order?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ORDER_ITEM_ID); ?>">Order</a></th>
                            <th class="hide-on-layout-narrow"><a href="transaction?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TRANSACTION_ID); ?>">Transaction</a></th>
                            <th class="hide-on-layout-narrow"><a href="user?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_USER_ID); ?>">User</a></th>
                        </tr>
                        <?php
                        /** @var IntegrationRequestRow $Request */
                        $odd = false;
                        foreach($Query as $Request) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='integration/request?id=<?php echo $Request->getID(); ?>'><?php echo $Request->getID(); ?></a></td>
                            <td><a href='integration?id=<?php echo $Request->getIntegrationID(); ?>'><?php echo $Request->getIntegrationName(); ?></a></td>
                            <td><?php echo $Request->getIntegrationType(); ?></td>
                            <td><?php echo $Request->getResult(); ?></td>
                            <td><?php echo date("M dS Y G:i:s", strtotime($Request->getDate())); ?></td>
                            <td class="hide-on-layout-narrow"><?php echo round($Request->getDuration(), 3); ?>s</td>
                            <td class="hide-on-layout-narrow"><a href='merchant?id=<?php echo $Request->getMerchantID(); ?>'><?php echo $Request->getMerchantName(); ?></a></td>
                            <td><a href='order?id=<?php echo $Request->getOrderItemID(); ?>'><?php echo $Request->getOrderItemID(); ?></a></td>
                            <td class="hide-on-layout-narrow"><a href='transaction?id=<?php echo $Request->getTransactionID(); ?>'><?php echo $Request->getTransactionID(); ?></a></td>
                            <td class="hide-on-layout-narrow"><a href='user?id=<?php echo $Request->getUserID(); ?>'><?php echo $Request->getUserID(); ?></a></td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <fieldset class="pagination">
                    <div class="legend">Page</div>
<!--                    <legend>Page</legend>-->
                    <?php $this->printPagination('integration/request?'); ?>
                </fieldset>
            </form>
        </section>
    </article>

<?php $Theme->renderHTMLBodyFooter(); ?>