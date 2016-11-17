<?php
namespace App\Total;
use App\AbstractApp;
use System\Config\DBConfig;
use User\Model\UserRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */
class WeeklyApp extends AbstractTotalsApp {
    const SESSION_KEY = __FILE__;

    const TIMEOUT = 60;

    public function __construct(UserRow $SessionUser) {
        parent::__construct($SessionUser);
    }

    /**
     * Print an HTML representation of this app
     * @param array $params
     * @return mixed
     */
    function renderAppHTML(Array $params = array()) {
        $stats = $this->getStats();

        $amount = number_format($stats['weekly'], 2);
        $count = number_format($stats['weekly_count']);
        
        echo <<<HTML
        <div class="app-total app-total-weekly">
            <a href="order?date_from={$stats['time_weekly']}" class="app-total-weekly-amount">
                ${$amount}
            </a> 
            <a href="order?date_from={$stats['time_weekly']}" class="app-total-weekly-count">
                Weekly ({$count})
            </a> 
        </div>
HTML;
    }

    public function fetchStats() {
        $offset = 0;

        $weekly  = date('Y-m-d', time() - 24*60*60*7 + $offset);

        $SQL = <<<SQL
SELECT
	SUM(CASE WHEN date>='{$weekly}' THEN amount ELSE 0 END) as weekly,
	SUM(CASE WHEN date>='{$weekly}' THEN 1 ELSE 0 END) as weekly_count

 FROM order_item oi

 WHERE status in ('Settled', 'Authorized')
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
        $stats['time_weekly'] = $weekly;

        return $stats;
    }

}

