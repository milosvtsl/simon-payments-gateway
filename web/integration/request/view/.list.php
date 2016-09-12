<?php
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/?>
    <section class="message">
        <h1>Request List</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else if($this->hasMessage()) { ?>
            <h6><?php echo $this->getMessage() ?></h6>

        <?php } else { ?>
            <h5>Search for Integration Requests...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-search themed">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="integration?" class="button">Integrations</a>
                <a href="integration/request?" class="button">Request List</a>
            </fieldset>
            <fieldset class="search-fields">
                <legend>Search</legend>
                <table>
                    <tbody>
                        <tr>
                            <th>From</th>
                            <td>
                                <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
                                <input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
                            </td>
                        </tr>
                        <tr>
                            <th>Limit</th>
                            <td>
                                <select name="limit">
                                    <?php
                                    $limit = @$_GET['limit'] ?: 50;
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
                            <th>Value</th>
                            <td>
                                <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="All Fields" size="33" />

                                <input type="submit" value="Search" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('integration/request?'); ?>
            </fieldset>
            <fieldset>
                <legend>Search Results</legend>
                <table class="table-results themed small">
                    <tr>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ID); ?>">ID</a></th>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_INTEGRATION_ID); ?>">Integration</a></th>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE); ?>">Type</a></th>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE_ID); ?>">Type ID</a></th>
                        <th>Request</th>
                        <th>Response</th>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESULT); ?>">Result</a></th>
                        <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_DATE); ?>">Date</a></th>
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
                        <td><?php echo $Request->getRequest(); ?></td>
                        <td><?php echo $Request->getResponse(); ?></td>
                        <td><?php echo $Request->getResult(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Request->getDate())); ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </fieldset>
            <fieldset class="paginate">
                <legend>Pagination</legend>
                <?php $this->printPagination('integration/request?'); ?>
            </fieldset>
        </form>
    </section>