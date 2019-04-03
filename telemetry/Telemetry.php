<?php

namespace telemetry;
class Telemetry implements TelemetryInterface
{
    public function error(string $message): void {
        $this->sendErrorMessage('error', $message);
    }

    public function info(string $message): void {
        $this->sendLogMessage('info', $message);
    }

    public function sendValues(array $values): void {
        $message = implode('&', array_map(
            function ($k, $v) { return $k . '=' . $v; },
            array_keys($values),
            $values
        ));
        $this->sendErrorMessage('values', $message);
    }

    public function log(string $message): void {
        $this->sendLogMessage('log', $message);
    }

    private function sendErrorMessage(string $type, string $message): void {
        $this->writeToStderr(json_encode([
                                             'type'      => $type,
                                             'timestamp' => time(),
                                             'message'   => $message
                                         ]) . PHP_EOL);
    }

    private function sendLogMessage(string $level, string $message): void {
        $this->writeToStdout(json_encode([
                                             'time'    => $this->formattedTime(),
                                             'level'   => $level,
                                             'message' => $message
                                         ]) . PHP_EOL);
    }

    private function formattedTime(): string {
        return (new \DateTime())->format("Y-m-d\TH:i:sT");
    }

    protected function writeToStdout(string $text): void {
        fwrite(STDOUT, $text);
    }

    protected function writeToStderr(string $text): void {
        fwrite(STDERR, $text);
    }
}