<?php
use Order\Model\OrderRow;
use Merchant\Model\MerchantRow;
/**
 * @var \Order\Model\OrderQueryStats $Stats
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 **/

if(!$export_filename)
    $export_filename = 'export.csv';
header("Content-Disposition: attachment; filename=\"$export_filename\"");
header("Content-Type: application/vnd.ms-excel");

echo "Count,Total,Settled,Void,Returned";
echo "\n".$Stats->getCount(),
    ',$'.$Stats->getTotal(),
    ',$'.$Stats->getSettledTotal(),
    ',$'.$Stats->getVoidTotal(),
    ',$'.$Stats->getReturnTotal();

echo "\n\n";
echo "\nUID,Amount,Fee,Status,Mode,Date,Invoice,Cust ID,Customer";
$offset = $SessionUser->getTimeZoneOffset();
foreach($Query as $Order) {
    /** @var OrderRow $Order */
    echo
        "\n", $Order->getUID(),
        ",$", $Order->getAmount(),
        ",$", $Order->getConvenienceFee() ?: 0,
        ",", $Order->getStatus(),
        ",", $Order->getEntryMode(),
        ",", $Order->getDate(),
        ",", str_replace(',', ';', $Order->getInvoiceNumber()),
        ",", str_replace(',', ';', $Order->getCustomerID()),
        ",", str_replace(',', ';', $Order->getCardHolderFullName());
}
