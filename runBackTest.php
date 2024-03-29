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

$startGame = '31333331'; //頭31333329
//$endGame   = '31334331'; //For Test
$endGame = '32176592'; //正式 (2020/01/01~2022/01/01)

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
            $strategy = explode('_', $row['name'])[4];
            switch ($strategy){
                default:
                case 'Forward':
                    $Data = $objDBTool->getGameData(intval($game - 2)); //抓-2期資料
                    break;
                case 'ThreeToNine':
                    $Data = $objDBTool->getGameData(intval($game - 1)); //抓-2期資料
                    break;
            }
            unset($Data['game']);
            $getPredict = $forecastTool->forecastNextGame($row['name'], $Data);

            $row['total_times'] = $row['total_times'] +1;
            $objDBTool->updateForecastTestTotalTimes($row['name'],intval($row['total_times']));

            if($row['status'] == 'SHOOT'){
                $row['shoot_times'] = $row['shoot_times'] +1;
                $objDBTool->updateForecastTestShootTimes($row['name'],intval($row['shoot_times']));
            }
        }

        if($balanceData[0] < 10000)
        {
            $objDBTool->inQueueLineNotify("\n回測第{$game}局\n策略{$row['c_name']}\n金額小於1萬\n目前金額：".$balanceData[0]);
            $balanceData[0] = 30000;
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

        if($balanceData[0] > 80000){
            $objDBTool->inQueueLineNotify("\n回測第{$game}局\n策略{$row['c_name']}\n金額小於8萬\n目前金額：".$balanceData[0]);
        }
    }
    echo "局{$game}數據\n".$msg."\n";
}
$objDBTool->inQueueLineNotify("\n回測{$count}局結果\n".$msg);
