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
        date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
        " 錯誤訊息：" . $exception->getMessage());
    fclose($error_file);
}

if ($info['http_code'] == 200) {
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
                    " 錯誤訊息：" . $connection->connect_error . "\n");
                fclose($error_file);
            }
            $sqlQuery = "SELECT * FROM DATA WHERE game = " . $game . ";";

            if ($dbresult = $connection->query($sqlQuery)) {

                $process_file = fopen("processlog.txt", "a+");
                fwrite($process_file, "驗證期數：" . $game . "=>成功!\t驗證時間：" .
                    date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) . "\n");
                fclose($process_file);

                # 取得結果
                if ($row = $dbresult->fetch_row()) {

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
                    " 錯誤訊息：" . $connection->connect_error . "\n");
                fclose($error_file);
            }

        } catch (Exception $exception) {

            $error_file = fopen("errorlog.txt", "a+");
            fwrite($error_file, "上傳資料時發生錯誤，錯誤發生時間：" .
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                " 發生錯誤遊戲期數：" . $game .
                " 錯誤訊息：" . $exception->getMessage() . "\n");
            fclose($error_file);

        }
    }
}
