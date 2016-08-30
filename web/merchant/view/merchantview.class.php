<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use Merchant\MerchantRow;
use View\AbstractView;

class MerchantView extends AbstractView
{
    private $_merchant;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_merchant = MerchantRow::fetchByID($id);
        if(!$this->_merchant)
            throw new \InvalidArgumentException("Invalid Merchant ID: " . $id);
        parent::__construct();
    }

    /** @return MerchantRow */
    public function getMerchant() { return $this->_merchant; }

    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('merchant?', "Merchants");
        $this->getTheme()->addCrumbLink('merchant?id=' . $this->_merchant->getID(), $this->_merchant->getShortName());
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], ucfirst($this->_action));

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
            case 'view':
                include('.view.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            case 'delete':
                include('.delete.php');
                break;
            case 'change':
                include('.change.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditMerchant = $this->getMerchant();
                    $EditMerchant->updateFields($post)
                        ? $this->setSessionMessage("Merchant Updated Successfully: " . $EditMerchant->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditMerchant->getUID());
                    header('Location: merchant?id=' . $EditMerchant->getID());

                    break;
                case 'delete':
                    print_r($post);
                    die();
                    break;
                case 'change':
                    print_r($post);
                    die();
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}