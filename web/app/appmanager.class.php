<?php
namespace App;
use App\Chart\DailyChart;
use App\Chart\MonthlyChart;
use App\Chart\MTDChart;
use App\Chart\WeeklyChart;
use App\Chart\WTDChart;
use App\Chart\YearlyChart;
use App\Chart\YTDChart;
use User\Model\UserRow;
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */


class AppManager {

    const DEFAULT_CONFIG = '["chart-all":{},"chart-daily":{},"chart-wtd":{},"chart-mtd":{},"chart-ytd":{}]';

    private $config;

    public function __construct(UserRow $SessionUser, $config=null) {
        $this->config = $config ?: $SessionUser->getAppConfig() ?: self::DEFAULT_CONFIG;
        $this->user = $SessionUser;
    }

    public function forEachApp($callback) {
        $SessionUser = $this->user;

        $configs = json_decode($this->config, true);
        foreach($configs as $key => $config) {
            switch(strtolower($key)) {
                case 'chart-daily': $App = new DailyChart($SessionUser, $config); break;
                case 'chart-weekly': $App = new WeeklyChart($SessionUser, $config); break;
                case 'chart-wtd': $App = new WTDChart($SessionUser, $config); break;
                case 'chart-monthly': $App = new MonthlyChart($SessionUser, $config); break;
                case 'chart-mtd': $App = new MTDChart($SessionUser, $config); break;
                case 'chart-yearly': $App = new YearlyChart($SessionUser, $config); break;
                case 'chart-ytd': $App = new YTDChart($SessionUser, $config); break;
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

