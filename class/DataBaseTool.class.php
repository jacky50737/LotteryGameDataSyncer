<?php

class DataBaseTool
{
    protected string $server = "localhost";         # MySQL/MariaDB 伺服器
    protected string $user = "pjtvqdla_jacky50737";       # 使用者帳號
    protected string $password = "Aa174677178508123"; # 使用者密碼
    protected string $dbname = "pjtvqdla_PK10";    # 資料庫名稱
    protected object $connection;

    public function __construct()
    {
        # 連接 MySQL/MariaDB 資料庫
        $this->connection = new mysqli($this->server, $this->user, $this->password, $this->dbname);

        # 檢查連線是否成功
        if ($this->connection->connect_error) {
            $error_msg = "\n" . '[error]' . "\n" .
                'DB連線失敗時發生錯誤，' .
                "\n" . '錯誤發生時間： ' . "\n" .
                date("Y-m-d A h:i:s", time() + (8 * 60 * 60)) .
                "\n" . ' 錯誤訊息： ' . $this->connection->connect_error;
            $objLineTool = new LineNotify();
            $objLineTool->doLineNotify($error_msg);
        }
    }

    /**
     * 驗證遊戲期數是否存在(true存在 false不存在)
     * @param string $game
     * @return bool
     */
    public function checkGame(string $game): bool
    {
        $sqlQuery = "SELECT * FROM DATA WHERE game = " . $game . ";";

        if ($this->connection->query($sqlQuery))
        {
            if(!is_null($this->connection->query($sqlQuery)->fetch_row()))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $game
     * @param array $gno
     * @return bool
     */
    public function upLoadGame(string $game,array $gno): bool
    {
        $sqlQuery = "INSERT INTO DATA" .
            "(game, no1, no2, no3, no4, no5, no6, no7, no8, no9, no10)" .
            " VALUES (" . $game . ", " .
            "$gno[0]" . ", " . "$gno[1]" . ", " . "$gno[2]" . ", " .
            "$gno[3]" . ", " . "$gno[4]" . ", " . "$gno[5]" . ", " .
            "$gno[6]" . ", " . "$gno[7]" . ", " . "$gno[8]" . ", " . "$gno[9]" . ")";

        if ($this->connection->query($sqlQuery) !== TRUE)
        {
            return false;
        }
        else
        {
            return true;
        }

    }

    public function closeDB(){
        # 釋放資源
        $this->connection->close();
    }

}
