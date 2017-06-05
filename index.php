<?php
//header('Access-Control-Allow-Origin: *.tuyensinh247.com');
//header('Access-Control-Allow-Methods:POST');
date_default_timezone_set('Asia/Ho_Chi_Minh');
@session_start();
ini_set('session.cookie_domain', '.tuyensinh247.com');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
//error_reporting(0);
//$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$yii=dirname(__FILE__).'../../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php'; 
defined('YII_DEBUG') or define('YII_DEBUG',true);
require_once($yii);
Yii::createWebApplication($config)->run();
//echo Yii::app()->cache->keyPrefix;
?>