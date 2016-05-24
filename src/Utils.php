<?php

namespace Kusebauch\NetteNewRelicLogger;

//TODO - I would like to have all of those as just namespaced function, but I wasn't able to make them work as callbacks
class Utils
{
	public static function check($strict = true)
	{
		$loaded = extension_loaded('newrelic');
		if (!$loaded && $strict) throw new \RuntimeException('Missing NewRelic extension.');
		return (bool) ini_get('newrelic.enabled') && $loaded;
	}

	public static function noticeLoggerFilter($message, $priority)
	{
		if (is_array($message)) $message = implode(' ', $message);
		if ($message instanceof \Exception) $message = $message->getMessage();
		return strpos($message, 'PHP Notice:') === 0;
	}

	public static function strictLoggerFilter($message, $priority)
	{
		if (is_array($message)) $message = implode(' ', $message);
		if ($message instanceof \Exception) $message = $message->getMessage();
		return strpos($message, 'PHP Strict standards:') === 0;
	}

	public static function onAppError(\Application $sender, \Exception $exception)
	{
		if ($exception instanceof \BadRequestException) return; // skip
		newrelic_notice_error($exception->getMessage(), $exception);
	}

	public static function onAppRequest(\Application $sender, \PresenterRequest $request)
	{
		if (PHP_SAPI === 'cli') {
			newrelic_name_transaction('$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1)));
			newrelic_background_job(true);
			return;
		}

		$params = $request->getParams();
		$transactionName = $request->getPresenterName() . (array_key_exists('action', $params) ? ':' . $params['action'] : '');
		if(array_key_exists('do', $params) && strrpos($params['do'], '-') === false) $transactionName .= '-' . $params['do'];
		newrelic_name_transaction($transactionName);
		if(strpos($request->getPresenterName(), 'Cron') !== false) newrelic_background_job(true);
	}
}
