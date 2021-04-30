<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/30 下午 12:29.
 */

declare(strict_types=1);

echo "現在時間(UTC)：" . date("Y-m-d A h:i:s", time()) . "\n";

echo "現在時間(UTC+8)：" . date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n";

$fp = fsockopen("script.google.com", 443, $errno, $errstr, 30);
if (!$fp) {
    $net =  "ERROR: $errno - $errstr\n";
} else {
    $net =  "連線正常!\n";
    fclose($fp);
}

echo "網路狀況：".$net."\n";

$url = "https://script.google.com/macros/s/AKfycbxWe91kaGcVApKgBuszP-_8XbsEn4IqMY0BDRnXzXuaXwST5ePAF7eit0HrS5eLlwtjjQ/exec&action=ping";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 100);

$results = json_decode(curl_exec($ch));
$info = curl_getinfo($ch);
curl_close($ch);

var_dump($results);