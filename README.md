# Monolog Discord handler with Tracy support
📝 Extended discord handler from `lefuturiste/monolog-discord-handler` with support of sending generated log files from Tracy (Nette Framework logging library) as an attachments

# Installation
```
composer require rixafy/monolog-discord-tracy-handler
```

# Usage

Check out https://github.com/lefuturiste/monolog-discord-handler#2-usage and replace `DiscordHandler\DiscordHandler` with `Rixafy\DiscordTracy\DiscordTracyHandler`

# How it works
If handler detects exception/error/log, it will search log directory for newest .html file, and if file is not older than 5 seconds, it will make a webhook request with attached file via curl library (it may change in the future to default guzzle http lib used in monolog)

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
