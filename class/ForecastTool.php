<?php

class ForecastTool
{
    /**
     * @var
     */
    private static $instance;

    /**
     * @return ForecastTool
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $name
     * @param array $gameData
     * @return int
     */
    public function forecastNextGame(string $name, array $gameData)
    {
        $forecastData = 0;

        switch ($name) {
            case 'YARDS_9_LEVELS_3':
            case 'YARDS_7_LEVELS_5':
            case 'YARDS_8_LEVELS_3':
            case 'YARDS_5_LEVELS_3':
            case 'YARDS_9_LEVELS_2':
            case 'YARDS_1_LEVELS_10':
                $total = intval($gameData['no1']) + intval($gameData['no10']);

                if ($total <= 10) {
                    $forecastData = $total;
                } else {
                    $forecastData = $total - 10;
                }
                break;
            default:
                break;
        }
        return $forecastData;
    }

    /**
     * @param $balance
     * @param $name
     * @param $status
     * @param $lastStatus
     * @param $fee
     * @return mixed
     */
    public function processBalance($balance, $name, $status, $lastStatus, $fee)
    {
        $betStrategy = [
            'YARDS_9_LEVELS_3'=>[
                'SHOOT'=>[
                    'DOWN' =>[18,1.2],
                    'SHOOT'=>[18,1.2],
                    'MISS1'=>[18,2.4],
                    'MISS2'=>[18,6],
                ],
                'MISS1'=>[
                    'DOWN' =>[306,0],
                    'SHOOT'=>[306,0],
                    'MISS1'=>[306,0],
                    'MISS2'=>[306,0],
                ],
                'MISS2'=>[
                    'DOWN' =>[4950,0],
                    'SHOOT'=>[4950,0],
                    'MISS1'=>[4950,0],
                    'MISS2'=>[4950,0],
                ],
                'DOWN'=>[
                    'DOWN' =>[18,0],
                    'SHOOT'=>[18,0],
                    'MISS1'=>[18,0],
                    'MISS2'=>[18,0],
                ]
            ],
            'YARDS_7_LEVELS_5'=>[
                'SHOOT'=>[
                    'DOWN' =>[14,5.2],
                    'SHOOT'=>[14,5.2],
                    'MISS1'=>[14,9.4],
                    'MISS2'=>[14,19.2],
                    'MISS3'=>[14,28],
                    'MISS4'=>[14,88],
                ],
                'MISS1'=>[
                    'DOWN' =>[63,0],
                    'SHOOT'=>[63,0],
                    'MISS1'=>[63,0],
                    'MISS2'=>[63,0],
                    'MISS3'=>[63,0],
                    'MISS4'=>[63,0],
                ],
                'MISS2'=>[
                    'DOWN' =>[259,0],
                    'SHOOT'=>[259,0],
                    'MISS1'=>[259,0],
                    'MISS2'=>[259,0],
                    'MISS3'=>[259,0],
                    'MISS4'=>[259,0],
                ],
                'MISS3'=>[
                    'DOWN' =>[980,0],
                    'SHOOT'=>[980,0],
                    'MISS1'=>[980,0],
                    'MISS2'=>[980,0],
                    'MISS3'=>[980,0],
                    'MISS4'=>[980,0],
                ],
                'MISS4'=>[
                    'DOWN' =>[3780,0],
                    'SHOOT'=>[3780,0],
                    'MISS1'=>[3780,0],
                    'MISS2'=>[3780,0],
                    'MISS3'=>[3780,0],
                    'MISS4'=>[3780,0],
                ],
                'DOWN'=>[
                    'DOWN'=>[14,0],
                    'SHOOT'=>[14,0],
                    'MISS1'=>[14,0],
                    'MISS2'=>[14,0],
                    'MISS3'=>[14,0],
                    'MISS4'=>[14,0],
                ],
            ]
        ];

        if($status == 'SHOOT'){
            $newBalance = $balance + $betStrategy[$name][$status][$lastStatus][1];
        }else{
            $newBalance = $balance - $betStrategy[$name][$status][$lastStatus][0];
        }
        $fee = $fee + ($betStrategy[$name][$status][$lastStatus][0] * 0.005);

        return [$newBalance, $fee];
    }

    /**
     * @param string $name
     * @return array
     */
    public function explodeForecast(string $name){
        $rows = explode("_", $name);
        return ['YARDS'=>$rows[1],'LEVELS'=>$rows[3]];
    }

    /**
     * @param $gno
     * @param $predict
     * @param $name
     * @return bool
     */
    public function checkForecastStatus($gno, $predict, $name)
    {
        $yards = intval(explode('_', $name)[1]);
        print("為{$yards}碼預測{$predict}最新：");
        print_r($gno);

        for ($i = 0; $i < $yards; $i++) {
            if(isset($gno[$i])){
                if (intval($gno[$i]) == intval($predict)) {
                    print("比對成功!");
                    return true;
                }
            }
        }
        print("比對失敗!");
        return false;
    }

    /**
     * @param $gno
     * @param $predict
     * @param $name
     * @return bool
     */
    public function checkForecastTestStatus($gno, $predict, $name)
    {
        $yards = intval(explode('_', $name)[1]);
        print("為{$yards}碼預測{$predict}最新：");
        print_r($gno);

        for ($i = 0; $i < $yards; $i++) {
            if(isset($gno["no".$i])){
                if (intval($gno["no".$i]) == intval($predict)) {
                    print("比對成功!");
                    return true;
                }
            }
        }
        print("比對失敗!");
        return false;
    }

    /**
     * @param $rowDataStatus
     * @param $status
     * @param $levels
     * @return array
     */
    public function processeForecastStatus($rowDataStatus, $status, $levels)
    {
        $miss = 0;
        if (str_contains($rowDataStatus, "MISS")) {
            $miss = explode("MISS", $rowDataStatus)[1];
//            var_dump($miss);
        }

        if ($status) {
            $rowDataStatus = 'SHOOT';
            $status_C = '中';
        } else {
            switch ($rowDataStatus) {
                case 'SHOOT':
                case 'DOWN':
                    $rowDataStatus = 'MISS1';
                    $status_C = '凹1';
                    break;
                default:
                    if ($miss == ($levels - 1)) {
                        $rowDataStatus = 'DOWN';
                        $status_C = '倒';
                    } else {
                        $tag = $miss + 1;
                        $rowDataStatus = "MISS{$tag}";
                        $status_C = "凹{$tag}";
                    }
            }
        }
        return ['status' => $rowDataStatus, 'result' => $status_C];
    }
}