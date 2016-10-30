<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Dompdf {
    define('DOMPDF_DIR', dirname(dirname(__DIR__)) . '/system/support/DOMPDF/');


    require_once DOMPDF_DIR . '/lib/html5lib/Parser.php';
    require_once DOMPDF_DIR . '/lib/php-font-lib/src/FontLib/Autoloader.php';
    require_once DOMPDF_DIR . '/lib/php-svg-lib/src/autoload.php';


    spl_autoload_register('\Dompdf\dompdf_autoload', true, true);


    function dompdf_autoload($class) {
        if (0 === strncmp('Cpdf', $class, 4)) {
            $file = DOMPDF_DIR . 'lib/Cpdf.php';
            require_once $file;
        }
        if (0 === strncmp('Dompdf', $class, 6)) {
            $file = DOMPDF_DIR . 'src/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 6)) . '.php';
            require_once $file;
        }
    }
}

namespace Order\PDF {
    use Dompdf\Dompdf;
    use Merchant\Model\MerchantRow;
    use Order\Model\OrderRow;
    use Order\View\OrderView;
    use View\Theme\Blank\BlankViewTheme;

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


}