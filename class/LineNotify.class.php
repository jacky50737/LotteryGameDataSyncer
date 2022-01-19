<?php

/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

include('class/CurlTool.class.php');

class LineNotify
{
    private string $token = "9geMEQ7E1EemVaZwvWxPNgFdKP4UbnkCBwhetO1WFpT";

    public function doLineNotify (string $msg): string
    {

        $url="https://notify-api.line.me/api/notify";

        $header = array('Authorization:Bearer ' . $this->token);

        $payload['message'] =  $msg;

        $curl = new CurlTool();
        $results = $curl->doPost($url,$header,$payload);

        if($results == false || $results->status != 200){
            return false;
        }

        return true;
    }
}