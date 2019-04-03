<?php

namespace satellite;

class SatelliteParametersFactory
{
    public static function createFromJson(string $class, string $json): SatelliteParametersInterface {
        $raw = json_decode($json, true);
        if ($raw === null) {
            throw new \RuntimeException('Invalid parameters json');
        }
        /** @var SatelliteParametersInterface $params */
        $params = new $class();
        foreach ($raw as $name => $data) {
            if (!\is_string($name)) {
                throw new \RuntimeException("Invalid parameter name: $name");
            }

            switch ($data['type'] ?? 'int') {
                case 'int':
                    $param = self::intParam($data);
                    break;
                case 'string':
                    $param = self::stringParam($data);
                    break;
                default:
                    $param = null;
            }
            if ($param === null) {
                throw new \RuntimeException("Invalid parameter: $name");
            }
            $params->add($name, $param);
        }

        return $params;
    }

    private static function intParam($data): ?ParameterInterface {
        if (!isset($data['min'], $data['max'])) {
            throw new \RuntimeException('Invalid parameters');
        }
        if (\is_int($data['max'])
            && \is_bool($data['telemetry'])
            && \is_int($data['min'])) {
            return new IntParameter($data['min'], $data['max'], $data['telemetry'] ?? true);
        }

        return null;
    }

    /**
     * Stub method
     *
     * @param $data
     * @return null|ParameterInterface
     */
    private static function stringParam($data): ?ParameterInterface {
        return new StringParameter();
    }
}