<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */
declare(strict_types=1);

require_once('class/LineNotify.class.php');
require_once('class/GetPK10GameData.class.php');
require_once('class/CurlTool.class.php');
require_once('class/DataBaseTool.class.php');

$objLineTool = new LineNotify();
$objLineTool->doLineNotify("\n" . "最新賽車資訊檢查...");
try {
    $objGameTool = new getPK10GameData();
    $arrGameData = $objGameTool->getPK10Data("New", "");
//var_dump($arrGameData);
    if ($arrGameData == false) {
        throw new Exception("取得資料失敗");
    }
    $objDBTool = new DataBaseTool();
//var_dump($objDBTool->checkGame(strval($arrGameData[0])));
    if ($objDBTool->checkGame(strval($arrGameData[0])) == false) {
        $objDBTool->upLoadGame(strval($arrGameData[0]), $arrGameData[1]);
        $objLineTool->doLineNotify("\n" . "檢查完畢 新增賽事{$arrGameData[0]}");
    } else {
        $objLineTool->doLineNotify("\n" . "檢查完畢 暫無最新賽事");
    }
    $objDBTool->closeDB();
} catch (Exception $exception) {
    if (isset($objDBTool)) {
        $objDBTool->closeDB();
    }
    $objLineTool->doLineNotify("\n" . $exception->getMessage());
}
exit(0);