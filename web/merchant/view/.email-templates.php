<?php
use System\Config\SiteConfig;
use System\Model\EmailTemplateRow;
use System\Mail\AbstractEmail;

/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = 'merchant?uid=' . $Merchant->getUID() . '&action=';

$Theme = $this->getTheme();
$Theme->addPathURL('merchant',      SiteConfig::$SITE_DEFAULT_MERCHANT_NAME . 's');
$Theme->addPathURL($action_url,     $Merchant->getShortName());
$Theme->addPathURL($action_url.'email-templates',     'Email Templates');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('merchant-email-templates', $action_url);

$AvailableEmailTemplates = EmailTemplateRow::getAvailableEmailTemplateClasses();
$Class = null;
if(!empty($_GET['class'])) {
    $Class = $_GET['class'];
    if(!in_array($Class, $AvailableEmailTemplates))
        throw new Exception("Unknown templates: " . $Class);
}
?>


<article class="themed">
     <section class="content">

            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form name="form-merchant-email-templates" class=" themed" method="POST">
                <input type="hidden" name="class" value="<?php echo $Class; ?>" />
                <input type="hidden" name="merchant_uid" value="<?php echo $Merchant->getUID(); ?>" />

                <fieldset class="themed">
                    <div class="legend">Edit Email Templates: <?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?> #<?php echo $Merchant->getID(); ?></div>

                    <div class="page-buttons order-page-buttons hide-on-print">
                        <a href="<?php echo $action_url; ?>view" class="page-button page-button-view">
                            <div class="app-button large app-button-view" ></div>
                            View <?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?>
                        </a>
                        <a href="<?php echo $action_url; ?>edit" class="page-button page-button-edit">
                            <div class="app-button large app-button-edit" ></div>
                            Edit <?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?>
                        </a>
                        <a href="<?php echo $action_url; ?>email-templates" class="page-button page-button-edit disabled">
                            <div class="app-button large app-button-edit" ></div>
                            Email Templates
                        </a>
                        <a href="<?php echo $action_url; ?>delete" class="page-button page-button-delete disabled">
                            <div class="app-button large app-button-delete" ></div>
                            Delete <?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?>
                        </a>
                    </div>

                    <hr/>


                    <table class="table-merchant-info themed striped-rows" style="width: 100%;">
                        <tr>
                            <th colspan="3" class="section-break"><?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?> Information</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Name</td>
                            <td><?php echo $Merchant->getName(); ?></td>
                            <td rowspan="2" style="width: 50%; vertical-align: top;">
                                <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>UID</td>
                            <td><?php echo $Merchant->getUID(); ?></td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="themed">
                    <div class="legend">Choose Template</div>
                    <table class="table-merchant-info themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Template</td>
                            <td>
                                <select name="class_change">
                                    <option>Choose an email template:</option>
                                    <?php
                                    foreach($AvailableEmailTemplates as $title => $class_option)
                                        echo "<option value='", $class_option, "'",
                                        (@$_GET['class'] == $class_option ? ' selected="selected"' : ''),
                                        ">", $title, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>


                <?php if($Class) {
                    if(!in_array($Class, $AvailableEmailTemplates))
                        throw new Exception("Invalid Class: " . $Class);

                    /** @var AbstractEmail $Class */
                    $title = $Class::TITLE;
                    $bcc = $Class::BCC;
                    $body = $Class::TEMPLATE_BODY;
                    $subject = $Class::TEMPLATE_SUBJECT;

                    $EmailTemplate = EmailTemplateRow::fetchAvailableTemplate($Class, $Merchant->getID());
                    if($EmailTemplate) {
                        // Replace email template
                        $bcc = $EmailTemplate->getBCC();
                        $body = $EmailTemplate->getBody();
                        $subject = $EmailTemplate->getSubject();
                    }

                    AbstractEmail::processTemplateConstants($body, $subject, $bcc, $Merchant);

                    ?>

                <fieldset>
                    <div class="legend">
                        Customize Email '<?php echo $title; ?>'
                    </div>
                    <table class="table-merchant-info themed striped-rows" style="width: 100%;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Subject</td>
                            <td>
                                <input type="text" name="subject" value="<?php echo $subject; ?>" style="width: 95%;"/>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">BCC</td>
                            <td>
                                <input type="text" name="bcc" value="<?php echo $bcc; ?>" style="width: 95%;"/>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Body</td>
                            <td style="padding-right: 24px;">
                                <textarea name="body" style="width: 95%; min-height: 30em;"><?php echo $body; ?></textarea>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Update</td>
                            <td>
                                <button type="submit">Update Email Template</button>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <script src="assets/js/jquery/jquery.min.js"></script>

                <link type="text/css" rel="stylesheet" href="assets/js/jqueryte/jquery-te-1.4.0.css">
                <script type="text/javascript" src="assets/js/jqueryte/jquery-te-1.4.0.min.js" charset="utf-8"></script>

                <script>

                    $(function() {
                        $('textarea[name=body]').jqte({
                            'left': false,
                            'center': false,
                            'right': false,
                            'indent': false,
                            'outdent': false
                        });
                    });
                </script>

                <?php } ?>

            </form>
        </section>
    </article>