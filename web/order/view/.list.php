<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $OrderQuery
 **/?>
    <section class="message">
        <h1>Order List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($OrderQuery) { ?>
            <h5><?php echo $OrderQuery->rowCount() ?> Orders found</h5>

        <?php } else { ?>
            <h5>Search for Order Accounts...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="order?" class="button">Order List</a>
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
                        <th>Card Holder</th>
                        <th>Customer ID</th>
                        <th>Invoice ID</th>
                        <th>User Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Order\Model\OrderRow $Order */
                    $odd = false;
                    foreach($OrderQuery as $Order) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='order?id=<?php echo $Order->getID(); ?>'><?php echo $Order->getID(); ?></a></td>
                        <td><?php echo $Order->getHolderFullFullName(); ?></td>
                        <td><?php echo $Order->getCustomerID(); ?></td>
                        <td><?php echo $Order->getInvoiceNumber(); ?></td>
                        <td><?php echo $Order->getUsername(); ?></td>
                        <td><?php echo $Order->getAmount(); ?></td>
                        <td><?php echo $Order->getStatus(); ?></td>
                        <td><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>

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
                echo "<a href='order?" . http_build_query($args) . "'>Next</a> ";
                ?>
            </fieldset>
        </form>
    </section>