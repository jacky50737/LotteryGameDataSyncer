<?php

/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/21 下午 06:01.
 */

class getPK10GameData
{

    protected array $url = ['https://api.api68.com/pks', 'https://api.apiose122.com/pks'];
    protected string $gameCode = '10037';

    /**
     * 雙模式取得賽車資料(最新 New,依日期 Date)
     * @param string $type
     * @param string $date
     * @return array|false
     */
    public function getPK10Data(string $type, string $date)
    {
        try {
            $game = "";
            $gno = [];
            $gnos = [];
            if($type == "New")
            {
                $uri= "/getLotteryPksInfo.do?lotCode=";
                $curl = new CurlTool();

                for($i=0;$i<5;$i++)
                {
                    $results = $curl->doGet($this->url[0].$uri.$this->gameCode);
                    if($results->errorCode == 0 ){
                        break;
                    }
                    usleep(100000);
                }

                if($i >= 5)
                {
                    throw new Exception("Post時間過久");
                }

                $game = $results->result->data->preDrawIssue;
                $gno = explode(',',$results->result->data->preDrawCode);

//                var_dump($game,$gno);
                return [$game,$gno];

            }
            elseif ($type == "Date")
            {
                $uri= "/getPksHistoryList.do?date=".$date."&lotCode=";
                $curl = new CurlTool();

                for($i=0;$i<5;$i++)
                {
                    $results = $curl->doGet($this->url[0].$uri.$this->gameCode);
                    if($results->errorCode == 0){
                        break;
                    }
                    usleep(100000);
                }

                if($i >= 5)
                {
                    throw new Exception("Post時間過久");
                }

                foreach ($results->result->data as $data)
                {
                    $game = $data->preDrawIssue;
                    $gno = explode(',',$data->preDrawCode);
                    $gnos[] = [$game,$gno];
                }
                return $gnos;
//                var_dump($results);
//                return false;
            }else{
                throw new Exception("未攜帶模式");
            }
//            var_dump($results);
        } catch (Exception $exception) {
            $error_msg = "\n" . '[error]' . "\n" .
                '下載遊戲資料時發生錯誤，' .
                "\n" . '錯誤發生模式： ' .($type == "New")?"最新":"歷史". "\n" .
                "\n" . '錯誤發生時間： ' . "\n" .
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                "\n" . ' 錯誤訊息： ' . $exception->getMessage();
            $objLineTool = new LineNotify();
            $objLineTool->doLineNotify($error_msg);
            return false;
        }
    }

}
