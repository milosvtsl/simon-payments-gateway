<?php
use \User\Model\UserRow;
/**
 * @var \View\AbstractListView $this
 **/?>
    <section class="content">
        <div class="action-fields">
            <a href="user?" class="button">User List</a>
        </div>

        <h1>User List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($this->hasMessage()) { ?>
            <h6><?php echo $this->getMessage() ?></h6>

        <?php } else { ?>
            <h5>Search for User Accounts...</h5>

        <?php } ?>

        <form class="form-search themed">
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
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('user?'); ?>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small">
                    <tr>
                        <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_ID); ?>">ID</a></th>
                        <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_USERNAME); ?>">Username</a></th>
                        <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_LNAME); ?>">Name</a></th>
                        <th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_EMAIL); ?>">Email</a></th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \User\Model\UserRow $User */
                    $odd = false;
                    foreach($this->getListQuery() as $User) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='user?id=<?php echo $User->getID(); ?>'><?php echo $User->getID(); ?></a></td>
                        <td><a href='user?id=<?php echo $User->getID(); ?>'><?php echo $User->getUsername(); ?></a></td>
                        <td><?php echo $User->getFullName(); ?></td>
                        <td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                        <td><?php echo $User->getMerchantCount(); ?></td>
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