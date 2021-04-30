<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

declare(strict_types=1);

$server = "localhost";         # MySQL/MariaDB 伺服器
$dbuser = "pjtvqdla_jacky50737";       # 使用者帳號
$dbpassword = "Aa174677178508123"; # 使用者密碼
$dbname = "pjtvqdla_PK10";    # 資料庫名稱

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

        $data = 'LotteryGameCode=2&IssueCount=&OpenDateDateTime=';

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
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
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

            foreach ($results as $result) {
                try {
                    $game = $result->NumberOfPeriod;
                    $gno = [];
                    foreach ($result->WinningNumbers as $key => $winningNumber) {
                        $gno[$key] = $winningNumber;
                    }

                    # 連接 MySQL/MariaDB 資料庫
                    $connection = new mysqli($server, $dbuser, $dbpassword, $dbname);

                    # 檢查連線是否成功
                    if ($connection->connect_error) {
                        $error_file = fopen("errorlog.txt", "a+");
                        fwrite($error_file, "DB連線失敗時發生錯誤，錯誤發生時間：" .
                            date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                            " 下載錯誤日期：" . $day . " 錯誤訊息：" . $connection->connect_error . "\n");
                        fclose($error_file);
                    }
                    $sqlQuery = "SELECT * FROM DATA WHERE game = " . $game . ";";

                    if ($dbresult = $connection->query($sqlQuery)) {

                        $process_file = fopen("processlog.txt", "a+");
                        fwrite($process_file, "驗證期數：" . $game . "=>成功!\t驗證時間：" .
                            date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) . "\n");
                        fclose($process_file);

                        # 取得結果
                        if (!$row = $dbresult->fetch_row()) {

                            $sqlQuery = "INSERT INTO DATA" .
                                "(game, no1, no2, no3, no4, no5, no6, no7, no8, no9, no10)" .
                                " VALUES (" . $game . ", " .
                                "$gno[0]" . ", " . "$gno[1]" . ", " . "$gno[2]" . ", " .
                                "$gno[3]" . ", " . "$gno[4]" . ", " . "$gno[5]" . ", " .
                                "$gno[6]" . ", " . "$gno[7]" . ", " . "$gno[8]" . ", " . "$gno[9]" . ")";

                            if ($connection->query($sqlQuery) === TRUE) {
                                $process_file = fopen("processlog.txt", "a+");
                                fwrite($process_file, "上傳期數：" . $game . "=>成功!\t上傳時間：" .
                                    date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) . "\n");
                                fclose($process_file);
                                echo $game . '期上傳成功!' . "\n";

                            } else {
                                $error_file = fopen("errorlog.txt", "a+");
                                fwrite($error_file, "上傳資料時發生錯誤，錯誤發生時間：" .
                                    date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                    " 發生錯誤遊戲期數：" . $game . " 錯誤訊息：" . $connection->connect_error . "\n");
                                fclose($error_file);
                            }

                        } else {
                            $process_file = fopen("processlog.txt", "a+");
                            fwrite($process_file, "驗證期數：" . $game . "=>已存在!\t 驗證時間：" .
                                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                                " 錯誤訊息：" . $connection->connect_error . "\n");
                            fclose($process_file);
                        }
                        # 釋放資源
                        $dbresult->close();
                    } else {
                        $error_file = fopen("errorlog.txt", "a+");
                        fwrite($error_file, "DB連線失敗時發生錯誤，錯誤發生時間：" .
                            date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                            " 下載錯誤日期：" . $day . " 錯誤訊息：" . $connection->connect_error . "\n");
                        fclose($error_file);
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
                        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
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
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
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
        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
        " 發生錯誤行：" . $exception->getLine() .
        " 錯誤訊息：" . $exception->getMessage() . "\n");
    fclose($error_file);
    $file = fopen("DailyLock.txt", "w");
    fwrite($file, "off");
    fclose($file);

}
