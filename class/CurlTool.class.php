<?php

/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

class CurlTool
{
    /**
     * URL 路徑 HEADER 內容
     * @param string $url
     * @param array $header
     * @param array $payload
     * @return mixed
     */
    public function doPost(string $url,array $header,array $payload): mixed
    {

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $results = json_decode(curl_exec($ch));
//        $info = curl_getInfo($ch);
            curl_close($ch);

            return $results;
        } catch (Exception $exception) {
            $error_file = fopen("errorLog.txt", "a+");
            fwrite($error_file, "POST資料時發生錯誤，錯誤發生時間：" .
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                " 錯誤訊息：" . $exception->getMessage());
            fclose($error_file);
            return false;
        }

    }
}