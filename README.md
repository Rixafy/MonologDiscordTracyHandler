# Monolog Discord handler with Tracy support
📝 Extended library `lefuturiste/monolog-discord-handler` with support of sending generated log files from Tracy (Nette Framework logging library) as an attachments to Discord channel

# Installation
```
composer require rixafy/monolog-discord-tracy-handler
```

# Usage

Check out [usage from original library](https://github.com/lefuturiste/monolog-discord-handler#2-usage) and replace `DiscordHandler\DiscordHandler` with `Rixafy\DiscordTracy\DiscordTracyHandler`

# How it works
If handler detects exception, error or info log, it will search log directory for newest `*.html` file, and if the file is not older than 5 seconds, handler will make a webhook request with attached file via curl library (it may change in the future to default guzzle http lib used in monolog)

Time check is because it's currently hard to tell if the last created html dump is correct one since tracy generates info/error/exception log only first time, and also if there was one error spamming every second, discord would be spammed with html files and probably would apply some api limits.

# Example configuration using contributte/monolog

```neon
extensions:
    monolog: Contributte\Monolog\DI\MonologExtension

monolog:
    channel:
        default:
            handlers:
                - Rixafy\DiscordTracy\DiscordTracyHandler('webhook_url', 'Name', 'SubName', 'DEBUG')
```
