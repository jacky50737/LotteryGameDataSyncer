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

$url = "https://script.google.com/macros/s/AKfycbybr5ubG0nKLIJ9mlBbWg9WVzltk5KtNrsxCs0GuQ/exec?game=20210428059&n1=1&n2=2&n3=3&n4=4&n5=5&n6=6&n7=7&n8=8&n9=9&n10=10&action=checkData";

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