<?php namespace tomjamescn\dbutils4php;


class DbUtils{


    public $pdo;
    public $debug = false;

    public function __construct($host, $dbName, $userName, $userPwd, $debug)
    {
        $this->debug = $debug;

        try {
            $this->pdo = new \PDO("mysql:host={$host};dbname={$dbName}", $userName, $userPwd, array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        }catch (\PDOException $e) {
            throw new \Exception('new PDO failed! ' . $e->getMessage());
        }
    }

    public function log($msg) {
        if ($this->debug) {
            echo "{$msg}\n";
        }

    }


    public function insertWithSql($sql) {
        $this->log($sql);
        $ret = $this->pdo->exec($sql);
        if ($ret === false) {
            $err = $this->pdo->errorInfo();
            if ($err[0] === '00000' || $err[0] === '01000') {
                throw new \Exception('Ö´ĞĞsql´íÎó '.$sql.' '.json_encode($err).'');
            }
        }
    }


    public function insertWithStmt($table, $data) {
        $pdo = $this->pdo;
        $columns = [];
        $placeHolders = [];
        $executeInputParameters = [];
        foreach ($data as $column => $value) {
            $columns[] = "`{$column}`";
            $placeHolders[] = '?';
            $executeInputParameters[] = $value;
        }
        $columnsStr = implode(',', $columns);
        $placeHoldersStr = implode(',', $placeHolders);
        $sql = "insert into `{$table}` ($columnsStr) values ($placeHoldersStr)";
        $this->log($sql);

        $stmt = $pdo->prepare($sql);
        $ret = $stmt->execute($executeInputParameters);
        if ($ret == false) {
            throw new \Exception("Ö´ĞĞsql³ö´í,insertWithStmt");
        }
    }


    function checkExist($table, $whereSql) {

        $pdo = $this->pdo;
        $sql = "select count(*) as num from {$table} where {$whereSql}";
        $this->log($sql);
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($rows) == 1 && $rows[0]['num'] >= 1) {
            return true;
        }else {
            return false;
        }
    }


    function updateWithSql($sql) {
        $this->log($sql);
        $pdo = $this->pdo;
        $ret = $pdo->exec($sql);
        if ($ret === false) {
            $err = $pdo->errorInfo();
            throw new \Exception('Ö´ĞĞsql´íÎó '.$sql.' '.json_encode($err).'');
        }
    }



    function updateWithStmt($table, $data, $whereSql) {
        $pdo = $this->pdo;
        $sets = [];
        $executeInputParameters = [];
        foreach ($data as $column => $value) {
            $sets[] = "`{$column}` = ?";
            $executeInputParameters[] = $value;
        }
        $setsStr = implode(',', $sets);
        $sql = "update `{$table}` set {$setsStr} where {$whereSql}";
        echo "$sql \n";
        $stmt = $pdo->prepare($sql);
        $ret = $stmt->execute($executeInputParameters);
        if ($ret == false) {
            throw new \Exception("Ö´ĞĞsql³ö´í,updateWithStmt");
        }
    }



    function insertOrUpdate($table, $data, $whereSql) {
        $exist = $this->checkExist($table, $whereSql);
        if ($exist) {
            //update
            $this->updateWithStmt($table, $data, $whereSql);
        }else {
            //insert
            $this->insertWithStmt($table, $data);
        }
    }




}