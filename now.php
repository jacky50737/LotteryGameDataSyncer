<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/30 下午 12:29.
 */

declare(strict_types=1);

echo "現在時間(UTC)：" . date("Y-m-d A h:i:s", time()) . "\n";

echo "現在時間(UTC+8)：" . date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n";

$fp = fsockopen("www.google.com", 80, $errno, $errstr, 30);
if (!$fp) {
    $net =  "ERROR: $errno - $errstr\n";
} else {
    $net =  "連線正常!\n";
    fclose($fp);
}

echo "網路狀況：".$net."\n";