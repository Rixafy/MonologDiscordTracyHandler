<?php

declare(strict_types=1);

namespace Rixafy\DiscordTracy;

use DiscordHandler\DiscordHandler;
use GuzzleHttp\Exception\RequestException;
use Monolog\LogRecord;
use Throwable;
use Tracy\Debugger;
use Tracy\Logger;

class DiscordTracyHandler extends DiscordHandler
{
	protected function write(LogRecord $record): void
	{
		try {
			parent::write($record);

			$logger = new Logger(Debugger::$logDirectory);
			$exception = $record->context['exception'] ?? null;
			if ($exception instanceof Throwable) {
				$file = $logger->getExceptionFile($record->context['exception'], strtolower($record->level->getName()));
				if (!file_exists($file)) {
					Debugger::getBlueScreen()->renderToFile($exception, $file);
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
		} catch (RequestException $e) {}
	}
}
