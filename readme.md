# nette-new-relic-logger

## Logging application requests
Logs all requests to Nette application and records application exceptions other than 404s. But does not record errors in the code. 

1) with running only when your NewRelic is 
 - installed
 - enabled
 - configured with `Bootstrap::setup()`
```php
$application = new Application();
\Kusebauch\NetteNewRelicLogger\Bootstrap::addOnError(['\Kusebauch\NetteNewRelicLogger\Utils', onAppError], $application);
\Kusebauch\NetteNewRelicLogger\Bootstrap::addOnRequest(['\Kusebauch\NetteNewRelicLogger\Utils', onAppRequest], $application);
```

2) try to run anyway (will be problematic, if you don't have NewRelic agent installed)
```php
$application = new Application();
$application->onError[] = ['\Kusebauch\NetteNewRelicLogger\Utils', onAppError];
$application->onRequest[] = ['\Kusebauch\NetteNewRelicLogger\Utils', onAppRequest];
```

## Logging application errors
Create a decorator over the current Tracy logger. Your application will keep logging the same way as it was up until now, but on top it will log to New Relic.

```php
$oldLogger = \Tracy\Debugger::getLogger();
$newLogger = \Kusebauch\NetteNewRelicLogger\Bridges\Tracy\LoggerDecorator($oldLogger);
$newLogger->excludeMessageFunc[] = ['\Kusebauch\NetteNewRelicLogger\Utils', 'noticeLoggerFilter'];
$newLogger->excludeMessageFunc[] = ['\Kusebauch\NetteNewRelicLogger\Utils', 'strictLoggerFilter'];
$newLogger->includeMessageFunc[] = function ($message, $priority) { return in_array($priority, [
    \Tracy\ILogger::CRITICAL, \Tracy\ILogger::ERROR, \Tracy\ILogger::EXCEPTION,
]) ? true : false; };
\Tracy\Debugger::setLogger($newLogger);
```