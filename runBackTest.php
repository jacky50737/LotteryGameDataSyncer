<?php
/**
 * 開發者 User
 * 創建於 2022/8/17
 * 使用   PhpStorm
 * 專案名稱LotteryGameDataSyncer
 */
declare(strict_types=1);

require_once('class/autoload.php');

$objLineTool = new LineNotify();
$objDBTool = DataBaseTool::getInstance();
$forecastTool = ForecastTool::getInstance();

$startGame = '31333331';
//$endGame   = '31333431'; //For Test
$endGame = '32439158'; //正式

$count = $endGame - $startGame;

$msg = "";
for($gameTag=$startGame;$gameTag<=$endGame;$gameTag++){
    $gameData = $objDBTool->getGameData(intval($gameTag));
    $game = $gameData['game'];
    unset($gameData['game']);

    $forecastData = $objDBTool->getForecastTestData();

    $msg = "";
    foreach ($forecastData as $row) {
        $lastMsg ="上期預測號碼：{$row['predict']}";
        $getPredict = $row['predict'];
        $forecastTag = $forecastTool->explodeForecast($row['name']);
        $status = $forecastTool->checkForecastTestStatus($gameData, $row['predict'], $row['name']);
        $status_C = "初始化";

        $forecastResult = $forecastTool->processeForecastStatus($row['status'], $status, $forecastTag['LEVELS']);
        $balanceData = $forecastTool->processBalance($row['balance'], $row['name'], $forecastResult['status'], $row['status'], $row['fee']);
        $row['status'] = $forecastResult['status'];
//    var_dump($row['c_name']."-本期預測結果：".$forecastResult['result']);
        if (in_array($row['status'], ['SHOOT', 'DOWN']) or $row['game'] == '0') {
            $pass2Data = $objDBTool->getGameData(intval($game - 2)); //抓-2期資料
            unset($pass2Data['game']);
//        var_dump($gameData);
//        var_dump($pass2Data);
            $getPredict = $forecastTool->forecastNextGame($row['name'], $pass2Data);
            $row['total_times'] = $row['total_times'] +1;
            $objDBTool->updateForecastTestTotalTimes($row['name'],intval($row['total_times']));

            if($row['status'] == 'SHOOT'){
                $row['shoot_times'] = $row['shoot_times'] +1;
                $objDBTool->updateForecastTestShootTimes($row['name'],intval($row['shoot_times']));
            }

        }

        $objDBTool->updateForecastTestData($row['name'], $game, $getPredict, $row['status'], $balanceData[0], $balanceData[1]);
        if(!empty($getPredict)){
            $msg .="\n-------";
            $msg .= "\n$lastMsg\n{$row['c_name']}-本期預測結果：".$forecastResult['result']."\n下期預測號碼：".$getPredict;
            if($row['total_times'] == 0){
                $msg .= "\n目前策略準確度：暫無";
            }else{
                $msg .= "\n目前策略準確度：".round((intval($row['shoot_times']))/intval($row['total_times'])*100,2)."%";
            }
            $msg .= "\n總倒次數：".$row['total_times']-$row['shoot_times'];
            $msg .= "\n總預測次數：".$row['total_times'];
            $msg .= "\n剩餘本金：".$row['balance'];
            $msg .= "\n水錢：".$row['fee'];
        }else{
            echo "getPredict is empty!";
            var_dump($getPredict);
        }

        if($row['balance'] > 80000){
            $objLineTool->doLineNotify("\n回測第{$count}局\n策略{$row['name']}\n金額小於8萬\n目前金額：".$row['balance']);
        }

        if($row['balance'] < 10000){
            $objLineTool->doLineNotify("\n回測第{$count}局\n策略{$row['name']}\n金額小於1萬\n目前金額：".$row['balance']);
        }
    }
    echo $msg;
}
$objLineTool->doLineNotify("\n回測{$count}局結果\n".$msg);
