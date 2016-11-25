<?php
use \User\Model\UserRow;
/**
 * @var \View\AbstractListView $this
 **/


$Theme = $this->getTheme();
$Theme->addPathURL('user',             'Users');
$Theme->addPathURL('user/list.php',    'Search');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('user-list');
?>
    <article class="themed">

        <section class="content">

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
                                    $limit = @$_GET['limit'] ?: 10;
                                    foreach(array(10,25,50,100,250) as $opt)
                                        echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                    ?>
                                </select>
                            <td class="value"><input type="submit" value="Search" class="themed" /></td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Search Results</legend>
                    <table class="table-results themed striped-rows small">
                        <tr>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_LNAME); ?>">Name</a></th>
                            <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_EMAIL); ?>">Email</a></th>
                            <th>Timezone</th>
                            <th>Created</th>
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
                            <td><?php echo str_replace('_', '', $User->getTimeZone()); ?></td>
                            <td><?php echo $User->getCreateDate() ? date('Y-m-d', strtotime($User->getCreateDate())) : 'N/A'; ?></td>
                            <td><a href='merchant/list.php?user_id=<?php echo $User->getID(); ?>'><?php echo $User->getMerchantCount(); ?></a></td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <fieldset class="pagination">
                    <legend>Page</legend>
                    <?php $this->printPagination('user?'); ?>
                </fieldset>
            </form>
        </section>
    </article>


<?php $Theme->renderHTMLBodyFooter(); ?>