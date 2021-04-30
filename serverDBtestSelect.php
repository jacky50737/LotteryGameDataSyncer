<?php
$server = "localhost";         # MySQL/MariaDB 伺服器
$dbuser = "pjtvqdla_jacky50737";       # 使用者帳號
$dbpassword = "Aa174677178508123"; # 使用者密碼
$dbname = "pjtvqdla_PK10";    # 資料庫名稱

# ------------查詢-------------

# 連接 MySQL/MariaDB 資料庫
$connection = new mysqli($server, $dbuser, $dbpassword, $dbname);

# 檢查連線是否成功
if ($connection->connect_error) {
    die("連線失敗：" . $connection->connect_error);
}

# MySQL/MariaDB 指令
$sqlQuery = "SELECT * FROM DATA WHERE game = '99998';";

# 執行 MySQL/MariaDB 指令
if ($result = $connection->query($sqlQuery)) {
    # 取得結果
    while ($row = $result->fetch_row()) {
        printf ("查詢成功 %s：%d\n", $row[0], $row[1]);
    }

    # 釋放資源
    $result->close();
} else {
    echo "執行失敗：" . $connection->error;
}

# 關閉 MySQL/MariaDB 連線
$connection->close();