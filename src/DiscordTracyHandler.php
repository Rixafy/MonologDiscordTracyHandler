<?php

declare(strict_types=1);

namespace Rixafy\DiscordTracy;

use DiscordHandler\DiscordHandler;
use Tracy\Debugger;

class DiscordTracyHandler extends DiscordHandler
{
    private bool $sendFile = false;

    protected function write(array $record): void
    {
        parent::write($record);
        $this->sendFile = true;
    }

    public function __destruct()
    {
        if ($this->sendFile) {
            $mask = Debugger::$logDirectory . '/*.html';

            $files = glob($mask);
            usort($files, function ($x, $y) {
                return filemtime($y) <=> filemtime($x);
            });

            $newestFile = $files[0] ?? null;
            if ($newestFile !== null) {
                if (filemtime($newestFile) + 5 < time()) {
                    return;
                }

                foreach ($this->config->getWebHooks() as $webHook) {
                    $json_data = [
                        'file' => curl_file_create($newestFile, 'text/html', 'exception.html')
                    ];

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
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);

                    curl_exec($curl);
                    curl_close($curl);
                }
            }
        }
    }
}
