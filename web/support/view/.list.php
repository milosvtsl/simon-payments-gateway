<?php
use Support\Model\SupportTicketRow;
/**
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/


$Theme = $this->getTheme();
$Theme->addPathURL('support',                   'Support');
$Theme->addPathURL('support/list.php',          'Tickets');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('support-ticket-list');
?>

    <article class="themed">

        <section class="content">

            <?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

            <form class="form-support-ticket-search themed">
                <fieldset class="search-fields">
                    <div class="legend">Search</div>
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
                                    <select name="category" style="min-width: 11.1em;" >
                                        <option value="">By Any Category</option>
                                        <option>Technical</option>
                                        <option>Reporting</option>
                                        <option>Billing</option>
                                        <option>Sales</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Value</td>
                                <td>
                                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="All Fields" size="33" />

                                    <input type="submit" value="Search" class="themed" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <div class="legend">Search Results</div>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th><a href="support?<?php echo $this->getSortURL(SupportTicketRow::SORT_BY_ID); ?>">ID</a></th>
                            <th><a href="support?<?php echo $this->getSortURL(SupportTicketRow::SORT_BY_CATEGORY); ?>">Category</a></th>
                            <th><a href="support?<?php echo $this->getSortURL(SupportTicketRow::SORT_BY_DATE); ?>">Date</a></th>
                            <th>Subject</th>
                            <th>Content</th>
                        </tr>
                        <?php
                        /** @var SupportTicketRow $Ticket */
                        $odd = false;
                        foreach($Query as $Ticket) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='support?uid=<?php echo $Ticket->getUID(); ?>'><?php echo $Ticket->getID(); ?></a></td>
                            <td><?php echo $Ticket->getCategory(); ?></td>
                            <td><?php echo date("M dS Y G:i:s", strtotime($Ticket->getDate())); ?></td>
                            <td><?php echo $Ticket->getSubject(); ?></td>
                            <td>
                                <textarea rows="2" cols="24" onclick="this.rows++; this.cols+=3;"><?php
                                    echo $Ticket->getContent();
                                    ?></textarea>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <fieldset class="pagination">
                    <div class="legend">Page</div>
                    <?php $this->printPagination('support?'); ?>
                </fieldset>
            </form>
        </section>
    </article>

<?php $Theme->renderHTMLBodyFooter(); ?>