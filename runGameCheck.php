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

$startGame = '31333329'; //頭31333329
//$endGame   = '31334331'; //For Test
$endGame = '32176592'; //正式

$count = $endGame - $startGame;

$msg = "";
for($gameTag=$startGame;$gameTag<=$endGame;$gameTag++){
    $gameData = $objDBTool->getGameData(intval($gameTag));
    if(!isset($gameData['game'])){
        echo "期數：{$gameTag} 缺失!\n";
        exit(0);
    }
}