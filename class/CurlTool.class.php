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
     * @return object
     */
    public function doPost(string $url,array $header,array $payload): object
    {

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

    }

    /**
     * @param string $url
     * @return mixed
     */
    public function doGet(string $url): mixed
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $results = json_decode(curl_exec($ch));
        curl_close($ch);

        return $results;

    }
}
