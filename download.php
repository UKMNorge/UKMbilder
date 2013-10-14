<?php
if(!isset($_GET['image']))
	die('feil');

#/* UKM LOADER */ if(!defined('UKM_HOME')) define('UKM_HOME', '/home/ukmno/public_html/UKM/'); require_once(UKM_HOME.'loader.php');
#UKM_loader('sql');

$info = explode('.', $_GET['image']);
$_GET['name'] = 'UKM '.$_GET['name'].'.'.strtolower(end($info));
$_GET['image'] = str_replace('http123','http://',$_GET['image']);
header('Content-Disposition: attachment; filename="'.$_GET['name'].'"');
readfile($_GET['image']);
exit();
?>