<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */
declare(strict_types=1);

require_once('class/autoload.php');

$objLineTool = new LineNotify();
$objLineTool->doLineNotify("\n" . "最新賽車資訊檢查...");
try {
    $objDBTool = DataBaseTool::getInstance();
    $forecastTool = ForecastTool::getInstance();
    while (date("s") < 58) {
        if (intval(date("s")) % 5 == 0) {
            $objGameTool = new getPK10GameData();
            $arrGameData = $objGameTool->getPK10Data("New", "");
//var_dump($arrGameData);
            if (!$arrGameData) {
                throw new Exception("取得資料失敗");
            }

            if (!$objDBTool->checkGame(strval($arrGameData[0]))) {
                $objDBTool->upLoadGame(strval($arrGameData[0]), $arrGameData[1]);

                $forecastData = $objDBTool->getForecastData();
//var_dump($forecastData);
                $msg = "";
                foreach ($forecastData as $row) {
                    $lastMsg ="上期預測號碼：{$row['predict']}";
                    $getPredict = $row['predict'];
                    $forecastTag = $forecastTool->explodeForecast($row['name']);
                    $status = $forecastTool->checkForecastStatus($arrGameData[1], $row['predict'], $row['name']);
                    $status_C = "初始化";
                    $forecastResult = $forecastTool->processeForecastStatus($row['status'], $status, $forecastTag['LEVELS']);
                    $row['status'] = $forecastResult['status'];
//    var_dump($row['c_name']."-本期預測結果：".$forecastResult['result']);
                    if (in_array($row['status'], ['SHOOT', 'DOWN']) or $row['game'] == '0') {
                        $pass2Data = $objDBTool->getGameData(intval($arrGameData[0] - 2)); //抓-2期資料
                        $gameData = $pass2Data['game'];
                        unset($pass2Data['game']);
//        var_dump($gameData);
//        var_dump($pass2Data);
                        $getPredict = $forecastTool->forecastNextGame($row['name'], $pass2Data);
                        $objDBTool->updateForecastTotalTimes($row['name'],intval($row['total_times'] + 1));

                        if($row['status'] == 'SHOOT'){
                            $objDBTool->updateForecastShootTimes($row['name'],intval($row['shoot_times']+1));
                        }

                    }

                    if(!$status and $row['name'] == 'YARDS_8_LEVELS_3' and !in_array($row['status'], ['SHOOT', 'DOWN']))
                    {
                        print "getPredict is ：".$getPredict."\n";
                        $getPredict = intval($row['predict']) + 3;
                        print "to getPredict is ：".$getPredict."\n";
                        if($getPredict >= 11){
                            var_dump("getPredict大於十為：");
                            var_dump($getPredict);
                            $getPredict = $getPredict - 10;
                        }
                    }
                    $objDBTool->updateForecastData($row['name'], $arrGameData[0], $getPredict, $row['status']);
                    if(!empty($getPredict)){
                        $msg .="\n-------";
                        $msg .= "\n$lastMsg\n{$row['c_name']}-本期預測結果：".$forecastResult['result']."\n下期預測號碼：".$getPredict;
                        $msg .= "\n目前策略準確度：".round((intval($row['shoot_times'])-intval($row['total_times']))/intval($row['total_times'])*100,2)."%";
                    }else{
                        echo "getPredict is empty!";
                        var_dump($getPredict);
                    }
                }
                $gameNum = implode('|',$arrGameData[1]);
                $objLineTool->doLineNotify(
                    "\n" . "檢查完畢 新增賽事$arrGameData[0]\n本期號碼：\n$gameNum".$msg);
            }
        }
        sleep(1);
    }
//    $objDBTool->closeDB();
    $objLineTool->doLineNotify("\n" . "本次檢查完畢!");
} catch (Exception $exception) {
    if (isset($objDBTool)) {
        $objDBTool->closeDB();
    }
    $objLineTool->doLineNotify("\n" . $exception->getMessage());
}
exit(0);