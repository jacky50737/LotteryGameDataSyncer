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

    public function doLineNotify(string $msg): string
    {

        $url = "https://notify-api.line.me/api/notify";

        $payload['message'] = $msg;

        $curl = new CurlTool();

        for ($i = 0; $i < 10; $i++) {
            $header = array('Authorization:Bearer ' . $this->token[$i]);
            $results = $curl->doPost($url, $header, $payload);
            if(!is_null($results->status)){
                if ($results->message == "ok" || $results->status == 200) {
                    return true;
                } 
            }
        }

        return false;
    }
}