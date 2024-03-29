<?php

class DataBaseTool
{
    protected string $server;    # MySQL/MariaDB 伺服器
    protected string $user;      # 使用者帳號
    protected string $password;  # 使用者密碼
    protected string $dbname;    # 資料庫名稱
    protected object $connection;

    /**
     * @var
     */
    private static $instance;

    /**
     * @return DataBaseTool
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $configs = include(__DIR__ . '/../config/database.php');
        $this->server = $configs['server'];
        $this->user = $configs['user'];
        $this->password = $configs['password'];
        $this->dbname = $configs['dbname'];

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

        if ($this->connection->query($sqlQuery)) {
            if (!is_null($this->connection->query($sqlQuery)->fetch_row())) {
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
    public function upLoadGame(string $game, array $gno): bool
    {
        $sqlQuery = "INSERT INTO DATA" .
            "(game, no1, no2, no3, no4, no5, no6, no7, no8, no9, no10)" .
            " VALUES (" . $game . ", " .
            "$gno[0]" . ", " . "$gno[1]" . ", " . "$gno[2]" . ", " .
            "$gno[3]" . ", " . "$gno[4]" . ", " . "$gno[5]" . ", " .
            "$gno[6]" . ", " . "$gno[7]" . ", " . "$gno[8]" . ", " . "$gno[9]" . ")";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $msg
     * @return bool
     */
    public function inQueueLineNotify(string $msg): bool
    {
        $sqlQuery = "INSERT INTO LINE_QUEUE" .
            "(MSG) VALUES ('" . $msg . "')";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array|false
     */
    public function getQueueLineNotify()
    {
        $sqlQuery = "SELECT ID, MSG FROM LINE_QUEUE ORDER BY ID ASC LIMIT 1;";
        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                if(empty($this->connection->query($sqlQuery)->fetch_assoc()['MSG'])){
                    return false;
                }
                return [
                    'id'=>$this->connection->query($sqlQuery)->fetch_assoc()['ID'],
                    'msg'=>$this->connection->query($sqlQuery)->fetch_assoc()['MSG']];
            }
        }
        return false;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function deQueueLineNotify(string $id): bool
    {
        $sqlQuery = "DELETE FROM LINE_QUEUE WHERE ID ='" .$id."';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @param string $process
     * @param string $game
     * @param string $date
     * @return bool|mixed
     */
    public function logLastTimeProcess(string $type, string $process, string $game, string $date)
    {

        if ($type == "save") {
            $sqlQuery = "UPDATE LOG SET game='" . $game . "', LAST_DATE='" . $date . "' WHERE process='" . $process . "'";
            for ($i = 0; $i < 5; $i++) {
                if ($this->connection->query($sqlQuery) == TRUE) {
                    return true;
                }
            }
        }

        if ($type == "getListTime") {
            $sqlQuery = "SELECT game FROM LOG WHERE process = '" . $process . "' AND LAST_DATE = '" . $date . "';";
            for ($i = 0; $i < 5; $i++) {
                if ($this->connection->query($sqlQuery) == TRUE) {
                    if(empty($this->connection->query($sqlQuery)->fetch_assoc()['game'])){
                        return "無";
                    }
                    return $this->connection->query($sqlQuery)->fetch_assoc()['game'];
                }
            }
        }

        return false;
    }


    public function getGameData($game)
    {

        $sqlQuery = "SELECT * FROM DATA WHERE game = '" . $game . "';";
        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                if(empty($this->connection->query($sqlQuery)->fetch_assoc())){
                    return "無";
                }
                return $this->connection->query($sqlQuery)->fetch_assoc();
            }
        }

        return false;
    }

    public function getForecastData()
    {
        $arraykN = ['name', 'c_name', 'game', 'predict', 'status','tag', 'shoot_times', 'total_times'];
        $data = [];
        $sqlQuery = "SELECT * FROM forecast ORDER BY TAG;";
        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                if (empty($this->connection->query($sqlQuery)->fetch_all())) {
                    return "無";
                }
                foreach ($this->connection->query($sqlQuery)->fetch_all() as $key => $item) {
                    foreach ($item as $num => $row) {
                        $data[$key][$arraykN[$num]] = $row;
                    }
                }
                return $data;
            }
        }

        return false;
    }

    public function getForecastTestData()
    {
        $arraykN = ['name', 'c_name', 'game', 'predict', 'status','tag', 'shoot_times', 'total_times', 'balance', 'fee','is_active'];
        $data = [];
        $sqlQuery = "SELECT * FROM forecast_test WHERE IS_ACTIVE = 'ON' ORDER BY TAG;";
        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                if (empty($this->connection->query($sqlQuery)->fetch_all())) {
                    return "無";
                }
                foreach ($this->connection->query($sqlQuery)->fetch_all() as $key => $item) {
                    foreach ($item as $num => $row) {
                        $data[$key][$arraykN[$num]] = $row;
                    }
                }
                return $data;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $game
     * @param $predict
     * @param $status
     * @return bool
     */
    public function updateForecastData($name, $game, $predict, $status)
    {
        $sqlQuery = "UPDATE FORECAST SET GAME = '" . $game . "', PREDICT = '" . $predict . "', STATUS = '" . $status . "' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $game
     * @param $predict
     * @param $status
     * @param $balance
     * @param $fee
     * @return bool
     */
    public function updateForecastTestData($name, $game, $predict, $status, $balance, $fee)
    {
        $sqlQuery = "UPDATE FORECAST_TEST SET GAME = '" . $game . "', PREDICT = '" . $predict . "', STATUS = '" . $status . "', BALANCE ='".$balance."', FEE ='".$fee."' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $totalTimes
     * @return bool
     */
    public function updateForecastTotalTimes($name, $totalTimes)
    {
        $sqlQuery = "UPDATE FORECAST SET TOTAL_TIMES = '" . $totalTimes . "' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $totalTimes
     * @return bool
     */
    public function updateForecastTestTotalTimes($name, $totalTimes)
    {
        $sqlQuery = "UPDATE FORECAST_TEST SET TOTAL_TIMES = '" . $totalTimes . "' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $shootTimes
     * @return bool
     */
    public function updateForecastShootTimes($name, $shootTimes)
    {
        $sqlQuery = "UPDATE FORECAST SET SHOOT_TIMES = '" . $shootTimes . "' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $shootTimes
     * @return bool
     */
    public function updateForecastTestShootTimes($name, $shootTimes)
    {
        $sqlQuery = "UPDATE FORECAST_TEST SET SHOOT_TIMES = '" . $shootTimes . "' WHERE NAME = '" . $name . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }

        return false;
    }


    public function checkLife(string $id): int
    {
        $sqlQuery = "SELECT IS_LIFE FROM LIFE WHERE ID = '" . $id . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return intval($this->connection->query($sqlQuery)->fetch_assoc()['IS_LIFE']);
            }
        }
        return 999;
    }

    public function setLife(string $id, int $life): int
    {
        $sqlQuery = "UPDATE LIFE SET IS_LIFE = '" . $life . "' WHERE LIFE.ID = '" . $id . "';";

        for ($i = 0; $i < 5; $i++) {
            if ($this->connection->query($sqlQuery) == TRUE) {
                return true;
            }
        }
        return false;
    }

    public function closeDB()
    {
        # 釋放資源
        $this->connection->close();
    }

}
