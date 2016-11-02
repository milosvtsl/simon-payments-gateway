<?php
use \Merchant\Model\MerchantRow;
/**
 * @var \View\AbstractListView $this
 **/

$button_current = 'merchant';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';

?>
    <article class="themed">

        <section class="content">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="merchant" class="nav_merchant">Merchants</a>
                <a href="merchant/list.php" class="nav_merchant_list">Search</a>
            </aside>
            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset class="search-fields">
                    <legend>Search all Merchants</legend>
                    <table class="themed">
                        <tbody>
                            <tr>
                                <td class="name">Merchant</td>
                                <td>
                                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="ID, UID, Name" />
                                    <select name="limit">
                                        <?php
                                        $limit = @$_GET['limit'] ?: 10;
                                        foreach(array(10,25,50,100,250) as $opt)
                                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Submit</td>
                                <td>
                                    <input type="submit" value="Search" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
                <br/>
                <fieldset>
                    <legend>Search Results</legend>
                    <table class="table-results themed small striped-rows" style="width: 98%">
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
                    <legend>Page</legend>
                    <?php $this->printPagination('merchant?'); ?>

                    <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                </fieldset>
            </form>
        </section>
    </article>