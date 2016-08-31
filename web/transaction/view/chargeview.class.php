<?php
/**
 * Created by PhpStorm.
 * Transaction: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Transaction\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;

class ChargeView extends AbstractView
{
    private $_transaction;
    private $_action;


    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('transaction?', "Transactions");
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], 'New Charge');

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();


        $sql = "SELECT m.id, m.short_name FROM merchant m ORDER BY m.id DESC";
        $DB = DBConfig::getInstance();
        $MerchantQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
        $MerchantQuery->execute();

        // Render Page
        include('.charge.php');

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditTransaction = $this->getTransaction();
                    $EditTransaction->updateFields($post)
                        ? $this->setSessionMessage("Transaction Updated Successfully: " . $EditTransaction->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditTransaction->getUID());
                    header('Location: transaction?id=' . $EditTransaction->getID());

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