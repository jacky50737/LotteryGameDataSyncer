<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

declare(strict_types=1);

function pingTest(): bool
{
    $fp = fsockopen("script.google.com", 80, $errno, $errstr, 30);
    if ($fp) {
        echo "連線正常!";
        return true;
    }else{
        $error_file = fopen("errorlog.txt", "a+");
        fwrite($error_file, "網路發生錯誤，錯誤發生時間：" .
            date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
            " 錯誤代碼：" . $errno . " 錯誤訊息：" . $errstr . "\n");
        fclose($error_file);
        return false;
    }
}

try {
    $file = fopen("DailyLock.txt", "r");
    $lock = fgets($file);
    fclose($file);

    if ($lock == 'off') {

        $file = fopen("DailyLock.txt", "w");
        fwrite($file, "on");
        fclose($file);

        $start_time = microtime(true);
        echo "開始時間:" . $start_time . "\n";
        $url = 'https://videoracing.com/api/Issue/Search';

        $data = 'LotteryGameCode=2&IssueCount=10&OpenDateDateTime=';

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

        echo $data_all . "\n";
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded; charset=UTF-8'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_all);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $results = json_decode(curl_exec($ch));
            $info = curl_getinfo($ch);
            curl_close($ch);
            echo "下載遊戲數據中...\n";
        } catch (Exception $exception) {
            $error_file = fopen("errorlog.txt", "a+");
            fwrite($error_file, "下載遊戲資料時發生錯誤，錯誤發生時間：" .
                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                " 下載錯誤日期：" . $day . " 錯誤訊息：" . $exception->getMessage() . "\n");
            fclose($error_file);
        }
        echo "下載遊戲數據完成...\n";

        if ($info['http_code'] == 200) {
            echo "寫入開始時間...\n";
            $file = fopen("locktime.txt", "w");
            fwrite($file, $start_time . "\n");
            fclose($file);

            echo "載入遊戲數據...\n";

            $fp = fsockopen("script.google.com", 80, $errno, $errstr, 30);
            $resultsCount = count($results);
            $doneStep = 0;

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
                    $check_retry_tag = 0;

                    if ($check_retry_tag < 3) {
                        $ch2 = curl_init();
                        curl_setopt($ch2, CURLOPT_URL, $excel_url_checkData);
                        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch2, CURLOPT_TIMEOUT, 100);
                        $is_have_data = curl_exec($ch2);
                        curl_close($ch2);
                        if ($is_have_data != false) {
                            $check_retry_tag = 3;
                        } elseif ($check_retry_tag == 2 && $is_have_data == false) {
                            $error_file = fopen("errorlog.txt", "a+");
                            fwrite($error_file, "檢查資料時發生CURL錯誤，錯誤發生時間：" .
                                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                                " 下載錯誤日期：" . $day . "\n");
                            fclose($error_file);
                        } else {
                            $check_retry_tag++;
                        }
                        if(!pingTest()){
                            $check_retry_tag = 0;
                        }
                    }

                    for ($i = 0; $i < 3; $i++) {
                        if ($is_have_data != false) {
                            $is_have = json_decode($is_have_data);
                            $i = 3;
                        }
                    }

                    $process_file = fopen("processlog.txt", "a+");
                    fwrite($process_file, "驗證期數：" . $game . "=>成功!\t驗證時間：" .
                        date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
                    fclose($process_file);

                    if (isset($is_have->dataFlag)) {

                        $action = "uploadData";
                        $excel_url_uploadData = $excel_url . "&action=" . $action;
                        $upload_retry_tag = 0;

                        if ($upload_retry_tag < 3) {
                            $ch3 = curl_init();
                            curl_setopt($ch3, CURLOPT_URL, $excel_url_uploadData);
                            curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
                            curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch3, CURLOPT_TIMEOUT, 100);
                            $is_upload_data = curl_exec($ch3);
                            curl_close($ch3);
                            if ($is_upload_data != false) {
                                $is_upload = json_decode($is_upload_data);
                                $upload_retry_tag = 3;
                            } elseif ($upload_retry_tag == 2 && $is_upload_data == false) {
                                $error_file = fopen("errorlog.txt", "a+");
                                fwrite($error_file, "上傳資料時發生CURL錯誤，錯誤發生時間：" .
                                    date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                                    " 下載錯誤日期：" . $day . "\n");
                                fclose($error_file);
                            } else {
                                $upload_retry_tag++;
                            }
                            if(!pingTest()){
                                $check_retry_tag = 0;
                            }
                        }


                        if (isset($is_upload->uploadtag)) {
                            $process_file = fopen("processlog.txt", "a+");
                            fwrite($process_file, "上傳期數：" . $game . "=>成功!\t上傳時間：" .
                                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
                            fclose($process_file);
                            echo $game . '期上傳成功!' . "\n";
                        }
                    } else {
                        $process_file = fopen("processlog.txt", "a+");
                        fwrite($process_file, "驗證期數：" . $game . "=>已存在!\t 驗證時間：" .
                            date("Y-m-d A h:i:s", time() + 8 * 60 * 60) . "\n");
                        fclose($process_file);
                    }
                    $doneStep++;
                    $laststep = $resultsCount - $doneStep;
                    if ($laststep > 1) {
                        $this_time = floatval(microtime(true));
                        $cost_time = floatval($this_time) - floatval($start_time);
                        $process_file = fopen("processlog.txt", "a+");
                        $pDate = intval((($this_time + ($laststep * ($cost_time / $doneStep))) / 1000));
                        $pDoneTime = date("Y-m-d A h:i:s", ($pDate + (8 * 60 * 60)));
                        fwrite($process_file, "預計完成時間：" . $pDoneTime . "\t 剩餘期數：" .
                            $laststep . "\n");
                        fclose($process_file);
                    }

                    usleep(1000);

                } catch (Exception $exception) {

                    $error_file = fopen("errorlog.txt", "a+");
                    fwrite($error_file, "上傳資料時發生錯誤，錯誤發生時間：" .
                        date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                        " 發生錯誤遊戲期數：" . $game .
                        " 錯誤訊息：" . $exception->getMessage() . "\n");
                    fclose($error_file);

                }
            }
            echo "讀寫完成...\n";
            $end_time = microtime(true);

            $time_total = $end_time - $start_time;

            $file = fopen("log.txt", "w");
            fwrite($file, $day);
            fclose($file);

            $process_file = fopen("processlog.txt", "a+");
            fwrite($process_file, "日期：" . $day . "執行了：" . $time_total . "\n");
            fclose($process_file);

            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);

        } else {
            echo "下載失敗...\n";
            $error_file = fopen("errorlog.txt", "a+");
            fwrite($error_file, "下載資料時發生錯誤，錯誤發生時間：" .
                date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
                " 發生錯誤遊戲日期：" . $day .
                " 錯誤code：" . $info['http_code'] . "\n");
            fclose($error_file);
//            var_dump($info);
        }
    } else {
        echo "正在爬號中 !\n";
        $file = fopen("locktime.txt", "r");
        $locktime = fgets($file);
        fclose($file);
        echo "距目前JOB已執行：" . (floatval(microtime(true)) - floatval($locktime)) . "秒\n";

        if ((floatval(microtime(true)) - floatval($locktime)) > 7200) {
            echo "已解除鎖定\n";
            $file = fopen("DailyLock.txt", "w");
            fwrite($file, "off");
            fclose($file);

        }

    }

} catch (Exception $exception) {

    $error_file = fopen("errorlog.txt", "a+");
    fwrite($error_file, "發生未知錯誤，錯誤發生時間：" .
        date("Y-m-d A h:i:s", time() + 8 * 60 * 60) .
        " 發生錯誤行：" . $exception->getLine() .
        " 錯誤訊息：" . $exception->getMessage() . "\n");
    fclose($error_file);
    $file = fopen("DailyLock.txt", "w");
    fwrite($file, "off");
    fclose($file);

}
