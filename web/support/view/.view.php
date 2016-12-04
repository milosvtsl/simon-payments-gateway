<?php
use Support\View\SupportTicketView;
/**
 * @var SupportTicketView $this
 **/
$Ticket = $this->getTicket();
$odd = false;
$action_url = 'support?uid=' . $Ticket->getUID() . '&action=';
$Theme = $this->getTheme();
$Theme->addPathURL('support',                   'Support');
$Theme->addPathURL($action_url,                     $Ticket->getID());
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('support-ticket-view',    $action_url);
?>
    <article class="themed">

        <section class="content">

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-support-ticket themed" onsubmit="return false;">
                <fieldset>
                    <div class="legend">Ticket Information</div>
                    <table class="table-support-ticket-info themed striped-rows">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>ID</td>
                            <td><?php echo $Ticket->getID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Support</td>
                            <td><a href='support?id=<?php echo $Ticket->getCategory(); ?>'><?php echo $Ticket->getSupportName(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Type</td>
                            <td><a href='support?type_id=<?php echo $Ticket->getSupportTypeID(); ?>'><?php echo $Ticket->getSupportType(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Type ID</td>
                            <td>
                                <a href='<?php echo strtolower($Ticket->getSupportType()); ?>?id=<?php echo $Ticket->getSupportTypeID(); ?>'>
                                    <?php echo $Ticket->getSupportTypeID(); ?>
                                </a>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Date</td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Ticket->getDate())); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>URL</td>
                            <td><a href="<?php echo $Ticket->getTicketURL(); ?>" target="_blank"><?php echo $Ticket->getTicketURL(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Result</td>
                            <td><?php echo $Ticket->getResult(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Ticket</td>
                            <td>
                                <textarea rows="30" cols="58" onclick="this.rows++; this.cols+=3;"><?php
                                    echo $Ticket->getTicket();
                                    ?></textarea>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Response</td>
                            <td>
                                <textarea rows="30" cols="58" onclick="this.rows++; this.cols+=3;"><?php
                                    echo $Ticket->getResponse();
                                    ?></textarea>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>