<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

declare(strict_types=1);

$url = 'https://videoracing.com/api/Issue/Search';

$data = 'LotteryGameCode=35&IssueCount=&OpenDateDateTime=';

$file = fopen("log.txt", "r");
$lastDay = fgets($file);
fclose($file);

$tomorrow = date('Y-m-d', strtotime($lastDay . "+1 days"));

$today = date("Y-m-d");

if ($tomorrow < $today) {
    $day = $tomorrow;
} else {
    $day = $today;
}
$data_all = $data . $day;

//echo $data_all;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded; charset=UTF-8'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_all);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$results = json_decode(curl_exec($ch));
$info = curl_getinfo($ch);
curl_close($ch);

if ($info['http_code'] == 200) {
    foreach ($results as $result) {
        $excel_url = 'https://script.google.com/macros/s/AKfycbybr5ubG0nKLIJ9mlBbWg9WVzltk5KtNrsxCs0GuQ/exec';
        $game = $result->NumberOfPeriod;
        $action = "checkData";
        $excel_url .= "?game=" . $game;
        foreach ($result->WinningNumbers as $key => $winningNumber) {
            $excel_url .= "&n" . ($key + 1) . "=" . $winningNumber;
        }
        $excel_url_checkData = $excel_url . "&action=" . $action;
        $is_have = json_decode(file_get_contents($excel_url_checkData));
        if (isset($is_have->dataFlag)) {
            $action = "uploadData";
            $excel_url_uploadData = $excel_url . "&action=" . $action;
            $is_upload = json_decode(file_get_contents($excel_url_uploadData));
            if(isset($is_upload->uploadtag)){
                echo $game.'期上傳成功!'."\n";
            }
        }
        usleep(10);
    }

    $file = fopen("log.txt", "w");
    fwrite($file, $day);
    fclose($file);
}