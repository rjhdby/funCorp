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

    public function sendTelemetry(string $message): void {
        $this->sendErrorMessage('values', $message);
    }

    private function sendErrorMessage(string $type, string $message): void {
        fwrite(STDERR, json_encode([
                                       'type'      => $type,
                                       'timestamp' => time(),
                                       'message'   => $message
                                   ]) . PHP_EOL);
    }

    private function sendLogMessage(string $level, string $message): void {
        fwrite(STDOUT, json_encode([
                                       'time'    => $this->formattedTime(),
                                       'level'   => $level,
                                       'message' => $message
                                   ]) . PHP_EOL);
    }

    private function formattedTime(): string {
        return (new \DateTime())->format("Y-m-d\TH:i:sT");
    }

    public function log(string $message): void {
        $this->sendLogMessage('log', $message);
    }
}