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
    while (date("s") < 58) {
        if(intval(date("s")) % 5 == 0){
            $objGameTool = new getPK10GameData();
            $arrGameData = $objGameTool->getPK10Data("New", "");
//var_dump($arrGameData);
            if ($arrGameData == false) {
                throw new Exception("取得資料失敗");
            }
            $objDBTool = DataBaseTool::getInstance();
//var_dump($objDBTool->checkGame(strval($arrGameData[0])));
            if ($objDBTool->checkGame(strval($arrGameData[0])) == false) {
                $objDBTool->upLoadGame(strval($arrGameData[0]), $arrGameData[1]);
                $objLineTool->doLineNotify("\n" . "檢查完畢 新增賽事{$arrGameData[0]}");
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