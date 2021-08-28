<?php

declare(strict_types=1);

namespace Rixafy\DiscordTracy;

use DiscordHandler\DiscordHandler;
use Psr\Log\LogLevel;
use Throwable;
use Tracy\Debugger;
use Tracy\Logger;

class DiscordTracyHandler extends DiscordHandler
{
    private array $records = [];

    protected function write(array $record): void
    {
        parent::write($record);
        $this->records[] = $record;
    }

    public function __destruct()
    {
        $logger = new Logger(Debugger::$logDirectory);
        
        foreach ($this->records as $record) {
            $exception = $record['context']['exception'] ?? null;
            if ($exception instanceof Throwable) {
                $level = constant(LogLevel::class . '::' . strtoupper($record['level_name']));
                if ($level === null) {
                    return;
                }

                $file = $logger->getExceptionFile($record['context']['exception'], $level);
                if (!file_exists($file)) {
                    return;
                }
                
                $fileName = basename($file);
                foreach ($this->config->getWebHooks() as $webHook) {
                    $curl = curl_init($webHook);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 3);
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, [
                        'Content-Type: multipart/form-data'
                    ]);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, [
                        'file' => curl_file_create($file, 'text/html', $fileName)
                    ]);
                    curl_exec($curl);
                    curl_close($curl);
                }
            }
        }
    }
}
