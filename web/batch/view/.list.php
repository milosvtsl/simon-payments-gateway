<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $Query
 * @var \Batch\Model\BatchQueryStats $Stats
 **/?>
    <section class="message">
        <h1>Batch List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($Stats) { ?>
            <h6><?php echo $Stats->getMessage() ?></h6>

        <?php } else { ?>
            <h5>Search for batches...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="batch?" class="button">Batch List</a>
            </fieldset>
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
                            <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="UID, Batch ID, Status" size="33" />
                            <input type="submit" value="Search" />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $Stats->printPagination('batch?'); ?>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small">
                    <tr>
                        <?php
                            function getOrderURL($field) {
                                if(@$_GET['orderby'] == $field)
                                    return http_build_query(array('orderby' => $field, 'order' => strcasecmp(@$_GET['order'], 'DESC') === 0 ? 'ASC' : 'DESC') + $_GET);
                                return http_build_query(array('orderby' => $field, 'order' => 'ASC') + $_GET);
                            }
                        ?>
                        <th><a href="batch?<?php echo getOrderURL('id'); ?>">ID</a></th>
                        <th><a href="batch?<?php echo getOrderURL('batch_id'); ?>">Batch ID</a></th>
                        <th><a href="batch?<?php echo getOrderURL('date'); ?>">Date</a></th>
                        <th>Orders</th>
                        <th>Settled</th>
                        <th>Authorized</th>
                        <th>Void</th>
                        <th><a href="batch?<?php echo getOrderURL('batch_status'); ?>">Status</a></th>
                        <th><a href="batch?<?php echo getOrderURL('merchant_id'); ?>">Merchant</a></th>
                    </tr>
                    <?php
                    /** @var \Batch\Model\BatchRow $Batch */
                    $odd = false;
                    foreach($Query as $Batch) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='batch?id=<?php echo $Batch->getID(); ?>'><?php echo $Batch->getID(); ?></a></td>
                        <td><?php echo $Batch->getBatchID(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Batch->getDate())); ?></td>
                        <td><?php echo $Batch->getOrderCount(); ?></td>
                        <td><?php echo $Batch->getOrderSettled() ? number_format ($Batch->getOrderSettled(), 2) : '(0)'; ?></td>
                        <td><?php echo $Batch->getOrderAuthorized() ? number_format ($Batch->getOrderAuthorized(), 2) : '(0)'; ?></td>
                        <td><?php echo $Batch->getOrderVoid() ? number_format ($Batch->getOrderVoid(), 2) : '(0)'; ?></td>
                        <td><?php echo $Batch->getBatchStatus(); ?></td>
                        <td><a href='merchant?id=<?php echo $Batch->getMerchantID(); ?>'><?php echo $Batch->getMerchantShortName(); ?></a></td>

                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $Stats->printPagination('batch?'); ?>
            </fieldset>
        </form>
    </section>