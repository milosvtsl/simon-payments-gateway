<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace {
    define('DOMPDF_DIR', dirname(dirname(__DIR__)) . '/system/lib/DOMPDF/');


    require_once DOMPDF_DIR . '/src/Autoloader.php';
    require_once DOMPDF_DIR . '/src/Canvas.php';
    require_once DOMPDF_DIR . '/src/CanvasFactory.php';
    require_once DOMPDF_DIR . '/src/Cellmap.php';
    require_once DOMPDF_DIR . '/src/Css/AttributeTranslator.php';
    require_once DOMPDF_DIR . '/src/Css/Color.php';
    require_once DOMPDF_DIR . '/src/Css/Style.php';
    require_once DOMPDF_DIR . '/src/Css/Stylesheet.php';
    require_once DOMPDF_DIR . '/src/Dompdf.php';
    require_once DOMPDF_DIR . '/src/Exception.php';
    require_once DOMPDF_DIR . '/src/Exception/ImageException.php';
    require_once DOMPDF_DIR . '/src/FontMetrics.php';
    require_once DOMPDF_DIR . '/src/Frame/Factory.php';
    require_once DOMPDF_DIR . '/src/Frame/FrameList.php';
    require_once DOMPDF_DIR . '/src/Frame/FrameListIterator.php';
    require_once DOMPDF_DIR . '/src/Frame/FrameTree.php';
    require_once DOMPDF_DIR . '/src/Frame/FrameTreeIterator.php';
    require_once DOMPDF_DIR . '/src/Frame/FrameTreeList.php';
    require_once DOMPDF_DIR . '/src/Frame.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/AbstractFrameDecorator.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Block.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Image.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Inline.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/ListBullet.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/ListBulletImage.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/NullFrameDecorator.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Page.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Table.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/TableCell.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/TableRow.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/TableRowGroup.php';
    require_once DOMPDF_DIR . '/src/FrameDecorator/Text.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/AbstractFrameReflower.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Block.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Image.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Inline.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/ListBullet.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/NullFrameReflower.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Page.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Table.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/TableCell.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/TableRow.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/TableRowGroup.php';
    require_once DOMPDF_DIR . '/src/FrameReflower/Text.php';
    require_once DOMPDF_DIR . '/src/Helpers.php';
    require_once DOMPDF_DIR . '/src/Image/Cache.php';
    require_once DOMPDF_DIR . '/src/JavascriptEmbedder.php';
    require_once DOMPDF_DIR . '/src/LineBox.php';
    require_once DOMPDF_DIR . '/src/Options.php';
    require_once DOMPDF_DIR . '/src/PhpEvaluator.php';
    require_once DOMPDF_DIR . '/src/Positioner/AbstractPositioner.php';
    require_once DOMPDF_DIR . '/src/Positioner/Absolute.php';
    require_once DOMPDF_DIR . '/src/Positioner/Block.php';
    require_once DOMPDF_DIR . '/src/Positioner/Fixed.php';
    require_once DOMPDF_DIR . '/src/Positioner/Inline.php';
    require_once DOMPDF_DIR . '/src/Positioner/ListBullet.php';
    require_once DOMPDF_DIR . '/src/Positioner/NullPositioner.php';
    require_once DOMPDF_DIR . '/src/Positioner/TableCell.php';
    require_once DOMPDF_DIR . '/src/Positioner/TableRow.php';
    require_once DOMPDF_DIR . '/src/Renderer/AbstractRenderer.php';
    require_once DOMPDF_DIR . '/src/Renderer/Block.php';
    require_once DOMPDF_DIR . '/src/Renderer/Image.php';
    require_once DOMPDF_DIR . '/src/Renderer/Inline.php';
    require_once DOMPDF_DIR . '/src/Renderer/ListBullet.php';
    require_once DOMPDF_DIR . '/src/Renderer/TableCell.php';
    require_once DOMPDF_DIR . '/src/Renderer/TableRowGroup.php';
    require_once DOMPDF_DIR . '/src/Renderer/Text.php';
    require_once DOMPDF_DIR . '/src/Renderer.php';
    require_once DOMPDF_DIR . '/src/Adapter/GD.php';
    require_once DOMPDF_DIR . '/lib/Cpdf.php';
    require_once DOMPDF_DIR . '/src/Adapter/CPDF.php';
    require_once DOMPDF_DIR . '/src/Adapter/PDFLib.php';

//    require_once DOMPDF_DIR . '/lib/html5lib/Parser.php';
//    require_once DOMPDF_DIR . '/lib/php-font-lib/src/FontLib/Autoloader.php';
//    require_once DOMPDF_DIR . '/lib/php-svg-lib/src/autoload.php';
//
//    require_once DOMPDF_DIR . '/src/Autoloader.php';


//    require_once DOMPDF_DIR . '/autoload.inc.php';

//    spl_autoload_register('\Dompdf\dompdf_autoload', true, true);
//
//
//    function dompdf_autoload($class) {
//        if (0 === strncmp('Cpdf', $class, 4)) {
//            $file = DOMPDF_DIR . 'lib/Cpdf.php';
//            require_once $file;
//        }
//        if (0 === strncmp('Dompdf', $class, 6)) {
//            $file = DOMPDF_DIR . 'src/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 6)) . '.php';
//            require_once $file;
//        }
//    }
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

