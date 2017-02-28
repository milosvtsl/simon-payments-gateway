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
class DailyChart extends AbstractTotalsApp {
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

        $appClassName = 'app-chart-today';
        echo <<<HTML
        <div class="app-chart {$appClassName}">
            <canvas class="app-chart-canvas app-chart-canvas-daily" ></canvas>
        </div>
HTML;

    }

    public function renderHTMLHeadContent()
    {
        parent::renderHTMLHeadContent();


        $stats = $this->getStats();

        $amount = number_format($stats['today'], 2);
        $count = number_format($stats['today_count']);
        $barChartData = $this->fetchBarData();

        $barChartData = json_encode($barChartData);

        echo <<<HTML

        <script>
            document.addEventListener('DOMContentLoaded', function(e) {
                var barChartData = {$barChartData};
                    var canvasElms = document.getElementsByClassName('app-chart-canvas-daily');
                    for(var i=0; i<canvasElms.length; i++) {
                    var canvasElm = canvasElms[i];
                    canvasElm.bar = new Chart(canvasElm, {
                        type: 'line',
                        data: barChartData,
                        options: {
                            title:{
                                display:true,
                                text:"Today's Sales \${$amount} ({$count})"
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
                                    stacked: true,
                                    fontSize: 40
                                }]
                            }
                        }
                    });
                     
                    canvasElm.parentNode.addEventListener('click', function(e) {
                         document.location.href = 'order?date_from={$stats['time_today_url']}';
                    });
                 }
            });
        </script>

HTML;

    }


    public function fetchStats() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $today = date('Y-m-d G:00:00', time() - $offset);
        $today_url = date('Y-m-d', time() - $offset);

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = " . $SessionUser->getMerchantID();

        $SQL = <<<SQL
SELECT
	SUM(amount - total_returned_amount) as today,
	COUNT(*) as today_count
 FROM order_item oi

WHERE
    date>='{$today}'
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
        $stats['time_today_url'] = $today_url;

        return $stats;
    }


    public function fetchBarData() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $today = date('Y-m-d G:00:00', time() - $offset);
        $end = date('Y-m-d G:00:00', time() - $offset + 24*60*60);

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = " . $SessionUser->getMerchantID();

        $SQL = <<<SQL
SELECT
  DATE_FORMAT(oi.date, '%H') as hour,
  count(*) as count,
  sum(oi.amount) as amount,
  sum(oi.total_returned_amount) as returned
FROM order_item oi

WHERE
    date BETWEEN '{$today}' AND '{$end}'
    AND status in ('Settled', 'Authorized')
    {$WhereSQL}
GROUP BY DATE_FORMAT(oi.date, '%Y%m%d%H')
order by hour;
LIMIT 24
SQL;

//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";


        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();

        $chartData = array(
            'labels' => array(),
            'datasets' => array(
                array(
                    'label' => "Returned",
                    'backgroundColor' => "#f0614d",
                    'data' => array_pad(array(), 24, 0)
                ),
                array(
                    'label' => "Amount",
                    'backgroundColor' => "#87cb27",
                    'data' => array_pad(array(), 24, 0)
                ),
//                array(
//                    'label' => "Count",
//                    'backgroundColor' => "#8bc6bb",
//                    'data' => array_pad(array(), 24, 0)
//                )
            )
        );
        for($i=1; $i<24; $i++) {
            $chartData['labels'][] = ($i<12?$i.'am':($i>12?$i-12:$i).'pm');
        }
        while($order = $stmt->fetch()) {
            $hour = (intval($order['hour']) - $offset/(60*60) ) % 24;

            $chartData['datasets'][0]['data'][$hour] = intval($order['returned']);
            $chartData['datasets'][1]['data'][$hour] = number_format(intval($order['amount']), 2);
//            $chartData['datasets'][2]['data'][intval($order['hour'])] = intval($order['count']);
        }

        return $chartData;
    }


}

