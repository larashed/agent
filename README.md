Larashed Monitoring Agent
==============

[![Build Status](https://travis-ci.com/larashed/agent.svg?branch=5.4)](https://travis-ci.com/larashed/agent)

This package hooks into your Laravel application and sends monitoring data to [larashed.com](https://larashed.com/)

---
* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Usage](#usage)
* [Agent Configuration](#agent-configuration)
* [Running the Daemon](#running-the-daemon)
* [Changelog](https://github.com/larashed/agent/releases)

## Version Compatibility

 Laravel  | Larashed Agent
:---------|:----------
 5.2.x    | 5.2.x
 5.3.x    | 5.3.x
 5.4.x    | 5.4.x
 5.5.x    | 5.5.x
 5.6.x    | 5.6.x
 5.7.x.   | 5.7.x

### Installation

By using the `composer require` command:

```
composer require "larashed/agent=~5.4"
```

Or by putting the following in your `composer.json` file:

```json
{
    "require": {
        "larashed/agent": "~5.4"
    }
}
```

And then running `composer install` from the terminal.

## Usage

Edit your `.env` file and add the following:
```bash
LARASHED_APP_ID=<Your App ID>
LARASHED_APP_KEY=<Your App key>
```

Run `php artisan larashed:daemon` command to start sending data.

## Agent Configuration

The agent config should suffice for most users, however if you need to change it, you'll have to publish it first.
To publish `larashed.php` configuration file, run:
```
php artisan vendor:publish --tag=larashed
```

## Running the Daemon

We recommend using supervisord to keep the agent daemon alive,
but you can use any software you prefer to keep it running.

```
[program:larashed-agent]
command=php /home/forge/app.com/artisan larashed:daemon
autostart=true
autorestart=true
stdout_logfile=/home/forge/app.com/larashed-agent.log
```
