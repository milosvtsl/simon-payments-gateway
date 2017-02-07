<?php
use Merchant\Model\MerchantRow;
use Subscription\Model\SubscriptionRow;
use View\AbstractListView;

/**
 * @var PDOStatement $ReportQuery
 * @var AbstractListView $this
 * @var PDOStatement $Query
 **/

$action_url = 'subscription/list.php?' . http_build_query($_GET);

$Theme = $this->getTheme();
$Theme->addPathURL('subscription',             'Subscriptions');
$Theme->addPathURL('subscription/list.php',    'Search');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('order-subscription-list');
?>
    <article class="themed">

        <section class="content">

            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form name="form-subscription-search" class="themed">

                <fieldset class="search-fields">
                    <div class="legend">Search</div>
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
                    <div class="legend">Search Results</div>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="subscription?<?php echo $this->getSortURL(SubscriptionRow::SORT_BY_ID); ?>">ID</a></th>
                            <th>Amount</th>
                            <th>Count</th>
                            <th>Frequency</th>
                            <th>Customer/ID</th>
                            <th><a href="subscription?<?php echo $this->getSortURL(SubscriptionRow::SORT_BY_STATUS); ?>">Status</a></th>
                            <th><a href="subscription?<?php echo $this->getSortURL(SubscriptionRow::SORT_BY_DATE); ?>">Next Date</a></th>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
                            <th class="hide-on-layout-narrow"><a href="subscription?<?php echo $this->getSortURL(SubscriptionRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                            <?php } ?>
                        </tr>
                        <?php
                        $odd = false;

                        // Get Timezone diff
                        $offset = $SessionUser->getTimeZoneOffset('now');

                        /** @var \Subscription\Model\SubscriptionRow $Subscription */
                        foreach($Query as $Subscription) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='subscription?uid=<?php echo $Subscription->getUID(); ?>'><?php echo $Subscription->getID(); ?></a></td>
                            <td style="font-weight: bold;"><?php echo $Subscription->getRecurAmount(); ?></td>
                            <td style="font-weight: bold;"><?php echo $Subscription->getRecurCount(); ?></td>
                            <td style="font-weight: bold;"><?php echo $Subscription->getRecurFrequency(); ?></td>
                            <td style="max-width: 8em;"><?php echo $Subscription->getCustomerFullName(); ?></td>
                            <td><?php echo $Subscription->getStatus(); ?></td>
                            <td><?php echo date("M dS h:i A", strtotime($Subscription->getRecurNextDate()) + $offset); ?></td>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
                            <td class="hide-on-layout-narrow"><a href='merchant?id=<?php echo $Subscription->getMerchantID(); ?>'><?php echo $Subscription->getMerchantShortName(); ?></a></td>
                            <?php } ?>
                        </tr>
                        <?php } ?>

                        <tr>
                            <td colspan="5" class="pagination">
                                <?php $this->printPagination('subscription?'); ?>
                            </td>
                            <td colspan="3" style="text-align: right">
                                <button name="action" type="submit" value="Export-Data" class="themed">Export Subscriptions (.csv)</button>

                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>