<?php

declare(strict_types=1);

namespace App\Model\Service;

final class ConsoleService
{
    public function tail(string $logFile, int $lines = 50): string
    {
        if (!file_exists($logFile)) {
            return '';
        }

        $content = file($logFile, FILE_IGNORE_NEW_LINES);
        if ($content === false) {
            return '';
        }

        $slice = array_slice($content, -$lines);
        return implode("\n", $slice);
    }

    public function append(string $logFile, string $line): void
    {
        file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
    }
}
