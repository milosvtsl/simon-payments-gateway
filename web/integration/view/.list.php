<?php
use \Integration\Model\IntegrationRow;
/**
 * @var \View\AbstractListView $this
 **/

?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <a href="user/account.php#content" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="order#content" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="integration#content" class="button current">Integration <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="integration/request#content" class="button">Requests <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>
    </nav>


    <article id="article" class="themed">
        <section id="content" class="content">
            <a name='content'></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="integration" class="nav_integration">Integration</a>
                <a href="integration/list.php" class="nav_integration_list">Search</a>
            </aside>
            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset>
                    <legend>Integration</legend>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="integration?<?php echo $this->getSortURL(IntegrationRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="integration?<?php echo $this->getSortURL(IntegrationRow::SORT_BY_NAME); ?>">Name</a></th>
                            <th>Success</th>
                            <th>Fail</th>
                            <th>Notes</th>
                        </tr>
                        <?php
                        /** @var \Integration\Model\IntegrationRow $Integration */
                        $odd = false;
                        foreach($this->getListQuery() as $Integration) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='integration?id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getID(); ?></a></td>
                            <td><a href='integration?id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getName(); ?></a></td>
                            <td><a href='integration/request?result=success&integration_id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getSuccessCount(); ?></a></td>
                            <td><a href='integration/request?result=fail&integration_id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getFailCount(); ?></a></td>
                            <td><?php echo $Integration->getNotes(); ?></td>

                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>