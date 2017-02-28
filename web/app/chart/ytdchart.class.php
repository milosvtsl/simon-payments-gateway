<?php
namespace App\Chart;
use System\Config\DBConfig;
use User\Model\UserRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */
class YtdChart extends AbstractTotalsApp {
    const SESSION_KEY = __FILE__;

    const TIMEOUT = 60;

    private $config;

    public function __construct(UserRow $SessionUser, $config) {
        parent::__construct($SessionUser);
        $this->config = $config;
    }

    /**
     * Generate a string representing the user configuration for this app
     * @return mixed
     */
    protected function getConfig() {
        return $this->config;
    }

    /**
     * Print an HTML representation of this app
     * @param array $params
     * @return mixed
     */
    function renderAppHTML(Array $params = array()) {

        $appClassName = 'app-chart-ytd';
        echo <<<HTML
        <div class="app-chart {$appClassName}">
            <canvas class="app-chart-canvas app-chart-canvas-ytd" ></canvas>
        </div>
HTML;

    }

    public function renderHTMLHeadContent()
    {
        parent::renderHTMLHeadContent();


        $stats = $this->getStats();

        $amount = number_format($stats['ytd'], 2);
        $count = number_format($stats['ytd_count']);
        $barChartData = $this->fetchBarData();

        $barChartData = json_encode($barChartData);

        echo <<<HTML

        <script>
            document.addEventListener('DOMContentLoaded', function(e) {
                var barChartData = {$barChartData};
                    var canvasElms = document.getElementsByClassName('app-chart-canvas-ytd');
                    for(var i=0; i<canvasElms.length; i++) {
                    var canvasElm = canvasElms[i];
                    canvasElm.bar = new Chart(canvasElm, {
                        type: 'line',
                        data: barChartData,
                        options: {
                            title:{
                                display:true,
                                text:"Year To Date Sales \${$amount} ({$count})"
                            },
                            tooltips: {
                                mode: 'index',
                                intersect: false
                            },
                            responsive: true,
                            scales: {
                                xAxes: [{
                                    stacked: true
                                }],
                                yAxes: [{
                                    stacked: true
                                }]
                            }
                        }
                    });
                     
                    canvasElm.addEventListener('click', function(e) {
                         document.location.href = 'order?date_from={$stats['time_ytd_url']}';
                    });
                 }
            });
        </script>

HTML;

    }


    public function fetchStats() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $ytd  = date('Y-01-01', time() - $offset);
        $ytd_url  = date('Y-01-01', time());

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = " . $SessionUser->getMerchantID();

        $SQL = <<<SQL
SELECT
	SUM(amount - total_returned_amount) as ytd,
	COUNT(*) as ytd_count
 FROM order_item oi

WHERE
    date>='{$ytd}'
    AND status in ('Settled', 'Authorized')
    {$WhereSQL}
SQL;


//        $duration = -microtime(true);
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();
        $stats = $stmt->fetch();
//        $duration += microtime(true);
//        $stats['duration'] = $duration;
        $stats['time_ytd'] = $ytd;
        $stats['time_ytd_url'] = $ytd_url;

        return $stats;
    }


    public function fetchBarData() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $ytd  = date('Y-01-01', time() + $offset);

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = " . $SessionUser->getMerchantID();

        $SQL = <<<SQL
SELECT
  DATE_FORMAT(oi.date, '%m') as month,
  count(*) as count,
  sum(oi.amount) as amount,
  sum(oi.total_returned_amount) as returned
FROM order_item oi

WHERE
    date>='{$ytd}'
    AND status in ('Settled', 'Authorized')
    {$WhereSQL}
GROUP BY DATE_FORMAT(oi.date, '%Y%m')
LIMIT 32
SQL;

//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();

        $chartData = array(
            'labels' => array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'datasets' => array(
                array(
                    'label' => "Returned",
                    'backgroundColor' => "#f0614d",
                    'data' => array_pad(array(), 12, 0)
                ),
                array(
                    'label' => "Amount",
                    'backgroundColor' => "#87cb27",
                    'data' => array_pad(array(), 12, 0)
                ),
//                array(
//                    'label' => "Count",
//                    'backgroundColor' => "#8bc6bb",
//                    'data' => array_pad(array(), 12, 0)
//                )
            )
        );
        while($order = $stmt->fetch()) {
            $chartData['datasets'][0]['data'][intval($order['month'])-1] = intval($order['returned']);
            $chartData['datasets'][1]['data'][intval($order['month'])-1] = intval($order['amount']);
//            $chartData['datasets'][2]['data'][intval($order['month'])-1] = intval($order['count']);
        }

        return $chartData;
    }


}

