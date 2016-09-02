<?php
/**
 * @var \Batch\View\BatchView $this
 * @var PDOStatement $BatchQuery
 **/
$Batch = $this->getBatch();
$odd = false;
$action_url = 'batch?id=' . $Batch->getID() . '&action=';
?>
    <section class="message">
        <h1>Edit <?php echo $Batch->getID(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Edit this Batch Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-Batch themed" method="POST">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="batch?" class="button">Batch List</a>
                <a href="<?php echo $action_url; ?>view" class="button">View</a>

            </fieldset>
            <fieldset>
                <legend>Edit Batch Fields</legend>
                <table class="table-batch-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Batch->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Batch->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>