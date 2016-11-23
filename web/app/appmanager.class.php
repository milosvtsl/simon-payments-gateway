<?php
namespace App;
use App\Chart\AllChart;
use App\Chart\DailyChart;
use App\Chart\MonthlyChart;
use App\Chart\MTDChart;
use App\Chart\WeeklyChart;
use App\Chart\WTDChart;
use App\Chart\YearlyChart;
use App\Chart\YTDChart;
use App\Ticket\CreateTicketApp;
use App\Ticket\RecentTicketsApp;
use User\Model\UserRow;
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */


class AppManager {

    const DEFAULT_CONFIG = '{"chart-daily":{},"chart-wtd":{},"chart-mtd":{},"chart-ytd":{}}'; // ,"ticket-create":{},"ticket-view":{}}';

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
                case 'chart-all': $App = new AllChart($SessionUser, $config); break;
                case 'chart-daily': $App = new DailyChart($SessionUser, $config); break;
                case 'chart-weekly': $App = new WeeklyChart($SessionUser, $config); break;
                case 'chart-wtd': $App = new WTDChart($SessionUser, $config); break;
                case 'chart-monthly': $App = new MonthlyChart($SessionUser, $config); break;
                case 'chart-mtd': $App = new MTDChart($SessionUser, $config); break;
                case 'chart-yearly': $App = new YearlyChart($SessionUser, $config); break;
                case 'chart-ytd': $App = new YTDChart($SessionUser, $config); break;

                case 'ticket-create': $App = new CreateTicketApp($SessionUser, $config); break;
                case 'ticket-view': $App = new RecentTicketsApp($SessionUser, $config); break;

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
                $App->renderHTMLHeadContent();
            }
        );
    }

    public function renderAppHTMLContent() {
        $this->forEachApp(
            function(AbstractApp $App) {
                $App->renderAppHTML();
            }
        );
    }

}

