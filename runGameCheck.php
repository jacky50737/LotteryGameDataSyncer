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
$startGame = '32135120'; //頭31333329
//$endGame   = '31334331'; //For Test
$endGame = '32176592'; //正式

$count = $endGame - $startGame;
$lastDay = '2021-11-';
$lostGame = [];
for($gameTag=$startGame;$gameTag<=$endGame;$gameTag++){
    $gameData = $objDBTool->getGameData(intval($gameTag));
    if(!isset($gameData['game'])){
        echo "期數：{$gameTag} 缺失!\n";
        $lostGame[]=strval($gameTag);
    }
}

$dd = 1;
while ($dd<31){
    $sDay = $lastDay.$dd;
    $day = date('Y-m-d', strtotime($sDay));
    echo "Day：".$day."\n";
    $arrGameData = $objGameTool->getPK10Data("Date", $day);
    foreach ($arrGameData as $result) {
        $game = $result[0];
        $gno = $result[1];
        if(in_array(strval($game),$lostGame)){
            $isSuccess = $objDBTool->upLoadGame(strval($game), $gno);
            if($isSuccess){
                echo "期數：{$game} 已補上!\n";
            }else {
                echo "期數：{$game} 補失敗!\n";
            }
        }
    }
    $dd++;
}