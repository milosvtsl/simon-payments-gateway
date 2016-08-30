<?php /**
 * @var \User\View\LoginView $this
 * @var PDOStatement $MerchantQuery
 **/?>
    <section class="message">
        <h1>Merchant List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($MerchantQuery) { ?>
            <h5><?php echo $MerchantQuery->rowCount() ?> merchants found</h5>

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
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed">
                    <tr>
                        <th>ID</th>
                        <th>Merchantname</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Merchant\MerchantRow $Merchant */
                    $odd = false;
                    foreach($MerchantQuery as $Merchant) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><a href='merchant.php?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getID(); ?></a></td>
                        <td><?php echo $Merchant->getMerchantname(); ?></td>
                        <td><?php echo $Merchant->getFullName(); ?></td>
                        <td><a href='mailto:<?php echo $Merchant->getEmail(); ?>'><?php echo $Merchant->getEmail(); ?></a></td>
                        <td><?php
                            /** @var \Merchant\MerchantRow $Merchant */
                            foreach($Merchant->queryMerchants() as $Merchant) {
                                echo "<a href='merchant.php?id=" . $Merchant->getID() . "'>"
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
                <?php
                $limit = @$_GET['limit'] ?: 10;
                $page = @$_GET['page'] ?: 1;

                $args = $_GET;
                if($page > 1) {
                    $args['page'] = $page - 1;
                    $url = '?' . http_build_query($args);
                    echo "<a href='?" . http_build_query($args) . "'>Previous</a> ";
                }

                $args['page'] = $page + 1;
                $url = '?' . http_build_query($args);
                echo "<a href='?" . http_build_query($args) . "'>Next</a> ";
                ?>
            </fieldset>
        </form>
    </section>