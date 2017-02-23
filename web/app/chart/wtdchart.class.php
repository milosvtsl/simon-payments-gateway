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
class WtdChart extends AbstractTotalsApp {
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

        $appClassName = 'app-chart-wtd';
        echo <<<HTML
        <div class="app-chart {$appClassName}">
            <canvas class="app-chart-canvas app-chart-canvas-wtd" ></canvas>
        </div>
HTML;

    }

    public function renderHTMLHeadContent()
    {
        parent::renderHTMLHeadContent();


        $stats = $this->getStats();

        $amount = number_format($stats['wtd'], 2);
        $count = number_format($stats['wtd_count']);
        $barChartData = $this->fetchBarData();

        $barChartData = json_encode($barChartData);

        echo <<<HTML

        <script>
            document.addEventListener('DOMContentLoaded', function(e) {
                var barChartData = {$barChartData};
                    var canvasElms = document.getElementsByClassName('app-chart-canvas-wtd');
                    for(var i=0; i<canvasElms.length; i++) {
                    var canvasElm = canvasElms[i];
                    canvasElm.bar = new Chart(canvasElm, {
                        type: 'line',
                        data: barChartData,
                        options: {
                            title:{
                                display:true,
                                text:"Week To Date \${$amount} ({$count})"
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
                         document.location.href = 'order?date_from={$stats['time_wtd_url']}';
                    });
                 }
            });
        </script>

HTML;

    }


    public function fetchStats() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $wtd  = date('Y-m-d G:00:00', time() - 24*60*60*date('w', time() - $offset));
        $wtd_url  = date('Y-m-d', time() - 24*60*60*date('w'));

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

        $SQL = <<<SQL
SELECT
	SUM(amount - total_returned_amount) as wtd,
	COUNT(*) as wtd_count
 FROM order_item oi

WHERE
    date>='{$wtd}'
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
        $stats['time_wtd'] = $wtd;
        $stats['time_wtd_url'] = $wtd_url;

        return $stats;
    }


    public function fetchBarData() {
        $SessionUser = $this->getSessionUser();
        $offset = $SessionUser->getTimeZoneOffset('now');
        $wtd  = date('Y-m-d G:00:00', time() - 24*60*60*date('w', time() - $offset));

        $WhereSQL = '';
        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $WhereSQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

        $SQL = <<<SQL
SELECT
  DATE_FORMAT(oi.date, '%w') as day,
  count(*) as count,
  sum(oi.amount) as amount,
  sum(oi.total_returned_amount) as returned
FROM order_item oi

WHERE
    date>='{$wtd}'
    AND status in ('Settled', 'Authorized')
    {$WhereSQL}
GROUP BY DATE_FORMAT(oi.date, '%Y%m%d')
LIMIT 7
SQL;

//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();

        $chartData = array(
            'labels' => array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            'datasets' => array(
                array(
                    'label' => "Returned",
                    'backgroundColor' => "#ba919e",
                    'data' => array_pad(array(), 7, 0)
                ),
                array(
                    'label' => "Amount",
                    'backgroundColor' => "#81aaba",
                    'data' => array_pad(array(), 7, 0)
                ),
//                array(
//                    'label' => "Count",
//                    'backgroundColor' => "#8bc6bb",
//                    'data' => array_pad(array(), 7, 0)
//                )

            )
        );
        while($order = $stmt->fetch()) {
            $chartData['datasets'][0]['data'][intval($order['day'])] = intval($order['returned']);
            $chartData['datasets'][1]['data'][intval($order['day'])] = intval($order['amount']);
//            $chartData['datasets'][2]['data'][intval($order['day'])] = intval($order['count']);
        }

        return $chartData;
    }


}

