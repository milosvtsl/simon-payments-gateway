<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $TransactionQuery
 **/?>
    <section class="message">
        <h1>Transaction List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($TransactionQuery) { ?>
            <h5><?php echo $TransactionQuery->rowCount() ?> Transactions found</h5>

        <?php } else { ?>
            <h5>Search for Transaction Accounts...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="transaction?" class="button">Transaction List</a>
                <a href="transaction/charge.php?" class="button">Charge</a>
            </fieldset>
            <fieldset class="search-fields">
                <legend>Search</legend>
                Search:
                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="UID, Order, Invoice, Name" />
                <select name="limit">
                    <?php
                    $limit = @$_GET['limit'] ?: 50;
                    foreach(array(10,25,50,100,250) as $opt)
                        echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                    ?>
                </select>

                <input type="submit" value="Search" />

            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Card Holder / TID</th>
                        <th>Customer ID</th>
                        <th>Invoice ID</th>
                        <th>User Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Transaction\Model\TransactionRow $Transaction */
                    $odd = false;
                    foreach($TransactionQuery as $Transaction) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>'><?php echo $Transaction->getID(); ?></a></td>
                        <td><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'><?php echo $Transaction->getOrderID(); ?></a></td>
                        <td>
                            <?php echo $Transaction->getHolderFullFullName(); ?>  <br/>
                            <?php echo $Transaction->getTransactionID(); ?>
                        </td>
                        <td><?php echo $Transaction->getCustomerID(); ?></td>
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
                <?php
                $limit = @$_GET['limit'] ?: 10;
                $page = @$_GET['page'] ?: 1;

                $args = $_GET;
                if($page > 1) {
                    $args['page'] = $page - 1;
                    $url = '?' . http_build_query($args);
                    echo "<a href='?" . http_build_query($args) . "'>Previous</a> ";
                }

                $args['page'] = $page + 1;
                $url = '?' . http_build_query($args);
                echo "<a href='transaction?" . http_build_query($args) . "'>Next</a> ";
                ?>
            </fieldset>
        </form>
    </section>