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
$sqlQuery = "CREATE TABLE user_table (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(50),
age INT UNSIGNED)";

# 執行 MySQL/MariaDB 指令
if ($connection->query($sqlQuery) === TRUE) {
echo "成功建立資料表。";
} else {
echo "執行失敗：" . $connection->error;
}

# 關閉 MySQL/MariaDB 連線
$connection->close();