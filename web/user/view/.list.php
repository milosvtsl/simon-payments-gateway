<?php
use \User\Model\UserRow;
/**
 * @var \View\AbstractListView $this
 **/

?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
            <a href="transaction/charge.php" class="button<?php echo @$ca['charge']; ?>">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>

        <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="order?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user?" class="button current">Users <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user/add.php" class="button">Add User <div class="submenu-icon submenu-icon-add"></div></a>
            <a href="integration?" class="button">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>
    </nav>

    <article class="themed">

         <section class="content">
             <!-- Bread Crumbs -->
             <aside class="bread-crumbs">
                 <a href="user" class="nav_user">Users</a>
                 <a href="user/list.php" class="nav_user_list">Search</a>
             </aside>

             <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-user-search themed">
                <fieldset class="search-fields">
                    <legend>Search</legend>
                    <table>
                        <tr>
                            <td class="name">Search</td>
                            <td class="value">
                                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="By ID, Name, Email" />
                            </td>
                            <td class="value">
                                <select name="limit">
                                    <?php
                                    $limit = @$_GET['limit'] ?: 50;
                                    foreach(array(10,25,50,100,250) as $opt)
                                        echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                    ?>
                                </select>
                            <td class="value"><input type="submit" value="Search" /></td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset style="display: inline-block;">
                    <legend>Search Results</legend>
                    <table class="table-results themed striped-rows">
                        <tr>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_LNAME); ?>">Name</a></th>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_EMAIL); ?>">Email</a></th>
                            <th>Merchants</th>
                        </tr>
                        <?php
                        /** @var \User\Model\UserRow $User */
                        $odd = false;
                        foreach($this->getListQuery() as $User) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='user?id=<?php echo $User->getID(); ?>'><?php echo $User->getID(); ?></a></td>
                            <td><a href='user?id=<?php echo $User->getID(); ?>'><?php echo $User->getFullName(); ?></a></td>
                            <td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                            <td><a href='merchant/list.php?user_id=<?php echo $User->getID(); ?>'><?php echo $User->getMerchantCount(); ?></a></td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <fieldset class="pagination">
                    <legend>Pagination</legend>
                    <table class="table-pagination themed small striped-rows">
                        <tr>
                            <td colspan="2">
                                <?php $this->printPagination('user?'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>