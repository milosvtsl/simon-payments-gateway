<?php
use \Merchant\Model\MerchantRow;
/**
 * @var \View\AbstractListView $this
 **/

$Theme = $this->getTheme();
$Theme->addPathURL('merchant',             'Merchants');
$Theme->addPathURL('merchant/list.php',    'Search');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('merchant-list');
?>

    <article class="themed">

        <section class="content">

            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset class="search-fields">
                    <div class="legend">Search all Merchants</div>
                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="Name, ID, UID" />
                    <select name="limit">
                        <?php
                        $limit = @$_GET['limit'] ?: 10;
                        foreach(array(10,25,50,100,250) as $opt)
                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                        ?>
                    </select>
                    <input type="submit" value="Search" class="themed" />
                </fieldset>
                <br/>

                <fieldset>
                    <div class="legend">Search Results</div>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="merchant?<?php echo $this->getSortURL(MerchantRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="merchant?<?php echo $this->getSortURL(MerchantRow::SORT_BY_NAME); ?>">Name</a></th>
                            <th>URL</th>
                            <th>State</th>
                            <th>Zip</th>
                            <th>Users</th>
                        </tr>
                        <?php
                        /** @var \Merchant\Model\MerchantRow $Merchant */
                        $odd = false;
                        foreach($this->getListQuery() as $Merchant) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getID(); ?></a></td>
                            <td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getShortName(); ?></a></td>
                            <td><a target="_blank" href='<?php echo $Merchant->getURL(); ?>'><?php echo preg_replace('/^https?:\/\//i', '', $Merchant->getURL()); ?></a></td>
                            <td><?php echo $Merchant->getRegionCode(); ?></td>
                            <td><?php echo $Merchant->getZipCode(); ?></td>
                            <td><a href='user?merchant_id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getUserCount(); ?></a></td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>

                <fieldset class="pagination">
                    <div class="legend">Page</div>
                    <?php $this->printPagination('merchant?'); ?>

                    <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                </fieldset>
            </form>
        </section>
    </article>

    <?php $Theme->renderHTMLBodyFooter(); ?>