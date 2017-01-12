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

    public function __construct($uid, $action=null) {
        $this->action = $action ?: 'view';
        $this->form = MerchantFormRow::fetchByUID($uid);
        if(!$this->form)
            throw new \InvalidArgumentException("Invalid Merchant Form ID: " . $uid);
        
        parent::__construct();
    }

    public function renderHTMLBody(Array $params) {
        $this->handleAuthority();

        // Render Page
        switch($this->action) {
            case 'edit':
            case 'view':
                return $this->renderEditHTMLBody($params);
            case 'delete':
                return $this->renderDeleteHTMLBody($params);
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->action);
        }
    }

    protected function handleAuthority() {
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
    }

    public function processFormRequest(Array $post) {
        /** @var MerchantFormRow $Form */
        $Form = $this->form;

        $this->handleAuthority();

            // Render Page
        switch($this->action) {
            case 'edit':
                try {
                    $Form->updateFields($post)
                        ? $this->setSessionMessage("<div class='info'>Template Updated Successfully: " . $Form->getTitle() . "</div>")
                        : $this->setSessionMessage("<div class='info'>No changes detected: " . $Form->getTitle() . "</div>");

                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                }
                header('Location: /merchant/form.php?uid=' . $Form->getUID());
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
        <link type='text/css' rel='stylesheet' href='merchant/view/assets/merchant.css'> 
HEAD;
        parent::renderHTMLHeadScripts();
    }

    private function renderEditHTMLBody($params)
    {
        /** @var MerchantFormRow $Form */
        $Form = $this->form;
        $odd = false;
        $action_url = 'merchant/form.php?uid=' . $Form->getUID() . '&action=';
//        $SessionManager = new SessionManager();
//        $SessionUser = $SessionManager->getSessionUser();

        $Theme = $this->getTheme();
        $Theme->addPathURL('merchant',      'Merchants');
        $Theme->addPathURL('merchant/form.php',     'Forms');
        $Theme->addPathURL($action_url,     $Form->getTitle());
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('merchant-form-edit', $action_url);
?>
        <article class="themed">
            <section class="content">
                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>
                <form name="form-merchant-form-edit" class="themed" method="POST" action="<?php echo $action_url; ?>edit">
                    <input type="hidden" name="id" value="<?php echo $Form->getID(); ?>" />
                    <input type="hidden" name="action" value="edit" />
                    <fieldset>
                        <div class="legend">Customize Fields for Order Page Template #<?php echo $Form->getUID(); ?></div>

                        <?php $odd = false; ?>
                        <table class="table-merchant-info themed small striped-rows" style="float: left; width: 49%;">
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
                                <td class="name">CSS Themes</td>
                                <td>
                                    <select multiple="multiple" name="classes[]" title="Choose Charge Form Theme Options">
                                        <option value="" selected>No Theme</option>
                                        <option value="mf-large">Large Text</option>
                                        <option value="mf-mobile">Mobile Layout</option>
                                        <option value="mf-dark">Low Light (dark theme)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Flags</td>
                                <td>
                                    <select multiple="multiple" name="flags[]" title="Choose Charge Form Options">
                                        <option value="" selected>Disable All Flags</option>
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

                        <?php $odd = false; ?>
                        <div style="width: 49%; max-height: 22em; overflow-y: auto">
                            <table class="table-merchant-info themed small striped-rows" style="width: 100%;">
                                <tbody>
                                <tr>
                                    <th>Field Name</th>
                                    <th>Enabled</th>
                                    <th>Required</th>
                                    <th class="hide-on-layout-narrow">Field (in database)</th>
                                </tr>
                                <?php
                                // TODO: add additional field select
                                foreach(MerchantFormRow::getAvailableFields(true) as $field=>$title) {
                                    if(!$Form->hasField($field))
                                        continue;
                                    ?>
                                    <tr class="field-row enabled row-<?php echo ($odd=!$odd)?'odd':'even';?>" data-field="<?php echo $field; ?>">
                                        <td><input type="text" name="fields[<?php echo $field; ?>][name]" value="<?php echo $Form->getCustomFieldName($field, $title); ?>" placeholder="<?php echo $title; ?>" size="12" /></td>
                                        <td>
                                            <label style="display: block; text-align: center;"><input type="checkbox" name="fields[<?php echo $field; ?>][enabled]" <?php echo $Form->hasField($field) ? ' checked' : '' ?> /></label>
                                        </td>
                                        <td>
                                            <label style="display: block; text-align: center;"><input type="checkbox" name="fields[<?php echo $field; ?>][required]" <?php echo $Form->isFieldRequired($field) ? ' checked' : '' ?> /></label>
                                        </td>
                                        <td class="hide-on-layout-narrow"><?php echo $field; ?></td>
                                    </tr>
                                    <?php
                                }

                                foreach(MerchantFormRow::getAvailableFields(true) as $field=>$title) {
                                    if($Form->hasField($field))
                                        continue;
                                    ?>
                                    <tr class="field-row row-<?php echo ($odd=!$odd)?'odd':'even';?>" data-field="<?php echo $field; ?>">
                                        <td><input type="text" name="fields[<?php echo $field; ?>][name]" value="<?php echo $Form->getCustomFieldName($field, $title); ?>" placeholder="<?php echo $title; ?>" size="12" /></td>
                                        <td>
                                            <label style="display: block; text-align: center;"><input type="checkbox" name="fields[<?php echo $field; ?>][enabled]" <?php echo $Form->hasField($field) ? ' checked' : '' ?> /></label>
                                        </td>
                                        <td>
                                            <label style="display: block; text-align: center;"><input type="checkbox" name="fields[<?php echo $field; ?>][required]" <?php echo $Form->isFieldRequired($field) ? ' checked' : '' ?> /></label>
                                        </td>
                                        <td class="hide-on-layout-narrow"><?php echo $field; ?></td>
                                    </tr>
                                    <?php
                                }

                                ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td colspan="4">
                                            <select name="field_add_select">
                                                <option value="">Create a Custom Form Field</option>
                                            <?php
                                            foreach(MerchantFormRow::getAvailableFields(true) as $field=>$title) {
                                                echo "\n\t\t\t\t<option value='{$field}'>{$title}</option>";
                                            } ?>
                                            </select>
                                            <input name="field_add_submit" type="button" value="Create" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </fieldset>

                    <fieldset>
                        <div class="legend">Preview Order Page Template #<?php echo $Form->getUID(); ?></div>
                        <iframe src="order/charge.php?form_uid=<?php echo $Form->getUID(); ?>&iframe=1&disabled=1" style="width: 99%; min-height: 56em; opacity: 0.5; transform: scale(0.8);"></iframe>
                    </fieldset>
                </form>
            </section>
        </article>

<?php
    }
}