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
class WeeklyChart extends AbstractTotalsApp {
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

        $appClassName = 'app-chart-weekly';
        echo <<<HTML
        <div class="app-chart {$appClassName}">
            <canvas class="app-chart-canvas app-chart-canvas-weekly" ></canvas>
        </div>
HTML;

    }

    public function renderHTMLHeadContent()
    {
        parent::renderHTMLHeadContent();


        $stats = $this->getStats();

        $amount = number_format($stats['weekly'], 2);
        $count = number_format($stats['weekly_count']);
        $barChartData = $this->fetchBarData();

        $barChartData = json_encode($barChartData);

        echo <<<HTML

        <script>
            document.addEventListener('DOMContentLoaded', function(e) {
                var barChartData = {$barChartData};
                    var canvasElms = document.getElementsByClassName('app-chart-canvas-weekly');
                    for(var i=0; i<canvasElms.length; i++) {
                    var canvasElm = canvasElms[i];
                    canvasElm.bar = new Chart(canvasElm, {
                        type: 'bar',
                        data: barChartData,
                        options: {
                            title:{
                                display:true,
                                text:"Weekly's Sales \${$amount} ({$count})"
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
                         document.location.href = 'order?date_from={$stats['time_weekly']}';
                    });
                 }
            });
        </script>

HTML;

    }


    public function fetchStats() {
        $SessionUser = $this->getSessionUser();
        $offset = -$SessionUser->getTimeZoneOffset('now');
        $weekly  = date('Y-m-d G:i:s', time() - 24*60*60*7 + $offset);

        $SQL = <<<SQL
SELECT
	SUM(amount - total_returned_amount) as weekly,
	COUNT(*) as weekly_count
 FROM order_item oi

WHERE
    date>='{$weekly}'
    AND status in ('Settled', 'Authorized')
SQL;

        $SessionUser = $this->getSessionUser();
//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";

        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $SQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

//        $duration = -microtime(true);
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();
        $stats = $stmt->fetch();
//        $duration += microtime(true);
//        $stats['duration'] = $duration;
        $stats['time_weekly'] = $weekly;

        return $stats;
    }


    public function fetchBarData() {
        $SessionUser = $this->getSessionUser();
        $offset = -$SessionUser->getTimeZoneOffset('now');
        $weekly  = date('Y-m-d G:i:s', time() - 24*60*60*7 + $offset);

        $SQL = <<<SQL
SELECT
  DATE_FORMAT(oi.date, '%U') as day,
  count(*) as count,
  sum(oi.amount) as amount
FROM order_item oi

WHERE
    date>='{$weekly}'
    AND status in ('Settled', 'Authorized')
GROUP BY DATE_FORMAT(oi.date, '%Y%m%d')
LIMIT 24
SQL;

//        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";

        if(!$SessionUser->hasAuthority('ROLE_ADMIN'))
            $SQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . $SessionUser->getID() . " AND um.id_merchant = oi.merchant_id)";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();

        $chartData = array(
            'labels' => array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            'datasets' => array(
                array(
                    'label' => "Amount",
                    'backgroundColor' => "#20465c",
                    'data' => array_pad(array(), 24, 0)
                ),
                array(
                    'label' => "Returned",
                    'backgroundColor' => "#d27171",
                    'data' => array_pad(array(), 24, 0)
                )
            )
        );
        while($order = $stmt->fetch()) {
            $chartData['datasets'][0]['data'][$order['day']] = intval($order['amount']);
        }

        return $chartData;
    }


}

