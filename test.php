<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/30 上午 10:19.
 */

declare(strict_types=1);

//require_once('class/DataBaseTool.class.php');
//
//$fileName = "test";
//
//$day = date("Y-m-d");
//
//$objDBTool = new DataBaseTool();
//
//$lastGame = $objDBTool->logLastTimeProcess("getListTime", $fileName, "", $day);
//
//var_dump($lastGame);
//
//$game = rand();
//
//$objDBTool->logLastTimeProcess("save", $fileName, strval($game), $day); //紀錄執行成功進度
//
//$lastGame = $objDBTool->logLastTimeProcess("getListTime", $fileName, "", $day);
//
//var_dump($lastGame);
//

$start_time = microtime(true);
$today = date("Y-m-d",intval($start_time + (8 * 60 * 60)));
var_dump($today);
echo "done";

