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
            <h5><?php echo $Stats->getMessage() ?></h5>

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
                            <input type="datetime-local" name="date_from" value="<?php echo @$_GET['date_from'] ?: date('Y-m-d\TH:i:s', time()-30*24*60*60);?>" /> to
                            <input type="datetime-local" name="date_to"   value="<?php echo @$_GET['date_to']   ?: date('Y-m-d\TH:i:s');?>"  />
                        </td>
                    </tr>
                    <tr>
                        <th>Value</th>
                        <td>
                            <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="UID, Batch ID" size="51" />
                            <select name="limit">
                                <?php
                                $limit = @$_GET['limit'] ?: 50;
                                foreach(array(10,25,50,100,250) as $opt)
                                    echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                ?>
                            </select>
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
                <table class="table-results themed">
                    <tr>
                        <th>ID</th>
                        <th>Batch ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Batch\Model\BatchRow $Batch */
                    $odd = false;
                    foreach($Query as $Batch) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='batch?id=<?php echo $Batch->getID(); ?>'><?php echo $Batch->getID(); ?></a></td>
                        <td><?php echo $Batch->getBatchID(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Batch->getDate())); ?></td>
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