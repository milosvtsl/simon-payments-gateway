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

$out = system('git status');

var_dump($out);