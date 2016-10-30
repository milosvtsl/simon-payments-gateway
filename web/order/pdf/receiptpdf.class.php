<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\PDF;

use Dompdf\Dompdf;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\View\OrderView;
use View\Theme\Blank\BlankViewTheme;

require_once dirname(dirname(__DIR__)) . '/system/support/DOMPDF/autoload.inc.php';

class ReceiptPDF extends Dompdf
{
    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $OrderView = new OrderView($Order->getID(), 'download');
        $OrderView->setTheme(new BlankViewTheme());

        ob_start();

        $OrderView->renderHTML();

        $output = ob_get_contents();

        ob_end_clean();

        $this->loadHtml($output);

//        $this->setPaper('A4', 'landscape');
    }
}

