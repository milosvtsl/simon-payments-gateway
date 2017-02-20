<?php
use Order\Model\OrderRow;

/**
 * @var \Order\Model\OrderQueryStats $Stats
 * @var \View\AbstractListView $this
 * @var PDOStatement $Query
 * @var PDOStatement $ReportQuery
 * @var Array $params
 **/

if(!$export_filename)
    $export_filename = 'export.csv';
header("Content-Disposition: attachment; filename=\"$export_filename\"");
header("Content-Type: application/vnd.ms-excel");

echo '"Span","Count","Authorized","Settled","Void","Returned","",""';

if(in_array(strtolower(@$params['action']), array('export', 'export-stats'))) {
    foreach ($ReportQuery as $Report) {
        /** @var \Order\Model\OrderQueryStats $Report */
        echo "\n\"" . $Report->getGroupSpan(),
            '", ' . $Report->getCount(),
            ', $' . $Report->getTotal(),
            ', $' . $Report->getSettledTotal(),
            ', $' . $Report->getVoidTotal(),
            ', $' . $Report->getReturnTotal(),
            ',,';
    }
}

if(in_array(strtolower(@$params['action']), array('export', 'export-data'))) {
    echo "\n\"Total" ,
        '", ' . $Stats->getCount(),
        ', $' . $Stats->getTotal(),
        ', $' . $Stats->getSettledTotal(),
        ', $' . $Stats->getVoidTotal(),
        ', $' . $Stats->getReturnTotal(),
        '';

    echo "\n\n";
    echo "\nUID,Amount,Status,Mode,Date,Invoice,Cust ID,Customer";
    $offset = $SessionUser->getTimeZoneOffset();
    foreach($Query as $Order) {
        /** @var OrderRow $Order */
        echo
            "\n", $Order->getUID(),
            ', $', $Order->getAmount(),
//            ", $", $Order->getConvenienceFee() ?: 0,
            ', ', $Order->getStatus(),
            ', ', $Order->getEntryMode(),
            ', ', $Order->getDate($SessionUser->getTimeZone())->format('M dS h:i A'),
            ', ', str_replace(',', ';', $Order->getInvoiceNumber()),
            ', ', str_replace(',', ';', $Order->getCustomerID()),
            ', ', str_replace(',', ';', $Order->getPayeeFullName()),
            '';
    }
}