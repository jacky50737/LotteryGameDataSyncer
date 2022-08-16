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
//$pass2Data = $objDBTool->getGameData(intval($arrGameData[0]-2));
//var_dump($pass2Data);
$forecastData = $objDBTool->getForecastData();
var_dump($forecastData);
foreach ($forecastData as $row){
    $status = $forecastTool->checkForecastStatus($arrGameData[1], $row['predict'], $row['name']);
    $status_C = "初始化";
    if($status){
        $row['status'] = 'SHOOT';
        $status_C = '中';
    }else{
        switch ($row['status']){
            case 'SHOOT':
                $row['status'] = 'MISS1';
                $status_C = '凹1';
                break;
            case 'MISS1':
                $row['status'] = 'MISS2';
                $status_C = '凹2';
                break;
            case 'MISS2':
                $row['status'] = 'DOWN';
                $status_C = '倒';
                break;
        }
    }
    var_dump($row['c_name']."-本期預測結果：".$status_C);
}

//$getPredict = $forecastTool->forecastNextGame();

