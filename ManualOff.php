<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/28 下午 04:30.
 */

declare(strict_types=1);

$file = fopen("DailyLock.txt", "w");
fwrite($file, "off");
fclose($file);
echo "已解除鎖定";