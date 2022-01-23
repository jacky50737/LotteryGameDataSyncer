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

$fileName = basename(__FILE__, '.php');

try {
    $pid = rand();
    $objLineTool = new LineNotify();
    $objLineTool->doLineNotify("\n" . "歷史賽車資訊檢查...");
    $file = fopen("DailyLock.txt", "r");
    $lock = fgets($file);
    fclose($file);
    $timeTool = new timeTool();
    $objDBTool = new DataBaseTool();
    $life = $objDBTool->checkLife($fileName);

    if ($life > 700) {
        $file = fopen("DailyLock.txt", "w");
        fwrite($file, "off");
        fclose($file);
        $objLineTool->doLineNotify("\n" . "因過久未執行，已解除鎖定");
        $objDBTool->setLife($fileName, 0);

    } else {
        $objDBTool->setLife($fileName, $life + 45);
    }

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
            $lastGame = $objDBTool->logLastTimeProcess("getListTime", $fileName, "", $day);
            $objLineTool->doLineNotify(
                "\n" . "寫入開始時間..." .
                "\n" . "載入遊戲數據中..." .
                "\n" . "共{$total}筆遊戲賽事" .
                "\n" . "上次執行位置：{$lastGame}"
            );
            $file = fopen("locktime.txt", "w");
            fwrite($file, $start_time . "\n");
            fclose($file);
            $jumpTag = 0;
            $done = 0;
            foreach ($arrGameData as $result) {
                try {
                    $game = $result[0];
                    $gno = $result[1];

                    $done++;
                    if (intval($game) <= intval($lastGame)) {
                        $jumpTag = 1;
                        continue;
                    }

                    if ($jumpTag == 1) {
                        $objLineTool->doLineNotify("\n" . 'Pid：' . $pid . "\n" . 'Life：' . $life . "\n" . "正在跳躍至上次執行位置：{$lastGame}");
                        $jumpTag = 0;
                    }

                    $file = fopen("DailyLock.txt", "r");
                    $lock = fgets($file);
                    fclose($file);
                    if ($lock == 'off') {
                        exit($objLineTool->doLineNotify("\n" . 'Pid：' . $pid . "\n" . "差斷中止!"));
                    }

                    if ($objDBTool->checkGame(strval($game)) == false) {
                        $isSuccess = $objDBTool->upLoadGame(strval($game), $gno);
                        $now_time = microtime(true);
                        $cost_time = $now_time - $start_time;
                        $maybeDone = intval($now_time + (($cost_time / $done) * ($total - $done))) + (8 * 60 * 60);
                        $excess_time = $timeTool->changeTimeType(intval(($cost_time / $done) * ($total - $done)));
                        $objLineTool = new LineNotify();

                        if ($isSuccess) {
                            $objDBTool->logLastTimeProcess("save", $fileName, strval($game), $day); //紀錄執行成功進度
                            $life = $objDBTool->checkLife($fileName);
                            $objDBTool->setLife($fileName, $life - 1);
                            $info_msg =
                                "\n" . '[info]' .
                                "\n" . 'Pid：' . $pid .
                                "\n" . 'Life：' . $life .
                                "\n" . '查詢日期：' . $day .
                                "\n" . '上傳期數：' . $game .
                                "\n" . '=>成功!' . "\t" . '上傳時間： ' . "\n" .
                                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                "\n" . "還有[" . ($total - $done) . "]筆賽事，" . "\n" . "剩餘時間： {$excess_time}" . "\n" . "預計完成時間：" . date("Y-m-d A h:i:s", $maybeDone);
                            $objLineTool->doLineNotify($info_msg);

                        } else {
                            $error_msg =
                                "\n" . '[error]' . "\n" .
                                '上傳資料時發生錯誤，錯誤發生時間，' .
                                "\n" . '錯誤發生時間： ' . "\n" .
                                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                "\n" . ' 錯誤訊息： ' . $this->connection->connect_error .
                                "\n" . "還有[" . ($total - $done) . "]筆賽事，" . "\n" . "預計完成時間：" . date("Y-m-d A h:i:s", $maybeDone);
                            $objLineTool->doLineNotify($error_msg);

                        }
                        usleep(800000);
                    } else {
                        $life = $objDBTool->checkLife($fileName);
                        $objDBTool->setLife($fileName, $life - 1);
                        $objLineTool->doLineNotify("\n" . 'Pid：' . $pid . "\n" . 'Life：' . $life . "\n" . '查詢日期：' . $day . "\n" . "本期[" . $game . "]已存在，前往下一期賽事" . "\n" . "還有[" . ($total - $done) . "]筆賽事，");
                        usleep(100000);
                    }


                } catch (Exception $exception) {
                    $error_msg =
                        "\n" . '[error]' . "\n" .
                        '上傳資料時發生錯誤，錯誤發生時間，' .
                        "\n" . '錯誤發生時間： ' . "\n" .
                        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                        "\n" . ' 錯誤訊息： ' . $exception->getMessage();
                    $objLineTool = new LineNotify();
                    $objLineTool->doLineNotify($error_msg);
                }
            }

            $objLineTool->doLineNotify("\n讀寫完成...");

            if ($done == $total) {
                $file = fopen("log.txt", "w");
                fwrite($file, $day);
                fclose($file);

                $msg =
                    "\n" . "共{$total}筆" .
                    "\n" . "已完成{$done}筆" .
                    "\n" . "核對完成!";
                $objLineTool->doLineNotify($msg);
            } else {
                $lost = $total - $done;
                $msg =
                    "\n" . "共{$total}筆" .
                    "\n" . "已完成{$done}筆" .
                    "\n" . "遺漏{$lost}筆" .
                    "\n" . "即將重試...";
                $objLineTool->doLineNotify($msg);
            }

            $objDBTool->setLife($fileName, 0);

            $end_time = microtime(true);
            $time_total = $end_time - $start_time;

            $process_msg = "\n" . "日期：" . $day . "執行了：" . $timeTool->changeTimeType(intval($time_total)) . "\n";

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
        $objLineTool->doLineNotify("\n" . "Life：" . $life . "\n" . "當前JOB已執行：" . $timeTool->changeTimeType(intval(microtime(true) - floatval($locktime))));

        if ((microtime(true) - floatval($locktime)) > 3600) {
            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);
            $objLineTool->doLineNotify("\n" . "已解除鎖定");

        }
    }
    $objDBTool->closeDB();
    exit(0);
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
