<?php

/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

class LineNotify
{
    private array $token = [
        "9geMEQ7E1EemVaZwvWxPNgFdKP4UbnkCBwhetO1WFpT",
        "PYtkGFJFyqPWYuAgA6dZHp8vXbWrPMKprgapNarwykG",
        "9HUFoPvXgt1nvh64mwalFot0ZcNYC9PgCQbsMgPsUIn",
        "ckwfUw1XWB41SOE8DDXKYxSZRZy3n8siG9npJX0pAG4",
    ];

    public function doLineNotify(string $msg,$is_Daily = false): string
    {

        $url = "https://notify-api.line.me/api/notify";

        $payload['message'] = $msg;

        $curl = new CurlTool();

        for ($i = 0; $i < 4; $i++) {
            $header = array('Authorization:Bearer ' . $this->token[$i]);
            for ($try = 0; $try < 10; $try++) {
                $results = $curl->doPost($url, $header, $payload, $is_Daily);
                if ($is_Daily){
                    return true;
                }
                if (!is_null($results->status) && !is_null($results->message)) {
                    if ($results->message == "ok" || $results->status == 200) {
                        $try = 10;
                        return true;
                    }
                } else {
                    var_dump($results);
                }
            }
        }

        return false;
    }
}