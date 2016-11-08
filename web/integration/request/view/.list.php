<?php
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <a href="user/account.php#content" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="integration#content" class="button">Integration <div class="submenu-icon submenu-icon-integration"></div></a>
            <a href="integration/request#content" class="button current">Requests <div class="submenu-icon submenu-icon-integration"></div></a>
        <?php } ?>
    </nav>

    <article id="article" class="themed">

        <section id="content" class="content">
            <a name='content' ></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="integration" class="nav_integration">Integration</a>
                <a href="integration/request#content" class="nav_integration_request">Requests</a>
                <a href="integration/request/list.php" class="nav_integration_list">Search</a>
            </aside>

            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset class="search-fields">
                    <legend>Search</legend>
                    <table>
                        <tbody>
                            <tr>
                                <td class="name">From</td>
                                <td>
                                    <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
                                    <input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Limit</td>
                                <td>
                                    <select name="limit">
                                        <?php
                                        $limit = @$_GET['limit'] ?: 10;
                                        foreach(array(10,25,50,100,250) as $opt)
                                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                        ?>
                                    </select>
                                    <select name="type" style="min-width: 11.1em;" >
                                        <option value="">By Any Type</option>
                                        <option value="transaction">Transaction</option>
                                        <option value="merchant">Merchant</option>
                                    </select>
                                    <select name="integration_id" >
                                        <option value="">By Integration</option>
                                        <?php
                                        $IntegrationQuery = IntegrationRow::queryAll();
                                        foreach($IntegrationQuery as $Integration)
                                            /** @var IntegrationRow $Integration */
                                            echo "\n\t\t\t\t\t\t\t<option value='", $Integration->getID(), "' ",
                                            ($Integration->getID() == @$_GET['integration_id'] ? 'selected="selected" ' : ''),
                                            "'>", $Integration->getName(), "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Value</td>
                                <td>
                                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="All Fields" size="33" />

                                    <input type="submit" value="Search" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Search Results</legend>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_INTEGRATION_ID); ?>">Integration</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE); ?>">Type</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE_ID); ?>">Type&nbsp;ID</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESULT); ?>">Result</a></th>
                            <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_DATE); ?>">Date</a></th>
                            <th>Duration</th>
                            <th>Response</th>
                        </tr>
                        <?php
                        /** @var IntegrationRequestRow $Request */
                        $odd = false;
                        foreach($Query as $Request) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='integration/request?id=<?php echo $Request->getID(); ?>'><?php echo $Request->getID(); ?></a></td>
                            <td><?php echo $Request->getIntegrationName(); ?></td>
                            <td><?php echo $Request->getIntegrationType(); ?></td>
                            <td>
                                <a href='<?php echo strtolower($Request->getIntegrationType()); ?>?id=<?php echo $Request->getIntegrationTypeID(); ?>'>
                                    <?php echo $Request->getIntegrationTypeID(); ?>
                                </a>
                            </td>
                            <td><?php echo $Request->getResult(); ?></td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Request->getDate())); ?></td>
                            <td><?php echo round($Request->getDuration(), 3); ?>s</td>
                            <td>
                                <textarea rows="2" cols="24" onclick="this.rows++; this.cols+=3;"><?php
                                    echo $Request->getResponse();
                                    echo "\n\nRequest:\n";
                                    echo $Request->getRequest();
                                    ?></textarea>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <fieldset class="pagination">
                    <legend>Page</legend>
                    <?php $this->printPagination('integration/request?'); ?>
                </fieldset>
            </form>
        </section>
    </article>