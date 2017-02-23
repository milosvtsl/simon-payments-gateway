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
class MtdChart extends AbstractTotalsApp {
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

        $appClassName = 'app-chart-mtd';
        echo <<<HTML
        <div class="app-chart {$appClassName}">
            <canvas class="app-chart-canvas app-chart-canvas-mtd" ></canvas>
        </div>
HTML;

    }

    public function renderHTMLHeadContent()
    {
        parent::renderHTMLHeadContent();


        $stats = $this->getStats();

        $amount = number_format($stats['mtd'], 2);
        $count = number_format($stats['mtd_count']);
        $barChartData = $this->fetchBarData();

        $barChartData = json_encode($barChartData);

        echo <<<HTML

        <script>
            document.addEventListener('DOMContentLoaded', function(e) {
                var barChartData = {$barChartData};
                var canvasElms = document.getElementsByClassName('app-chart-canvas-mtd');
                
                for(var i=0; i<canvasElms.length; i++) {
                    var canvasElm = canvasElms[i];
                    var ctx = canvasElm.getContext("2d");
                    canvasElm.bar = new Chart(ctx, {
                        type: 'bar',
                        data: barChartData,
                        options: {
                            title:{
                                display:true,
                                text:"Month To Date \${$amount} ({$count})"
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
                     
                    canvasElm.parentNode.addEventListener('click', function(e) {
                         document.location.href = 'order?date_from={$stats['time_mtd_url']}';
                    });
                 }
            });
        </script>

HTML;

    }


    public function fetchStats() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $mtd  = date('Y-m-01 G:00:00', time() - $offset);
        $mtd_url  = date('Y-m-01', time());

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

        $SQL = <<<SQL
SELECT
	SUM(amount - total_returned_amount) as mtd,
	COUNT(*) as mtd_count
 FROM order_item oi

WHERE
    date>='{$mtd}'
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
        $stats['time_mtd'] = $mtd;
        $stats['time_mtd_url'] = $mtd_url;

        return $stats;
    }


    public function fetchBarData() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $mtd  = date('Y-m-01 G:00:00', time() - $offset);

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

        $SQL = <<<SQL
SELECT
  DATE_FORMAT(oi.date, '%d') as day,
  count(*) as count,
  sum(oi.amount) as amount,
  sum(oi.total_returned_amount) as returned
FROM order_item oi

WHERE
    date>='{$mtd}'
    AND status in ('Settled', 'Authorized')
    {$WhereSQL}
GROUP BY DATE_FORMAT(oi.date, '%Y%m%d')
LIMIT 32
SQL;

//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();

        $chartData = array(
            'labels' => array(),
            'datasets' => array(
                array(
                    'label' => "Amount",
                    'backgroundColor' => "#81aaba",
                    'data' => array_pad(array(), 32, 0)
                ),
                array(
                    'label' => "Returned",
                    'backgroundColor' => "#ba919e",
                    'data' => array_pad(array(), 32, 0)
                ),
                array(
                    'label' => "Count",
                    'backgroundColor' => "#8bc6bb",
                    'data' => array_pad(array(), 32, 0)
                )
            )
        );
        for($i=1; $i<32; $i++) {
            $chartData['labels'][] = $i % 3 === 0 ? $i : '';
        }
        while($order = $stmt->fetch()) {
            $chartData['datasets'][0]['data'][intval($order['day'])-1] = intval($order['amount']);
            $chartData['datasets'][1]['data'][intval($order['day'])-1] = intval($order['returned']);
            $chartData['datasets'][2]['data'][intval($order['day'])-1] = intval($order['count']);
        }

        return $chartData;
    }


}

