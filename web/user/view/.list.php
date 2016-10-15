<?php
use \User\Model\UserRow;
/**
 * @var \View\AbstractListView $this
 **/

$button_current = 'user';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';

?>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="user" class="nav_user">Users</a>
        <a href="user/list.php" class="nav_user_list">Search</a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-user-search themed">
            <fieldset class="search-fields">
                <legend>Search</legend>
                User Name:
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
            <fieldset style="display: inline-block;">
                <legend>Search Results</legend>
                <table class="table-results themed small striped-rows">
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
                        <td><?php echo $User->getFullName(); ?></td>
                        <td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                        <td><a href='merchant/list.php?user_id=<?php echo $User->getID(); ?>'><?php echo $User->getMerchantCount(); ?></a></td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('user?'); ?>
            </fieldset>
        </form>
    </section>