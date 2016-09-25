<?php
use \Integration\Model\IntegrationRow;
/**
 * @var \View\AbstractListView $this
 **/?>
    <section class="content">
        <div class="action-fields">
            <a href="integration?" class="button">Integrations</a>
            <a href="integration/request?" class="button">Requests</a>
        </div>

        <h1>Integration List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($this->hasMessage()) { ?>
            <h6><?php echo $this->getMessage() ?></h6>

        <?php } else { ?>
            <h5>Listing Integrations...</h5>

        <?php } ?>
        <form class="form-search themed">
            <fieldset>
                <legend>Integrations</legend>
                <table class="table-results themed small">
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
