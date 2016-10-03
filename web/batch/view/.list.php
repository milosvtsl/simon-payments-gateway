<?php
use \Batch\Model\BatchRow;
use \Merchant\Model\MerchantRow;
/**
 * @var \View\AbstractListView $this
 **/?>

    <!-- Page Navigation -->
    <nav class="page-menu">
        <a href="transaction?" class="button">Transactions</a>
        <a href="order?" class="button">Orders</a>
        <a href="batch?" class="button current">Batches</a>
        <a href="user/logout.php" class="button">Log Out</a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="batch?" class="nav_batch">Batches</a>
        <a href="batch/list.php" class="nav_batch_list">Search</a>
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
                                $MerchantQuery = MerchantRow::queryAll();
                                foreach($MerchantQuery as $Merchant)
                                    /** @var MerchantRow $Merchant */
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
                            <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="UID, Batch ID, Status" size="33" />
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
                        <th><a href="batch?<?php echo $this->getSortURL(BatchRow::SORT_BY_ID); ?>">ID</a></th>
                        <th><a href="batch?<?php echo $this->getSortURL(BatchRow::SORT_BY_BATCH_ID); ?>">Batch</a></th>
                        <th><a href="batch?<?php echo $this->getSortURL(BatchRow::SORT_BY_DATE); ?>">Date</a></th>
                        <th>Orders</th>
                        <th>Total</th>
                        <th><a href="batch?<?php echo $this->getSortURL(BatchRow::SORT_BY_BATCH_STATUS); ?>">Status</a></th>
                        <th><a href="batch?<?php echo $this->getSortURL(BatchRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                    </tr>
                    <?php
                    $odd = false;
                    foreach($this->getListQuery() as $Batch) {
                        /** @var \Batch\Model\BatchRow $Batch */ ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='batch?id=<?php echo $Batch->getID(); ?>'><?php echo $Batch->getID(); ?></a></td>
                        <td><?php echo $Batch->getBatchID(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Batch->getDate())); ?></td>
                        <td><?php echo $Batch->getOrderCount(); ?></td>
                        <td><?php echo $Batch->getOrderAmount() ? number_format ($Batch->getOrderTotal(), 2) : '(0)'; ?></td>
                        <td><?php echo $Batch->getBatchStatus(); ?></td>
                        <td><a href='merchant?id=<?php echo $Batch->getMerchantID(); ?>'><?php echo $Batch->getMerchantShortName(); ?></a></td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('batch?'); ?>
            </fieldset>
        </form>
    </section>