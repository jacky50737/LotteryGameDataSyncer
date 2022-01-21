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
require_once('class/TimeTool.class.php');

try {
    $objLineTool = new LineNotify();
    $objLineTool->doLineNotify("\n" . "歷史賽車資訊檢查...");
    $file = fopen("DailyLock.txt", "r");
    $lock = fgets($file);
    fclose($file);
    $timeTool = new timeTool();
    if ($lock == 'off') {

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

        if ($tomorrow < $today) {
            $day = $tomorrow;
        } else {
            $day = $today;
            sleep(1800);
        }

        $objGameTool = new getPK10GameData();

        $arrGameData = $objGameTool->getPK10Data("Date", $day);

        if (!empty($arrGameData)) {
            $total = count($arrGameData);
            $objLineTool->doLineNotify("\n" . "寫入開始時間..." . "\n" . "載入遊戲數據中..." . "\n" . "共{$total}筆遊戲賽事");
            $file = fopen("locktime.txt", "w");
            fwrite($file, $start_time . "\n");
            fclose($file);

            $done = 0;
            foreach ($arrGameData as $result) {
                try {
                    $game = $result[0];
                    $gno = $result[1];

                    $objDBTool = new DataBaseTool();
                    $done++;
                    if ($objDBTool->checkGame(strval($game)) == false) {
                        $isSuccess = $objDBTool->upLoadGame(strval($game), $gno);
                        $now_time = microtime(true);
                        $cost_time = $now_time - $start_time;
                        $maybeDone = intval($now_time + (($cost_time / $done) * $total)) + (8 * 60 * 60);
                        $excess_time = $timeTool->changeTimeType(intval(($cost_time / $done) * ($total-$done)));
                        $objLineTool = new LineNotify();

                        if ($isSuccess) {
                            $info_msg = "\n" . '[info]' .
                                "\n".'查詢日期：' . $day .
                                "\n".'上傳期數：' . $game .
                                "\n" . '=>成功!' . "\t" . '上傳時間： ' . "\n" .
                                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                "\n" . "還有[" . ($total - $done) . "]筆賽事，" . "\n" . "剩餘時間： {$excess_time}"."\n" . "預計完成時間：" . date("Y-m-d A h:i:s", $maybeDone);
                            $objLineTool->doLineNotify($info_msg);

                        } else {
                            $error_msg = "\n" . '[error]' . "\n" .
                                '上傳資料時發生錯誤，錯誤發生時間，' .
                                "\n" . '錯誤發生時間： ' . "\n" .
                                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                "\n" . ' 錯誤訊息： ' . $this->connection->connect_error .
                                "\n" . "還有[" . ($total - $done) . "]筆賽事，" . "\n" . "預計完成時間：" . date("Y-m-d A h:i:s", $maybeDone);
                            $objLineTool->doLineNotify($error_msg);

                        }

                    } else {
                        $objLineTool->doLineNotify("\n".'查詢日期：' . $day ."\n" . "本期[" . $game . "]已存在，前往下一期賽事"."\n" . "還有[" . ($total - $done) . "]筆賽事，");
                    }
                    $objDBTool->closeDB();
                    usleep(800000);

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

            $process_msg = "\n" . "日期：" . $day . "執行了：" . $timeTool->changeTimeType($time_total) . "\n";
            $objLineTool->doLineNotify($process_msg);

            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);
        }

    } else {
        $objLineTool->doLineNotify("\n" . "正在爬號中 !");
        $file = fopen("locktime.txt", "r");
        $locktime = fgets($file);
        fclose($file);
        $objLineTool->doLineNotify("\n" . "當前JOB已執行：" . $timeTool->changeTimeType(intval(microtime(true) - floatval($locktime))));

        if ((microtime(true) - floatval($locktime)) > 3600) {
            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);
            $objLineTool->doLineNotify("\n" . "已解除鎖定");

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
