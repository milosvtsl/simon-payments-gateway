<?php
use \Merchant\Model\MerchantRow;
/**
 * @var \View\AbstractListView $this
 **/

//$button_current = 'merchant';
//include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';

?>


    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button hide-on-layout-horizontal1">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
            <a href="transaction/charge.php" class="button<?php echo @$ca['charge']; ?>">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>
        <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
        <a href="order" class="button">Transactions <div class="submenu-icon submenu-icon-transaction"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="merchant" class="button current">Merchants <div class="submenu-icon submenu-icon-merchant"></div></a>
            <a href="merchant/add.php" class="button">Add Merchant <div class="submenu-icon submenu-icon-add"></div></a>
            <a href="user" class="button">Users <div class="submenu-icon submenu-icon-user"></div></a>
        <?php } ?>
    </nav>

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