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
            $delta = $time - $row['setTime'];
            switch ($row['current'] <=> $row['set']) {
                case -1:
                    $current = min($row['set'], $row['current'] + $delta * $row['speed']);
                    break;
                case 1:
                    $current = max($row['set'], $row['current'] - $delta * $row['speed']);
                    break;
                default:
                    $current = $row['set'];
            }
            if ($current === $row['set'] && $row['set'] !== $row['current']) {
                $upd[$row['name']] = $current;
            }
            $out[ $row['name'] ] = ['set' => $row['set'], 'value' => $current];
        }

        $this->setParams(json_encode($upd, true));
        $this->log('GET RESULT', json_encode($out, true));

        return $out;
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
        $this->log('SET RAW', json_encode($raw, true));
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