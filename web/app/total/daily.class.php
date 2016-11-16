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


class Daily extends AbstractApp {
    const TIMEOUT = 60;

    private $stats = null;
    private $user = null;

    public function __construct(UserRow $SessionUser) {
        // Session key based on file path
        $sessionKey = __FILE__;

        // Check Session for cached statistics
        if(isset($_SESSION[$sessionKey]))  {
            $this->stats = $_SESSION[$sessionKey];

            // Clear cache if the timeout has been reached
            if($this->stats['timestamp'] < time() + self::TIMEOUT)
                $this->stats = null;
        }

        $offset = 0;

        if(!$this->stats) {
            $this->stats = $this->fetchStats($offset);
            $this->stats['timestamp'] = time();
            $_SESSION[$sessionKey] = $this->stats;
        }
        $this->user = $SessionUser;
    }

    /**
     * Print an HTML representation of this app
     * @param array $params
     * @return mixed
     */
    function printAppHTML(Array $params = array()) {
        $stats = $this->stats;

?>
<a href="order?date_from=<?php echo $stats['time_today']; ?>&order=asc&order-by=id" class="stat-box stat-box-first">
    <div class="stat-large">$<?php echo number_format($stats['today'], 2); ?></div>
    <div>Today (<?php echo number_format($stats['today_count']); ?>)</div>
</a>
    <?php

    }

    public function fetchStats($offset=null) {
        $today = date('Y-m-d', time() + $offset);

        $SQL = <<<SQL
SELECT
	SUM(CASE WHEN date>='{$today}' THEN amount ELSE 0 END) as today,
	SUM(CASE WHEN date>='{$today}' THEN 1 ELSE 0 END) as today_count

 FROM order_item oi

 WHERE status in ('Settled', 'Authorized')
SQL;

        $SessionUser = $this->user;
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
        $stats['time_year_to_date'] = $year_to_date;

        return $stats;
    }

}

