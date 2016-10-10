<?php
use Order\Model\OrderRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="order?" class="button current">Orders</a>
        <a href="transaction/charge.php?" class="button">Charge</a>
    </nav>
    
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="order" class="nav_order">Orders</a>
        <a href="order/list.php" class="nav_order_list">Search</a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-search themed">
            <fieldset class="search-fields">
                <legend>Search</legend>
                <table>
                    <tbody>
                    <tr>
                        <th>From</th>
                        <td>
                            <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
                            <input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
                        </td>
                    </tr>
                    <tr>
                        <th>Limit</th>
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
                                $MerchantQuery = \Merchant\Model\MerchantRow::queryAll();
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
                        <th>Value</th>
                        <td>
                            <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="UID, MID, Amount, Card, Name, Invoice ID" size="33" />
                            <input type="submit" value="Search" />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small striped-rows">
                    <tr>
                        <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_ID); ?>">ID</a></th>
                        <th>Amount</th>
                        <th>Customer</th>
                        <th>Mode</th>
                        <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_DATE); ?>">Date</a></th>
                        <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_INVOICE_NUMBER); ?>">Invoice ID</a></th>
                        <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_STATUS); ?>">Status</a></th>
                        <th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                    </tr>
                    <?php
                    /** @var \Order\Model\OrderRow $Order */
                    $odd = false;
                    foreach($Query as $Order) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='order?uid=<?php echo $Order->getUID(); ?>'><?php echo $Order->getID(); ?></a></td>
                        <td><?php echo $Order->getAmount(); ?></td>
                        <td><?php echo $Order->getCardHolderFullName(); ?></td>
                        <td><?php echo ucfirst($Order->getEntryMode()); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Order->getDate())); ?></td>
                        <td><?php echo $Order->getInvoiceNumber(); ?></td>
                        <td><?php echo $Order->getStatus(); ?></td>
                        <td><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>

                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('order?'); ?>

                <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>
            </fieldset>
        </form>
    </section>