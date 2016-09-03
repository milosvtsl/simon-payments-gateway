<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $Query
 * @var \User\Model\UserQueryStats $Stats
 **/?>
    <section class="message">
        <h1>User List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($Stats) { ?>
            <h5><?php echo $Stats->getMessage(); ?></h5>

        <?php } else { ?>
            <h5>Search for User Accounts...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="user?" class="button">User List</a>
            </fieldset>
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
                <?php $Stats->printPagination('user?'); ?>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \User\Model\UserRow $User */
                    $odd = false;
                    foreach($Query as $User) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='user?id=<?php echo $User->getID(); ?>'><?php echo $User->getID(); ?></a></td>
                        <td><?php echo $User->getUsername(); ?></td>
                        <td><?php echo $User->getFullName(); ?></td>
                        <td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
                        <td><?php
                            /** @var \Merchant\Model\MerchantRow $Merchant */
                            foreach($User->queryMerchants() as $Merchant) {
                                echo "<a href='merchant?id=" . $Merchant->getID() . "'>"
                                    . $Merchant->getShortName()
                                    . "</a><br/>";
                            } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $Stats->printPagination('user?'); ?>
            </fieldset>
        </form>
    </section>