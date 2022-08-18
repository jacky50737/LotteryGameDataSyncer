<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

declare(strict_types=1);

require_once('class/autoload.php');
$objDBTool = DataBaseTool::getInstance();
$objLineTool = new LineNotify();
//while (date('s') < 58){
    $data = $objDBTool->getQueueLineNotify();
    if(!empty($data['id'])){
        echo "處理待發送訊息[{$data['msg']}]...";
        $isSuccess = $objLineTool->doLineNotify($data['msg']);
        if($isSuccess){
            echo "成功!\n";
            $objDBTool->deQueueLineNotify($data['id']);
        }else{
            echo "失敗!\n";
        }
    }
//}