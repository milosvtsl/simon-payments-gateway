<?php
use Order\Model\OrderRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderQueryStats;
use View\AbstractListView;
/**
 * @var OrderQueryStats $Report
 * @var PDOStatement $ReportQuery
 * @var AbstractListView $this
 * @var PDOStatement $Query
 **/

$button_current = 'order';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';

$action_url = 'order/list.php?' . http_build_query($_GET);
?>

    <article id="article" class="themed">

        <section id="content" class="content">
            <a name='content'></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="order" class="nav_order">Transactions</a>
                <a href="order/list.php" class="nav_order_list">Search</a>
            </aside>
            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form name="form-order-search" class="themed">

                <fieldset class="search-fields">
                    <legend>Search</legend>
                    <table class="themed">
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
                                <select name="merchant_id" style="min-width: 20.5em;" >
                                    <option value="">By Merchant</option>
                                    <?php
                                    if($SessionUser->hasAuthority('ROLE_ADMIN'))
                                        $MerchantQuery = MerchantRow::queryAll();
                                    else
                                        $MerchantQuery = $SessionUser->queryUserMerchants();
                                    foreach($MerchantQuery as $Merchant)
                                        /** @var \Merchant\Model\MerchantRow $Merchant */
                                        echo "\n\t\t\t\t\t\t\t<option value='", $Merchant->getID(), "' ",
                                            ($Merchant->getID() == @$_GET['merchant_id'] ? 'selected="selected" ' : ''),
                                            "'>", $Merchant->getShortName(), "</option>";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="name">Report</td>
                            <td>
                                <select name="stats_group">
                                    <?php
                                    $stats_group = @$_GET['stats_group'];
                                    foreach(array('Day', 'Week', 'Month', 'Year') as $opt)
                                        echo "<option value='{$opt}' ", $stats_group == $opt ? ' selected="selected"' : '' ,">By ", $opt, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="name">Value</td>
                            <td>
                                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="ID, UID, MID, Amount, Card, Name, Invoice ID" size="27" />
                                <input name="action" type="submit" value="Search" class="themed" />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>

                <fieldset>
                    <legend>Search Report</legend>
                    <table class="table-stats themed small striped-rows" style="width: 98%;">
                        <tr>
                            <th><?php echo @$params['stats_group'] ? @$params['stats_group'] . 'ly' : 'Range'; ?></th>
                            <th>Authorized Total</th>
                            <th>Settled</th>
                            <th>Void</th>
                            <th>Returned</th>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                            <th>Conv. Fee</th>
                            <?php } ?>
                        </tr>
                        <?php
                            $odd = false;
                            foreach($ReportQuery as $Report) {
                            $report_url = $action_url . '&date_from=' . $Report->getStartDate() . '&date_to=' . $Report->getEndDate()
                            /** @var \Order\Model\OrderQueryStats $Stats */
                        ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href="<?php echo $report_url; ?>&status="><?php echo $Report->getGroupSpan(); ?></a></td>
                            <td><a href="<?php echo $report_url; ?>&status="><?php echo number_format($Report->getTotal(),2), ' (', $Report->getTotalCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $report_url; ?>&status=Settled"><?php echo number_format($Report->getSettledTotal(),2), ' (', $Report->getSettledCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $report_url; ?>&status=Void"><?php echo number_format($Report->getVoidTotal(),2), ' (', $Report->getVoidCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $report_url; ?>&status=Return"><?php echo number_format($Report->getReturnTotal(),2), ' (', $Report->getReturnCount(), ')'; ?></a></td>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                            <td><?php echo number_format($Report->getConvenienceFeeTotal(),2), ' (', $Report->getConvenienceFeeCount(), ')'; ?></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>" style="font-weight: bold;">
                            <td><?php echo $Stats->getGroupSpan(); ?> (Total)</td>
                            <td><a href="<?php echo $action_url; ?>&status="><?php echo number_format($Stats->getTotal(),2), ' (', $Stats->getTotalCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $action_url; ?>&status=Settled"><?php echo number_format($Stats->getSettledTotal(),2), ' (', $Stats->getSettledCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $action_url; ?>&status=Void"><?php echo number_format($Stats->getVoidTotal(),2), ' (', $Stats->getVoidCount(), ')'; ?></a></td>
                            <td><a href="<?php echo $action_url; ?>&status=Return"><?php echo number_format($Stats->getReturnTotal(),2), ' (', $Stats->getReturnCount(), ')'; ?></a></td>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                                <td><?php echo number_format($Stats->getConvenienceFeeTotal(),2), ' (', $Stats->getConvenienceFeeCount(), ')'; ?></td>
                            <?php } ?>
                        </tr>

                        <tr>
                            <td colspan="6" style="text-align: right">
                                <span style="font-size: 0.7em; color: grey; float: left;">
                                    <?php if($this->hasMessage()) echo $this->getMessage(); ?>
                                </span>
                                <button name="action" type="submit" value="Export-Stats">Export Reporting (.csv)</button>
                            </td>
                        </tr>

                    </table>

                </fieldset>

                <fieldset>
                    <legend>Search Results</legend>
                    <table class="table-results themed small striped-rows" style="width: 98%;">
                        <tr>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_DATE); ?>">Date</a></th>
                            <th>Amount</th>
                            <th>Customer/ID</th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_INVOICE_NUMBER); ?>">Invoice</a></th>
                            <th class="hide-on-layout-vertical">Mode</th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_STATUS); ?>">Status</a></th>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
                            <th class="hide-on-layout-vertical"><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                            <?php } ?>
                        </tr>
                        <?php
                        /** @var \Order\Model\OrderRow $Order */
                        $odd = false;

                        // Get Timezone diff
                        $offset = $SessionUser->getTimeZoneOffset('now');

                        foreach($Query as $Order) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='order?uid=<?php echo $Order->getUID(); ?>#form-order-view'><?php echo $Order->getID(); ?></a></td>
                            <td style="max-width: 6em;"><?php echo date("M jS h:i A", strtotime($Order->getDate()) + $offset); ?></td>
                            <td style="font-weight: bold;"><?php echo $Order->getAmount(); ?></td>
                            <td style="max-width: 5em;"><?php echo $Order->getCardHolderFullName(), ($Order->getCustomerID() ? '/' . $Order->getCustomerID() : ''); ?></td>
                            <td style="max-width: 5em;"><?php echo $Order->getInvoiceNumber(); ?></td>
                            <td class="hide-on-layout-vertical"><?php echo ucfirst($Order->getEntryMode()); ?></td>
                            <td><?php echo $Order->getStatus(); ?></td>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
                            <td class="hide-on-layout-vertical"><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>

                        <tr>
                            <td colspan="5" class="pagination">
                                <?php $this->printPagination('order?'); ?>
                            </td>
                            <td colspan="3" style="text-align: right">
                                <button name="action" type="submit" value="Export-Data">Export Transactions (.csv)</button>
                                <button name="action" type="submit" value="Export">Export All (.csv)</button>

                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>