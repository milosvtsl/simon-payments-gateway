<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractView;

class MerchantFormAddView extends AbstractView
{
    public function __construct() {
        parent::__construct();
    }

    protected function renderHTMLHeadScripts() {
        echo <<<HEAD
        <script src="merchant/view/assets/merchant.js"></script>
        <link type='text/css' rel='stylesheet' href='merchant/view/assets/merchant.css'> 
HEAD;
        parent::renderHTMLHeadScripts();
    }

    public function renderHTMLBody(Array $params)
    {
        $action_url = 'merchant/addform.php';
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $Theme = $this->getTheme();
        $Theme->addPathURL('merchant',              'Merchants');
        $Theme->addPathURL('merchant/form.php',     'Forms');
        $Theme->addPathURL($action_url,             'Add New Custom Order Form');
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('merchant-form-add', $action_url);
?>
        <article class="themed">
            <section class="content">
                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>
                <form name="form-merchant-form-add" class="themed" method="POST">
                    <fieldset>
                        <div class="legend">Create new customized order page</div>

                        <?php $odd = false; ?>
                        <table class="table-merchant-info themed small striped-rows" style="float: left; width: 49%;">
                            <tr>
                                <th colspan="2">Template Information</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">ID</td>
                                <td>New</td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">UID</td>
                                <td>New</td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Name</td>
                                <td><input type="text" name="title" value="" placeholder="Custom Order Form Name" autofocus required /></td>
                            </tr>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Merchant</td>
                                <td>
                                    <select name="merchant_uid" class="">
                                        <?php
                                        $MerchantQuery = $SessionUser->queryUserMerchants();
                                        foreach ($MerchantQuery as $MerchantOption) {
                                            /** @var MerchantRow $MerchantOption */
                                            echo "\n\t\t\t\t\t\t\t<option value='" . $MerchantOption->getUID() . "'>",
                                            $MerchantOption->getShortName(),
                                            "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr >
                                <td colspan="2">
                                    <input type="submit" value="Create New Template" class="themed"/>
<!--                                    <input type="reset" value="Reset Form" class="themed"/>-->
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>

<?php
    }

    public function processFormRequest(Array $post) {
        $Form = MerchantFormRow::createNewMerchantForm($post);

        $SessionManager = new SessionManager();
        $SessionManager->setMessage("Custom Form created successfully: " . $Form->getUID());
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
        header('Location: ' . $baseHREF . 'merchant/form.php?uid=' . $Form->getUID() . '&action=edit');
        die();

    }
}