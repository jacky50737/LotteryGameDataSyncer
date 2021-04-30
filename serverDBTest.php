<?php
$server = "localhost";         # MySQL/MariaDB 伺服器
$dbuser = "pjtvqdla_jacky50737";       # 使用者帳號
$dbpassword = "Aa174677178508123"; # 使用者密碼
$dbname = "pjtvqdla_PK10";    # 資料庫名稱

# 連接 MySQL/MariaDB 資料庫
$connection = new mysqli($server, $dbuser, $dbpassword, $dbname);

# 檢查連線是否成功
if ($connection->connect_error) {
    die("連線失敗：" . $connection->connect_error);
}

# MySQL/MariaDB 指令
$sqlQuery = "INSERT INTO DATA (game, no1, no2, no3, no4, no5, no6, no7, no8, no9, no10) VALUES ('99999', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)";

# 執行 MySQL/MariaDB 指令
if ($connection->query($sqlQuery) === TRUE) {
    # 若有 AUTO_INCREMENT 的 ID 欄位，可直接取得此筆資料的 ID
    $last_id = $connection->insert_id;
    echo "成功新增資料，新資料 ID：" . $last_id;
} else {
    echo "執行失敗：" . $connection->error;
}

# 關閉 MySQL/MariaDB 連線
$connection->close();