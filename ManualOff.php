<?php
/**
 * Created by Jacky.
 * Project Name: LotteryGameDataSyncer.
 * Date Time: 2021/4/28 下午 04:30.
 */

declare(strict_types=1);

require_once('class/DataBaseTool.class.php');

$objDBTool = new DataBaseTool();

$file = fopen("DailyLock.txt", "w");
fwrite($file, "off");
fclose($file);

$objDBTool->setLife($fileName, 0);

echo "已解除鎖定";
