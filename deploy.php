<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

//chdir('web');
// Enable class autoloader
//spl_autoload_extensions('.class.php');
//spl_autoload_register();

// Test command
$cmd_test = 'ssh admin.paylogicnetwork.com -t "cd /usr/share/nginx/spg; php test.php;"';

// Deploy command
$cmd_deploy = 'ssh admin.paylogicnetwork.com -t "cd /usr/share/nginx/spg; git pull;"';

// Check git status
exec('git status', $out, $ret);
if(strpos(implode("\n", $out), 'nothing to commit, working directory clean') === false) {
    echo "Commit and push code before deploying, n00b";
    exit(1);
}

// Local Test
echo "\nTesting locally...\n";
require 'test.php';

// Deploy
echo "\nDeploying remotely...\n";
exec($cmd_deploy, $out, $ret);
if(strpos(implode("\n", $out), 'error') === false) {
    echo "Looks like there was an error";
    exit(1);
}
echo implode("\n", $out);

// Remote Test
echo "\nTesting remotely...\n";
$ret = system($cmd_test, $out);

// TODO revert on fail remotely?
// TODO check out production branch?