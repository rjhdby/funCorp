<?php

namespace server;

class Server
{
    private $db;

    public function __construct(Db $db) {
        $this->db = $db;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParams(array $params): array {
        $time   = time();
        $params = $this->db->getParams($params);
        $out    = [];
        $upd    = [];
        foreach ($params as $row) {
            $delta   = $time - $row['setTime'];
            $current = $this->calculateCurrent($row['current'], $row['set'], $row['speed'], $delta);
            if ($current === $row['set'] && $row['set'] !== $row['current']) {
                $upd[ $row['name'] ] = $current;
            }
            $out[ $row['name'] ] = ['set' => $row['set'], 'value' => $current];
        }

        $this->setParams(json_encode($upd, true));
        $this->log('GET RESULT', json_encode($out, true));

        return $out;
    }

    private function calculateCurrent($old, $new, $speed, $delta): int {
        if ($speed === 0) {
            return $new;
        }
        switch ($old <=> $new) {
            case -1:
                $current = min($new, $old + $delta * $speed);
                break;
            case 1:
                $current = max($new, $old - $delta * $speed);
                break;
            default:
                $current = $new;
        }

        return $current;
    }

    /**
     * @param string $json
     * @return array
     */
    public function setParams(string $json): array {
        $raw = json_decode($json, true);
        if ($raw === false) {
            return ['FAIL'];
        }
        $tmp = [];
        foreach ($raw as $key => $value) {
            $this->db->setParam($key, $value);
            $tmp[] = $key;
        }
        $params = $this->db->getParams($tmp);
        $out    = [];
        foreach ($params as $row) {
            $out[ $row['name'] ] = ['set' => $row['set'], 'value' => $row['current']];
        }
        $this->log('SET RESULT', json_encode($out, true));

        return $out;
    }

    /**
     * @param string $text
     * @param string $type
     */
    private function log(string $type, string $text): void {
        fwrite(fopen('php://stdout', 'wb'), $type . ' : ' . $text . PHP_EOL);
    }
}