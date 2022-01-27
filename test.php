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

//$start_time = microtime(true);
//$today = date("Y-m-d A h:i:s",intval($start_time + (8 * 60 * 60)));
//var_dump($today);
//echo "done";

$server = "104.168.139.189";         # MySQL/MariaDB 伺服器
$dbuser = "pjtvqdla_jacky50737";       # 使用者帳號
$dbpassword = "Aa174677178508123"; # 使用者密碼
$dbname = "pjtvqdla_PK10";    # 資料庫名稱

# 連接 MySQL/MariaDB 資料庫
$connection = new mysqli($server, $dbuser, $dbpassword, $dbname);

# 檢查連線是否成功
if ($connection->connect_error) {
    die("連線失敗：" . $connection->connect_error);
}

