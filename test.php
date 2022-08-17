<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/30 上午 10:19.
 */

declare(strict_types=1);

require_once('class/autoload.php');

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

//$server = "104.168.139.189";         # MySQL/MariaDB 伺服器
//$dbuser = "pjtvqdla_jacky50737";       # 使用者帳號
//$dbpassword = "Aa174677178508123"; # 使用者密碼
//$dbname = "pjtvqdla_PK10";    # 資料庫名稱

//# 連接 MySQL/MariaDB 資料庫
//$connection = new mysqli($server, $dbuser, $dbpassword, $dbname);
//# 檢查連線是否成功
//if ($connection->connect_error) {
//    die("連線失敗：" . $connection->connect_error);
//}

$objDBTool = DataBaseTool::getInstance();
$forecastTool = ForecastTool::getInstance();
$arrGameData[0] = '32437750';
$arrGameData[1] = [
    'no1'=>7,
    'no2'=>6,
    'no3'=>5,
    'no4'=>4,
    'no5'=>1,
    'no6'=>3,
    'no7'=>2,
    'no8'=>10,
    'no9'=>8,
    'no10'=>9,
    ];


//$forecastData = $objDBTool->getForecastData();
////var_dump($forecastData);
//foreach ($forecastData as $row){
//    $status = $forecastTool->checkForecastStatus($arrGameData[1], $row['predict'], $row['name']);
//    $status_C = "初始化";
//    $forecastResult = $forecastTool->processeForecastStatus($row['status'], $status);
//    $row['status'] = $forecastResult['status'];
////    var_dump($row['c_name']."-本期預測結果：".$forecastResult['result']);
//    if(in_array($row['status'],['SHOOT','DOWN'])){
//        $pass2Data = $objDBTool->getGameData(intval($arrGameData[0]-2)); //抓-2期資料
//        $gameData = $pass2Data['game'];
//        unset($pass2Data['game']);
////        var_dump($gameData);
////        var_dump($pass2Data);
//        $getPredict = $forecastTool->forecastNextGame($row['name'],$pass2Data);
//        $objDBTool->updateForecastData($row['name'],$arrGameData[1],$row['predict'],$row['status']);
//    }
//}

//$getPredict = $forecastTool->forecastNextGame();

//----KNN TEST-----

//取得主機使用狀況
function get_server_memory_usage(){

    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;

    return round($memory_usage,2)."%";
}

function get_server_cpu_usage(){

    $load = sys_getloadavg();
    return $load[0]."%";

}

if(function_exists('shell_exec')) {
    echo "shell_exec is enabled";
} else {
    echo "shell_exec is disabled";
}

echo "RAM：";
print get_server_memory_usage();
echo "\nCPU";
print get_server_cpu_usage();
