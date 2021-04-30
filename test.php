<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/30 上午 10:19.
 */

declare(strict_types=1);
//$all_round_time = 0;
//$laststep = 860;
//$doneStep = 0;
//$start_time = microtime(true);
//
//
//
//for($i=0;$i<5;$i++){
//    sleep(5);
//    $doneStep++;
//    $laststep = 860 - $doneStep;
//    $this_time = floatval(microtime(true));
//    $cost_time = floatval($this_time) - floatval($start_time);
//    $process_file = fopen("processlog.txt", "a+");
//    $pDate = intval((($this_time + ($laststep * ($cost_time / $doneStep))) / 1000));
//    echo $pDate."\n";
//    $pDoneTime = date("Y-m-d A h:i:s", $pDate);
//    fwrite($process_file, "預計完成時間：" . $pDoneTime . "\t 剩餘期數：" .
//        $laststep . "\n");
//    fclose($process_file);
for($i=0;$i<100;$i++)
{
    $fp = fsockopen("www.google.com", 80, $errno, $errstr, 30);
    if (!$fp) {
        echo "ERROR: $errno - $errstr\n";
    } else {
        echo "連線正常!\n";
        fclose($fp);
    }
}

