Larashed Monitoring Agent
==============

[![Build Status](https://travis-ci.com/larashed/agent.svg?branch=master)](https://travis-ci.com/larashed/agent)

This package hooks into your Laravel application and sends monitoring data to [larashed.com](https://larashed.com/)

---
* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Usage and Configuration](#usage-and-configuration)
* [Golang agent](#golang-server-agent)
* [Release history](https://github.com/larashed/agent/releases)

## Version Compatibility

 Laravel  | Larashed Agent
:---------|:----------
 5.2.x    | 5.2.x
 5.3.x    | 5.3.x
 5.4.x    | 5.4.x
 5.5.x    | 5.5.x
 5.6.x    | 5.6.x
 5.7.x.   | 5.7.x
 5.8.x.   | 5.8.x
 6.x      | 6.x

## Installation for Laravel 6.x

Using the `composer require` command:

```
composer require larashed/agent
```

## Installation for Laravel 5.x

Using the `composer require` command:

```
composer require larashed/agent:5.x.*
```

For Laravel 5.4 and below, add service provider

```
Larashed\Agent\AgentServiceProvider::class
```

## Usage and configuration

Edit your `.env` file and add the following:
```bash
LARASHED_APP_ID=<Your App ID>
LARASHED_APP_KEY=<Your App key>
```

If you'd like to disable monitoring for certain environments use:
```bash
LARASHED_IGNORED_ENVS=env1,env2
```

By default requests are tracked with your application's user's Id and name. If you'd like to disable it, use:
```
LARASHED_COLLECT_USER_DATA=false
```

### Running the agent

```
php artisan larashed:agent
```

We recommend using [Supervisord](http://supervisord.org/installing.html) to keep the agent daemon alive,
but you can use any software you prefer to keep it running.

```
[program:larashed-agent]
command=php /home/forge/app.com/artisan larashed:agent
autostart=true
autorestart=true
stdout_logfile=/home/forge/app.com/larashed-agent.log
```

### Tracking deployments

```
php artisan larashed:deploy
```

### Publishing config file

The default agent config should suffice for most users, however if you need to change it, you'll have to publish it first.
To publish `larashed.php` configuration file, run:
```
php artisan vendor:publish --tag=larashed
```

### Golang agent

The `larashed/agent` PHP composer package hooks into your application and collects the necessary metrics (HTTP requests, queue jobs, database queries, etc.), that data is then sent over IPC using a UNIX domain socket.
 
When you run `php artisan larashed:agent`, it downloads our [Golang agent](https://github.com/larashed/agent-go) which is responsible for a number of things:
- Starts the socket server
- Collects and sends server metrics
- Sends application metrics received from this package

## We'd love to hear your feedback!

If your have any questions, feature requests, issues or just want to say hi, don't hesitate to get in touch via <a href="mailto:hello@larashed.com">hello@larashed.com</a>.
For issues regarding this package, please [submit a new issue](https://github.com/larashed/agent/issues/new) in this repository. 

For general purpose questions and feedback that you’d like our community to also see, you can [create an issue](https://github.com/larashed/feedback/issues/new) in 
our public [`larashed/feedback`](https://github.com/larashed/feedback/issues) Github repository.
