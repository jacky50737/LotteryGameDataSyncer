<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

declare(strict_types=1);

$url = 'https://videoracing.com/api/Issue/Search';

$data = 'LotteryGameCode=2&IssueCount=1&OpenDateDateTime=';

try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded; charset=UTF-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $results = json_decode(curl_exec($ch));
    $info = curl_getinfo($ch);
    curl_close($ch);

} catch (Exception $exception) {
    $error_file = fopen("errorlog.txt", "a+");
    fwrite($error_file, "下載遊戲資料時發生錯誤，錯誤發生時間：" .
        date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
        " 錯誤訊息：" . $exception->getMessage());
    fclose($error_file);
}

if ($info['http_code'] == 200) {
    foreach ($results as $result) {
        try {
            $excel_url = 'https://script.google.com/macros/s/AKfycbybr5ubG0nKLIJ9mlBbWg9WVzltk5KtNrsxCs0GuQ/exec';
            $game = $result->NumberOfPeriod;
            $action = "checkData";
            $excel_url .= "?game=" . $game;
            foreach ($result->WinningNumbers as $key => $winningNumber) {
                $excel_url .= "&n" . ($key + 1) . "=" . $winningNumber;
            }
            $excel_url_checkData = $excel_url . "&action=" . $action;

            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_URL, $excel_url_checkData);
            curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 20);
            $is_have_data = curl_exec($ch2);
            curl_close($ch2);

            for ($i = 0; $i < 3; $i++) {
                if ($is_have_data != false) {
                    $is_have = json_decode($is_have_data);
                    $i = 3;
                }
            }

            $process_file = fopen("processFastlog.txt", "a+");
            fwrite($process_file, "驗證期數：" . $game . "=>成功! 嘗試次數：".$i."驗證時間：" .
                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
            fclose($process_file);

            $process_file = fopen("processFastlog.txt", "a+");
            fwrite($process_file, "開始上傳期數：" . $game . "，開始時間：" .
                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
            fclose($process_file);
            if (isset($is_have->dataFlag)) {
                $action = "uploadData";
                $excel_url_uploadData = $excel_url . "&action=" . $action;

                $ch3 = curl_init();
                curl_setopt($ch3, CURLOPT_URL, $excel_url_uploadData);
                curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch3, CURLOPT_TIMEOUT, 20);
                $is_upload_data = curl_exec($ch3);
                curl_close($ch3);

                for ($j = 0; $j < 3; $j++) {
                    if ($is_upload_data != false) {
                        $is_upload = json_decode($is_upload_data);
                        $j = 3;
                    }
                }
                $process_file = fopen("processFastlog.txt", "a+");
                fwrite($process_file, "上傳期數結束：" . $game . " 嘗試次數：".$j."，結束時間：" .
                    date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
                fclose($process_file);
                if (isset($is_upload->uploadtag)) {
                    $process_file = fopen("processFastlog.txt", "a+");
                    fwrite($process_file, "上傳期數：" . $game . "=>成功!，上傳時間：" .
                        date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
                    fclose($process_file);
                    echo $game . '期上傳成功!' . "\n";
                }
            }
            usleep(1);
        } catch (Exception $exception) {
            $error_file = fopen("errorFastlog.txt", "a+");
            fwrite($error_file, "上傳資料時發生錯誤，錯誤發生時間：" .
                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                " 發生錯誤遊戲期數：" . $game .
                " 錯誤訊息：" . $exception->getMessage());
            fclose($error_file);
        }
    }
}
