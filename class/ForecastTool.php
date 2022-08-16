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
        print("預測{$predict}最新：");
        print_r($gno);

        for ($i = 1; $i < $yards; $i++) {
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