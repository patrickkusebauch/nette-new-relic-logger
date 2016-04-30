<?php

namespace Kusebauch\NetteNewRelicLogger\Bridges\Tracy;

use \Tracy\ILogger;

/**
 * Class LoggerDecorator
 */
class LoggerDecorator implements ILogger
{

	/** @var ILogger */
	private $oldLogger;

	/** @var bool */
	public $directory = true; // workaround https://github.com/nette/tracy/pull/74

	/** @var callable($message, $priority):bool[] should the message be excluded from NewRelic? */
	public $excludeMessageFunc = [];

	/** @var callable($message, $priority):bool[] should the message be always logged to NewRelic? */
	public $includeMessageFunc = [];

	public function __construct(ILogger $oldLogger)
	{
		$this->oldLogger = $oldLogger;
	}

	/**
	 * @param string|array|Exception
	 * @param string
	 * @return string logged error filename
	 */
	function log($value, $priority = self::INFO)
	{
		$exceptionFile = $this->oldLogger->log($value, $priority);

		$forceLog = false;
		foreach ($this->includeMessageFunc as $func) {
			if($func($value, $priority) === true) {
				$forceLog = true;
				break;
			}
		}

		if($forceLog === false) {
			foreach ($this->excludeMessageFunc as $func) {
				if($func($value, $priority) === false) return $exceptionFile;
			}
		}

		if (is_array($value)) $value = implode(' ', $value);
		if($value instanceof \Exception) {
			newrelic_notice_error($value->getMessage(), $value);
			return $exceptionFile;
		}
		newrelic_notice_error($value);

		return $exceptionFile;
	}
}