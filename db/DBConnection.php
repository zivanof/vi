<?php
namespace db;

/**
 * @author Velizar Ivanov <zivanof@gmail.com>
 */
class DBConnection {
    private $con;

    private $inTransaction = false;

    public function beginTransaction() {
        $this->getCon()->beginTransaction();
        $this->inTransaction = true;
    }

    private function getCon() {
        $this->init();

        return $this->con;
    }

    public function commit() {
        if ($this->isOpen()) {
            $this->con->commit();
            $this->inTransaction = false;
        }
    }

    public function rollback() {
        if ($this->isOpen() && $this->inTransaction) {
            $this->con->rollback();
            $this->inTransaction = false;
        }
    }

    public function close() {
        if ($this->isOpen()) {
            $this->con = null;
        }
    }

    private function init() {
        if (!$this->isOpen()) {
            $this->connectToMySQL();
        }
    }

    private function isOpen() {
        return null !== $this->con;
    }

    private function connectToMySQL() {
        $this->con = new \PDO("mysql:host={$this->serverName};dbname={$this->dbName}", $this->username, $this->passoword);
        $this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function query($query, $params = []) {
        $mode = \PDO::FETCH_ASSOC;

        if (empty($params)) {
            $stmt = $this->con->query($query);
            $stmt->setFetchMode($mode);

            return $stmt->fetchAll();
        } else {
            $stmt = $this->con->prepare($query);

            foreach ($params as $name => $value) {
                $stmt->bindParam($name, $value);
            }

            if ($stmt->execute($params)) {
                return $stmt->fetchAll();
            } else {
                return [];
            }
        }
    }

    public function executeQuery($query) {
        $this->init();

        return $this->con->query($query);
    }

    public function executeUpdate($query) {
        $this->init();

        return $this->con->exec($query);
    }

    public function prepare($query) {
        $this->init();

        return $this->con->prepare($query);
    }

    public function getLastInsertedId() {
        if ($this->isOpen()) {
            return $this->con->lastInsertId();
        }

        return null;
    }

}
