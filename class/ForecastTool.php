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
     * @param $gno
     * @param $predict
     * @param $name
     * @return bool
     */
    public function checkForecastStatus($gno, $predict, $name)
    {
        $yards = intval(explode('_', $name)[1]);
        for ($i = 1; $i < $yards; $i++) {
            if ($gno["no{$i}"] == strval($predict)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $rowDataStatus
     * @param $status
     * @return array
     */
    public function processeForecastStatus($rowDataStatus, $status)
    {
        $status_C = "初始化";
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
                case 'MISS1':
                    $rowDataStatus = 'MISS2';
                    $status_C = '凹2';
                    break;
                case 'MISS2':
                    $rowDataStatus = 'DOWN';
                    $status_C = '倒';
                    break;
            }
        }
        return ['status' => $rowDataStatus, 'result' => $status_C];
    }
}