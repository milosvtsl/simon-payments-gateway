<?php
namespace App;
use App\Chart\DailyChart;
use App\Chart\MonthlyChart;
use App\Chart\MTDChart;
use App\Chart\WeeklyChart;
use App\Chart\WTDChart;
use App\Chart\YearlyChart;
use App\Chart\YTDChart;
use App\Provision\ProvisionStatusApp;
use App\Ticket\CreateTicketApp;
use App\Ticket\NewsApp;
use App\Ticket\RecentTicketsApp;
use User\Model\UserRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */


class AppManager {

    const DEFAULT_CONFIG = '{"app-chart-daily":{},"app-chart-wtd":{},"app-chart-mtd":{},"app-chart-ytd":{}}'; // ,"app-ticket-create":{}
//    const DEFAULT_CONFIG = '{"app-chart-daily":{},"app-chart-wtd":{},"app-chart-mtd":{},"app-chart-ytd":{},"app-provision-status":{}}';

    private $config;

    public function __construct(UserRow $SessionUser, $config=null) {
        $config = $config ?: $SessionUser->getAppConfig() ?: self::DEFAULT_CONFIG;
        $config = json_decode($config, true);
        if(!$config)
            throw new \InvalidArgumentException("Invalid Config JSON: " . $config);
        $this->config = $config;
        $this->user = $SessionUser;
    }

    public function forEachApp($callback) {
        $SessionUser = $this->user;

        $configs = $this->config;
        foreach($configs as $key => $config) {
            switch(strtolower($key)) {
                case 'app-chart-daily': $App = new DailyChart($SessionUser, $config); break;
                case 'app-chart-weekly': $App = new WeeklyChart($SessionUser, $config); break;
                case 'app-chart-wtd': $App = new WTDChart($SessionUser, $config); break;
                case 'app-chart-monthly': $App = new MonthlyChart($SessionUser, $config); break;
                case 'app-chart-mtd': $App = new MTDChart($SessionUser, $config); break;
                case 'app-chart-yearly': $App = new YearlyChart($SessionUser, $config); break;
                case 'app-chart-ytd': $App = new YTDChart($SessionUser, $config); break;

                case 'app-ticket-create': $App = new CreateTicketApp($SessionUser, $config); break;
                case 'app-ticket-view': $App = new RecentTicketsApp($SessionUser, $config); break;
                case 'app-ticket-news': $App = new NewsApp($SessionUser, $config); break;

                case 'app-provision-status': $App = new ProvisionStatusApp($SessionUser, $config); break;

                default: throw new \InvalidArgumentException("Invalid Config Key: " . $key);
            }
            $ret = $callback($App);
            if($ret === false)
                break;
        }
    }

    public function renderHTMLHeadContent() {
        $this->forEachApp(
            function(AbstractApp $App) {
                try {
                    $App->renderHTMLHeadContent();
                } catch (\Exception $ex) {
                    error_log($ex->getMessage());
                }
            }
        );
    }

    public function renderAppHTMLContent() {
        $i=0;
        $this->forEachApp(
            function(AbstractApp $App) use (&$i) {
                try {
                    $App->renderAppHTML();
                    if(++$i%2===0) echo '<br />';
                } catch (\Exception $ex) {
                    error_log($ex->getMessage());
                }
            }
        );
    }

}

