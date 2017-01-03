<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use Merchant\Model\MerchantFormRow;
use System\Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractView;

class MerchantFormView extends AbstractView
{
    private $form;
    private $action;

    public function __construct($id, $action=null) {
        $this->action = $action ?: 'view';
        $this->form = MerchantFormRow::fetchByID($id);
        if(!$this->form)
            throw new \InvalidArgumentException("Invalid Merchant ID: " . $id);
        
        parent::__construct();
    }

    public function renderHTMLBody(Array $params) {
        /** @var MerchantFormRow $Form */
        $Form = $this->form;
        
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            $merchant_id = $Form->getMerchantID();
            if(!$merchant_id) {
                // Only admins may edit default templates
                $this->setSessionMessage("Only admins may edit default templates. Permission required: ROLE_ADMIN");
                header('Location: /merchant/form.php');
                die();
            }
            $list = $SessionUser->getMerchantList();
            if(!in_array($merchant_id, $list)) {
                // Invalid Access
                $this->setSessionMessage("Merchant not assigned to user");
                header('Location: /merchant/form.php');
                die();
            }
        }

        // Render Page
        switch($this->action) {
            case 'view':
                return $this->renderEditHTMLBody($params);
            case 'edit':
                return $this->renderEditHTMLBody($params);
            case 'delete':
                return $this->renderEditDeleteBody($params);
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->action);
        }
    }

    public function processFormRequest(Array $post) {
        $Merchant = $this->getMerchant();

        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may edit/view merchants
            $this->setSessionMessage("Unable to view/edit merchant. Permission required: ROLE_ADMIN");
            header('Location: /merchant?id=' . $Merchant->getID());
            die();
        }

        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            if(!in_array($Merchant->getID(), $SessionUser->getMerchantList())) {
                // Only admins may edit/view merchants
                $this->setSessionMessage("Unable to view/edit merchant. This account does not have permission.");
                header('Location: /merchant?id=' . $Merchant->getID());
                die();
            }
        }

            // Render Page
        switch($this->action) {
            case 'edit':
                try {
                    $Merchant->updateFields($post)
                        ? $this->setSessionMessage("<div class='info'>Merchant Updated Successfully: " . $Merchant->getName() . "</div>")
                        : $this->setSessionMessage("<div class='info'>No changes detected: " . $Merchant->getName() . "</div>");

                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                }
                header('Location: /merchant?id=' . $Merchant->getID());
                die();
                break;

            case 'provision':
                $IntegrationRow = IntegrationRow::fetchByID($_GET['integration_id']);
                $MerchantIdentity = $IntegrationRow->getMerchantIdentity($this->getMerchant());
                if($MerchantIdentity->isProvisioned()) {
                    $this->setSessionMessage("Merchant already provisioned: " . $this->getMerchant()->getName());
                    header('Location: /merchant?id=' . $this->getMerchant()->getID());
                    die();
                }

                try {
                    $MerchantIdentity->provisionRemote();
                    $this->setSessionMessage("Merchant provisioned successfully: " . $this->getMerchant()->getName());
                } catch (IntegrationException $ex) {
                    $this->setSessionMessage("Merchant failed to provision: " . $ex->getMessage());
                }
                header('Location: /merchant?id=' . $this->getMerchant()->getID());
                die();

                break;
            case 'delete':
                print_r($post);
                die();
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->action);
        }
    }

    protected function renderHTMLHeadScripts() {
        echo <<<HEAD
        <script src="merchant/view/assets/merchant.js"></script>
HEAD;
        parent::renderHTMLHeadScripts();
    }

    private function renderEditHTMLBody($params)
    {
        /** @var MerchantFormRow $Form */
        $Form = $this->form;
        $odd = false;
        $action_url = 'merchant/form.php?id=' . $Form->getID() . '&action=';
//        $SessionManager = new SessionManager();
//        $SessionUser = $SessionManager->getSessionUser();

        $Theme = $this->getTheme();
        $Theme->addPathURL('merchant',      'Merchants');
        $Theme->addPathURL($action_url,     $Form->getTitle());
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('merchant-edit', $action_url);
?>
        <article class="themed">
            <section class="content">
                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>
                <form name="form-merchant-edit" class="themed" method="POST" action="<?php echo $action_url; ?>edit">
                    <input type="hidden" name="id" value="<?php echo $Form->getID(); ?>" />
                    <input type="hidden" name="action" value="edit" />
                    <fieldset>
                        <div class="legend">Customize Fields for Order Page Template #<?php echo $Form->getUID(); ?></div>


                        <?php $odd = false; ?>
                        <table class="table-merchant-info themed small striped-rows" style="float: left; width: 49%">
                            <tbody>
                            <tr>
                                <th>Display Name</th>
                                <th>Enabled</th>
                                <th>Required</th>
                                <th>Field</th>
                            </tr>
                            <?php
                            // TODO: add additional field select
                            foreach(MerchantFormRow::getAvailableFields() as $field=>$title) {
                                ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td><input type="text" name="field[<?php echo $field; ?>][name]" value="<?php echo $Form->getCustomFieldName($field, $title); ?>" placeholder="<?php echo $title; ?>" size="12" /></td>
                                    <td>
                                        <label style="display: block; text-align: center;"><input type="checkbox" name="field[<?php echo $field; ?>][enabled]" <?php echo $Form->hasField($field) ? ' checked' : '' ?> /></label>
                                    </td>
                                    <td>
                                        <label style="display: block; text-align: center;"><input type="checkbox" name="field[<?php echo $field; ?>][required]" <?php echo $Form->isFieldRequired($field) ? ' checked' : '' ?> /></label>
                                    </td>
                                    <td><?php echo $field; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td colspan="4">
                                    <?php
                                    // TODO: add additional field select
                                    foreach(MerchantFormRow::getAvailableFields() as $field=>$title) {
                                        ?>

                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <?php $odd = false; ?>
                        <table class="table-merchant-info themed small striped-rows" style="width: 49%;">
                            <tr>
                                <th colspan="2">Template Information</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">ID</td>
                                <td><?php echo $Form->getID(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">UID</td>
                                <td><?php echo $Form->getUID(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Name</td>
                                <td><input type="text" name="title" value="<?php echo $Form->getTitle(); ?>" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Css Themes</td>
                                <td>
                                    <select multiple="multiple" name="classes">
                                        <option value="mf-dark">Dark</option>
                                        <option value="mf-large">Large</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Flags</td>
                                <td>
                                    <select multiple="multiple" name="flags">
                                        <option value="recur_enabled">Enable Automatic Billing</option>
                                    </select>
                                </td>
                            </tr>
                            <tr >
                                <td colspan="2">
                                    <input type="submit" value="Update Template" class="themed"/>
                                    <input type="reset" value="Reset Form" class="themed"/>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                    <fieldset>
                        <div class="legend">Preview Order Page Template #<?php echo $Form->getUID(); ?></div>
                        <iframe src="order/charge.php?iframe=1&disabled=1" style="width: 99%; min-height: 56em; opacity: 0.5;"></iframe>
                    </fieldset>
                </form>
            </section>
        </article>
<?php
    }
}