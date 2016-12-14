<?php
use \Integration\Model\IntegrationRow;
/**
 * @var \View\AbstractListView $this
 **/
$Theme = $this->getTheme();
$Theme->addPathURL('integration',             'Integration');
$Theme->addPathURL('integration/list.php',    'API Endpoints');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('integration-list');
?>


    <article class="themed">
        <section class="content">

            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-search themed">
                <fieldset>
                    <div class="legend">Integration</div>
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