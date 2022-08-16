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

    public function forecastNextGame(string $name, array $gameData){
        $forecastData = 0;
//        $yards = intval(explode('_',$name)[1]);

        switch ($name){
            case 'YARDS_9_LEVELS_3':
            case 'YARDS_7_LEVELS_5':
                $total = intval($gameData['no1']) + intval($gameData['no10']);

                if($total <= 10){
                    $forecastData = $total;
                }else{
                    $forecastData = $total - 10;
                }
                break;
            default:
                break;
        }
        return $forecastData;
    }
}