<?php

namespace server;

use satellite\SatelliteParametersInterface;

class Db
{
    public $db;

    public function __construct(string $file) {
        $this->db = new \SQLite3($file);

        $this->db->exec('CREATE TABLE IF NOT EXISTS parameters(
                    `name` VARCHAR(50),
                    `speed` INT, 
                    `current` INT, 
                    `set` INT,
                    `setTime` INT)'
        );
    }

    /**
     * @param SatelliteParametersInterface $params
     */
    public function reset(SatelliteParametersInterface $params): void {
        $time = time();
        $this->db->exec('DELETE FROM parameters');
        $stmt = $this->db->prepare('INSERT INTO parameters (`name`,`speed`,`current`,`set`,`setTime`)
                          VALUES (:name, 10000, :current, :set, :time)');
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':current', $value);
        $stmt->bindParam(':set', $value);
        $stmt->bindParam(':time', $time);
        foreach ($params->getNames() as $name) {
            $value = $params->get($name);
            $stmt->execute();
        }
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParams(array $params): array {
        $result = $this->db->query('SELECT `name`, `current`, `set`, `speed`, `setTime` FROM parameters');
        $out    = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (\in_array($row['name'], $params, true)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param $name
     * @param $current
     */
    public function setParam($name, $current): void {
        $time = time();
        $stmt = $this->db->prepare('UPDATE parameters SET `set` = :current, `setTime` = :time WHERE `name` =:name');
        $stmt->bindParam(':current', $current);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
    }
}