<?php
use Transaction\Model\TransactionRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/?>
    <section class="content">
        <div class="action-fields">
            <a href="transaction?" class="button current">Transactions</a>
            <a href="order?" class="button">Orders</a>
            <a href="transaction/charge.php?" class="button">Charge</a>
        </div>
        <form class="form-search themed">
            <fieldset class="search-fields">
                <legend>Search</legend>
                <table style="min-width: 40em;">
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
                                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="TID, MID, Amount, Card Number, Batch ID" size="33" />

                                <input type="submit" value="Search" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small">
                    <tr>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_ID); ?>">ID</a></th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_ORDER_ITEM); ?>">Order</a></th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_BATCH_ITEM); ?>">Batch</a></th>
                        <th>Card Holder</th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_DATE); ?>">Date</a></th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_INVOICE_NUMBER); ?>">Invoice ID</a></th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_USERNAME); ?>">Username</a></th>
                        <th>Amount</th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_STATUS); ?>">Status</a></th>
                        <th><a href="transaction?<?php echo $this->getSortURL(TransactionRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                    </tr>
                    <?php
                    /** @var \Transaction\Model\TransactionRow $Transaction */
                    $odd = false;
                    foreach($Query as $Transaction) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>'><?php echo $Transaction->getID(); ?></a></td>
                        <td><?php if($Transaction->getOrderID()) { ?><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'><?php echo $Transaction->getOrderID(); ?></a><?php } else echo 'N/A'; ?></td>
                        <td><?php if($Transaction->getBatchID()) { ?><a href='batch?id=<?php echo $Transaction->getBatchID(); ?>'><?php echo $Transaction->getBatchID(); ?></a><?php } else echo 'N/A'; ?></td>
                        <td><?php echo $Transaction->getHolderFullFullName(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getTransactionDate())); ?></td>
                        <td><?php echo $Transaction->getInvoiceNumber(); ?></td>
                        <td><?php echo $Transaction->getUsername(); ?></td>
                        <td><?php echo $Transaction->getAmount(); ?></td>
                        <td><?php echo $Transaction->getStatus(); ?></td>
                        <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('transaction?'); ?>


                <?php if($this->hasException()) { ?>
                    <h5><?php echo $this->hasException(); ?></h5>

                <?php } else if ($this->hasSessionMessage()) { ?>
                    <h5><?php echo $this->popSessionMessage(); ?></h5>

                <?php } else if($this->hasMessage()) { ?>
                    <h6><?php echo $this->getMessage() ?></h6>

                <?php } else { ?>
                    <h5>Search for Transaction Accounts...</h5>

                <?php } ?>

            </fieldset>
        </form>
    </section>