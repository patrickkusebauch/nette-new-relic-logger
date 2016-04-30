<?php

namespace Kusebauch\NetteNewRelicLogger;

use Nette\Application\Application;

class Bootstrap
{
	protected static $running = false;

	/**
	 * @param $callable callable($message, $priority):bool
	 * @param Application $application
	 */
	public static function addOnRequest($callable, Application $application)
	{
		if (!Utils::check()) return;
		$application->onRequest[] = $callable;
	}

	/**
	 * @param $callable callable($message, $priority):bool
	 * @param Application $application
	 */
	public static function addOnError($callable, Application $application)
	{
		if (!Utils::check()) return;
		$application->onError[] = $callable;
	}

	/**
	 * @param string $appName
	 * @param string|null $license
	 */
	public static function setup($appName = 'PHP Application', $license = NULL)
	{
		if (!Utils::check()) return;
		static::$running = true;
		if ($license === NULL) {
			newrelic_set_appname($appName);
		} else {
			newrelic_set_appname($appName, $license);
		}
	}

}
