<?php

namespace Kusebauch\NetteNewRelicLogger;

use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\Request;

//TODO - I would like to have all of those as just namespaced function, but I wasn't able to make them work as callbacks
class Utils
{
	public static function check($strict = true)
	{
		$loaded = extension_loaded('newrelic');
		if (!$loaded && $strict) throw new \RuntimeException('Missing NewRelic extension.');
		return (bool) ini_get('newrelic.enabled') && $loaded;
	}

	public static function startsWith($haystack, $needle)
	{
		return (substr($haystack, 0, strlen($needle)) === $needle);
	}

	public static function noticeLoggerFilter($message, $priority)
	{
		if (is_array($message)) $message = implode(' ', $message);
		if ($message instanceof \Exception) $message = $message->getMessage();
		if(static::startsWith($message, "PHP Notice:")) return true;
		return false;
	}

	public static function strictLoggerFilter($message, $priority)
	{
		if (is_array($message)) $message = implode(' ', $message);
		if ($message instanceof \Exception) $message = $message->getMessage();
		if(static::startsWith($message, "PHP Strict standards:")) return true;
		return false;
	}

	public static function onAppError(Application $sender, \Exception $exception)
	{
		if ($exception instanceof BadRequestException) return; // skip
		newrelic_notice_error($exception->getMessage(), $exception);
	}

	public static function onAppRequest(Application $sender, Request $request)
	{
		if (PHP_SAPI === 'cli') {
			newrelic_name_transaction('$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1)));
			newrelic_background_job(true);
			return;
		}
		$params = $request->getParameters();
		newrelic_name_transaction($request->getPresenterName() . (isset($params['action']) ? ':' . $params['action'] : ''));
		if(strpos($request->getPresenterName(), 'Cron') !== false) newrelic_background_job(true);
	}
}
