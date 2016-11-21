<?php
namespace App\Chart;
use App\AbstractApp;
use System\Config\DBConfig;
use User\Model\UserRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */
class WTDChart extends AbstractTotalsApp {
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
        $stats = $this->getStats();

        $amount = number_format($stats['week_to_date'], 2);
        $count = number_format($stats['week_to_date_count']);
        
        echo <<<HTML
        <div class="app-chart app-chart-wtd">
            <a href="order?date_from={$stats['time_week_to_date']}" class="app-chart-amount app-chart-wtd-amount">
                \${$amount}
            </a> 
            <a href="order?date_from={$stats['time_week_to_date']}" class="app-chart-count app-chart-wtd-count">
                This week ({$count})
            </a> 
        </div>
HTML;
    }

    public function fetchStats() {
        $offset = 0;

        $week_to_date = date('Y-m-d', time() - 24*60*60*date('w') + $offset);

        $SQL = <<<SQL
SELECT
	SUM(amount) as week_to_date,
	COUNT(*) as week_to_date_count
 FROM order_item oi

WHERE
    date>='{$week_to_date}'
    AND status in ('Settled', 'Authorized')
SQL;

        $SessionUser = $this->getSessionUser();
        $ids = $SessionUser->getMerchantList() ?: array(-1);
        $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";
//            $SQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . intval($userID) . " AND um.id_merchant = oi.merchant_id)";

        $duration = -microtime(true);
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();
        $stats = $stmt->fetch();
        $duration += microtime(true);
        $stats['duration'] = $duration;
        $stats['time_week_to_date'] = $week_to_date;

        return $stats;
    }
}

