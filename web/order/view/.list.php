<?php
use Order\Model\OrderRow;
use Merchant\Model\MerchantRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/

$button_current = 'order';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';

?>

    <article class="themed">

        <section class="content">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="order" class="nav_order">Transactions</a>
                <a href="order/list.php" class="nav_order_list">Search</a>
            </aside>
            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
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
                                    $limit = @$_GET['limit'] ?: 50;
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
                            <td class="name">Value</td>
                            <td>
                                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="ID, UID, MID, Amount, Card, Name, Invoice ID" size="33" />
                                <input type="submit" value="Search" />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset style="display: inline-block;">
                    <legend>Search Results</legend>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_ID); ?>">ID</a></th>
                            <th>Amount</th>
                            <th>Customer</th>
                            <th class="hide-on-layout-vertical">Mode</th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_DATE); ?>">Date</a></th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_INVOICE_NUMBER); ?>">Invoice&nbsp;ID</a></th>
                            <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_STATUS); ?>">Status</a></th>
                            <th class="hide-on-layout-vertical"><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                        </tr>
                        <?php
                        /** @var \Order\Model\OrderRow $Order */
                        $odd = false;

                        // Get Timezone diff
                        $offset = $SessionUser->getTimeZoneOffset('now');

                        foreach($Query as $Order) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='order?uid=<?php echo $Order->getUID(); ?>#form-order-view'><?php echo $Order->getID(); ?></a></td>
                            <td>$<?php echo $Order->getAmount(); ?></td>
                            <td><?php echo $Order->getCardHolderFullName(); ?></td>
                            <td class="hide-on-layout-vertical"><?php echo ucfirst($Order->getEntryMode()); ?></td>
                            <td><?php echo date("M jS h:i A", strtotime($Order->getDate()) + $offset); ?></td>
                            <td><?php echo $Order->getInvoiceNumber(); ?></td>
                            <td><?php echo $Order->getStatus(); ?></td>
                            <td class="hide-on-layout-vertical"><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>

                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>

                <fieldset class="pagination">
                    <legend>Page</legend>
                    <table class="table-pagination themed striped-rows">
                        <tr>
                            <td colspan="2">
                                <?php $this->printPagination('order?'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php if($this->hasMessage()) echo $this->getMessage(); ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>