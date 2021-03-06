<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use User\Session\SessionManager;
use View\AbstractView;

class BatchView extends AbstractView
{
    const VIEW_PATH = 'batch';
    const VIEW_NAME = 'Transactions';
    private $batch_id;
    private $merchant_id;

    public function __construct($batch_id, $merchant_id, $action=null) {
        $this->action = strtolower($action) ?: 'view';
        $this->batch_id = $batch_id;
        $this->merchant_id = $merchant_id;
        parent::__construct();
    }

    public function renderHTMLBody(Array $params) {

        $action_url = 'order/batch.php?batch_id=' . $this->batch_id . '&merchant_id='.$this->merchant_id;

        switch($this->action) {
            default:
            case 'view':
?>

<?php
                break;
        }
    }

    public function processFormRequest(Array $post) {
        $SessionManager = new SessionManager();
        try {
            // Render Page
            switch($this->action) {
//                case 'edit':
//                    $EditOrder = $this->getOrder();
//                    $EditOrder->updateFields($post)
//                        ? $this->setSessionMessage("Order Updated Successfully: " . $EditOrder->getUID())
//                        : $this->setSessionMessage("No changes detected: " . $EditOrder->getUID());
//                    header('Location: order?id=' . $EditOrder->getID() . '');
//                    die();

                case 'delete':
                    print_r($post);
                    die();

                case 'change':
                    print_r($post);
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->action);
            }

        } catch (\Exception $ex) {
            $SessionManager->setMessage(
                "<div class='error'>Error: ".$ex->getMessage() . "</div>"
            );
            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header('Location: ' . $baseHREF . 'order/batch.php?batch_id=' . $this->batch_id . '&merchant_id='.$this->merchant_id.'&message=' . $ex->getMessage()  . '');
            die();
        }
    }
}