<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer-main.
 * Date Time: 2022/1/21 下午 03:26.
 */

declare(strict_types=1);

class TimeTool
{
    public function changeTimeType($seconds): string
    {
        if ($seconds > 3600){
            $hours = intval($seconds/3600);
            $minutes = $seconds % 3600;
            $time = $hours.":".gmstrftime('%M分%S秒', $minutes);
        }else{
            $time = gmstrftime('%H小時%M分%S秒', $seconds);
        }
        return $time;
    }
}
