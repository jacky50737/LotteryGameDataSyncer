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
$objGameTool = new getPK10GameData();
$objDBTool = DataBaseTool::getInstance();
$forecastTool = ForecastTool::getInstance();

//$startGame = '31333329'; //頭31333329
$startGame = '32135121'; //頭31333329
//$endGame   = '31334331'; //For Test
$endGame = '32176592'; //正式

$count = $endGame - $startGame;
$lastDay = '2021-11-26';
$dd = 30;
while ($dd){
    $day = date('Y-m-d', strtotime($lastDay . "+1 days"));
    $arrGameData = $objGameTool->getPK10Data("Date", $day);
    foreach ($arrGameData as $result) {
        $game = $result[0];
        $gno = $result[1];
        $dbGameData = $objDBTool->getGameData(intval($game));
        if(!isset($gameData['game'])){
            echo "期數：{$game} 缺失!\n";
            $isSuccess = $objDBTool->upLoadGame(strval($game), $gno);
            if($isSuccess){
                echo "期數：{$game} 已補上!\n";
            }
        }
    }
    $dd--;
}
