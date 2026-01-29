<?php

declare(strict_types=1);

namespace App\Model\Service;

use RuntimeException;

final class ProcessService
{
    public function start(string $sessionName, string $command, string $workingDir, string $logFile): ?int
    {
        if (!is_dir($workingDir)) {
            throw new RuntimeException('Working directory does not exist.');
        }

        $safeCommand = sprintf('cd %s && %s >> %s 2>&1', escapeshellarg($workingDir), $command, escapeshellarg($logFile));
        if ($this->hasScreen()) {
            $full = sprintf('screen -dmS %s bash -lc %s', escapeshellarg($sessionName), escapeshellarg($safeCommand));
            exec($full);
            return null;
        }

        exec($safeCommand . ' &');
        return null;
    }

    public function stop(string $sessionName): void
    {
        if ($this->hasScreen()) {
            exec(sprintf('screen -S %s -X quit', escapeshellarg($sessionName)));
            return;
        }
    }

    public function sendCommand(string $sessionName, string $command): void
    {
        if ($this->hasScreen()) {
            $payload = addcslashes($command, "\\\"\\$");
            exec(sprintf('screen -S %s -X stuff "%s^M"', escapeshellarg($sessionName), $payload));
        }
    }

    private function hasScreen(): bool
    {
        $path = trim((string) shell_exec('command -v screen'));
        return $path !== '';
    }
}
