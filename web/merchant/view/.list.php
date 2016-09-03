<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $Query
 * @var \Merchant\Model\MerchantQueryStats $Stats
 **/?>
    <section class="message">
        <h1>Merchant List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($Stats) { ?>
            <h5><?php echo $Stats->getMessage(); ?></h5>

        <?php } else { ?>
            <h5>Search for Merchant Accounts...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="search-fields">
                <legend>Search</legend>
                Merchant Name:
                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" />
                <select name="limit">
                    <?php
                    $limit = @$_GET['limit'] ?: 50;
                    foreach(array(10,25,50,100,250) as $opt)
                        echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                    ?>
                </select>
                <input type="submit" value="Search" />

            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $Stats->printPagination('merchant?'); ?>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>Zipcode</th>
                    </tr>
                    <?php
                    /** @var \Merchant\Model\MerchantRow $Merchant */
                    $odd = false;
                    foreach($Query as $Merchant) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getID(); ?></a></td>
                        <td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getShortName(); ?></a></td>
                        <td><a href='mailto:<?php echo $Merchant->getMainEmailID(); ?>'><?php echo $Merchant->getMainEmailID(); ?></a></td>
                        <td><?php echo $Merchant->getStateCode(); ?></td>
                        <td><?php echo $Merchant->getZipCode(); ?></td>

                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $Stats->printPagination('merchant?'); ?>
            </fieldset>
        </form>
    </section>