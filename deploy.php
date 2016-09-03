<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

chdir('web');
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

exec('git status', $out, $ret);
if(strpos(implode("\n", $out), 'nothing to commit, working directory clean') === false) {
    echo "Commit and push code before deploying, n00b";
    exit(1);
}


$ret = system('ssh admin.paylogicnetwork.com -t "cd /usr/share/nginx/spg; git pull;"', $out);