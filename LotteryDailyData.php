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

try {
    $objLineTool = new LineNotify();
    $objLineTool->doLineNotify("\n" . "歷史賽車資訊檢查...");
    $file = fopen("DailyLock.txt", "r");
    $lock = fgets($file);
    fclose($file);

    if ($lock == 'off')
    {

        $file = fopen("DailyLock.txt", "w");
        fwrite($file, "on");
        fclose($file);

        $start_time = microtime(true);
        $objLineTool->doLineNotify("\n" . "開始時間:" . $start_time);

        $file = fopen("log.txt", "r");
        $lastDay = fgets($file);
        fclose($file);

        $tomorrow = date('Y-m-d', strtotime($lastDay . "+1 days"));

        $today = date("Y-m-d");

        if ($tomorrow < $today)
        {
            $day = $tomorrow;
        }
        else
        {
            $day = $today;
            sleep(1800);
        }

        $objGameTool = new getPK10GameData();
        $arrGameData = $objGameTool->getPK10Data("Date", $day);

        if (!empty($arrGameData))
        {
            $objLineTool->doLineNotify("\n" . "寫入開始時間..."."\n" . "載入遊戲數據中...");
            $file = fopen("locktime.txt", "w");
            fwrite($file, $start_time . "\n");
            fclose($file);

            foreach ($arrGameData as $result)
            {
                try
                {
                    $game = $result[0];
                    $gno = $result[1];

                    $objDBTool = new DataBaseTool();

                    if ($objDBTool->checkGame(strval($game)) == false)
                    {
                        $objDBTool->upLoadGame(strval($game), $gno);
                    }
                    else
                    {
                        $objLineTool->doLineNotify("\n" . "本期已存在 前往下一期最新賽事");
                    }
                    $objDBTool->closeDB();

                    usleep(1000000);

                } catch (Exception $exception) {
                    $error_msg = "\n" . '[error]' . "\n" .
                        '上傳資料時發生錯誤，錯誤發生時間，' .
                        "\n" . '錯誤發生時間： ' . "\n" .
                        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                        "\n" . ' 錯誤訊息： ' . $exception->getMessage();
                    $objLineTool = new LineNotify();
                    $objLineTool->doLineNotify($error_msg);
                }
            }
            $objLineTool->doLineNotify("\n讀寫完成...");
            $end_time = microtime(true);

            $time_total = $end_time - $start_time;

            $file = fopen("log.txt", "w");
            fwrite($file, $day);
            fclose($file);

            $process_msg = "\n" . "日期：" . $day . "執行了：" . $time_total . "\n";
            $objLineTool = new LineNotify();
            $objLineTool->doLineNotify($process_msg);

            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);
        }
    }
    else
    {
        $objLineTool->doLineNotify("\n" . "正在爬號中 !");
        $file = fopen("locktime.txt", "r");
        $locktime = fgets($file);
        fclose($file);
        $objLineTool->doLineNotify("\n" . "當前JOB已執行：" . (microtime(true) - floatval($locktime)) . "秒");

        if ((microtime(true) - floatval($locktime)) > 2100)
        {
            $objLineTool->doLineNotify("\n" . "已解除鎖定");
            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);
        }
    }

} catch (Exception $exception) {
    $error_msg = "\n" . '[error]' . "\n" .
        '發生未知錯誤，錯誤發生時間，' .
        "\n" . '錯誤發生時間： ' . "\n" .
        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
        "\n" . ' 發生錯誤行： ' . $exception->getLine() .
        "\n" . ' 錯誤訊息： ' . $exception->getMessage();
    $objLineTool = new LineNotify();
    $objLineTool->doLineNotify($error_msg);

    $file = fopen("DailyLock.txt", "w");
    fwrite($file, "off");
    fclose($file);

}
