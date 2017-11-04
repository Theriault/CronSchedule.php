<?php
namespace Theriault;

class CronSchedule
{

	private $minute = array();
	private $hour = array();
	private $dayOfMonth = array();
	private $month = array();
	private $dayOfWeek = array();
	private $year = array();
	private $hasYear = false;

	public function __construct(string $schedule)
	{
		$components = preg_split("/\\s+/", trim($schedule), 7);

		if ($components === false || count($components) < 5 || count($components) > 6)
		{
			throw new InvalidArgumentException("Invalid format supplied for schedule: {$schedule}");
		}

		$this->minute = $this->parseValue($components[0], 0, 59, false, false);
		$this->hour = $this->parseValue($components[1], 0, 23, false, false);
		$this->dayOfMonth = $this->parseValue($components[2], 1, 31, false, false);
		$this->month = $this->parseValue($components[3], 1, 12, true, false);
		$this->dayOfWeek = $this->parseValue($components[4], 1, 7, false, true);
		$this->hasYear = isset($components[5]);
		if ($this->hasYear)
		{
			$this->year = $this->parseValue($components[5], 1900, 3000, false, false);
		}
	}

	public function checkMonth(int $month)
	{
		return $month >= 1 && $month <= 12 && !empty($this->month[$month]);
	}

	public function checkMinute(int $minute)
	{
		return $minute >= 0 && $minute <= 59 && !empty($this->minute[$minute]);
	}

	public function checkHour(int $hour)
	{
		return $hour >= 0 && $hour <= 23 && !empty($this->hour[$hour]);
	}

	public function checkDayOfMonth(int $dayOfMonth)
	{
		return $dayOfMonth >= 1 && $dayOfMonth <= 32 && !empty($this->dayOfMonth[$dayOfMonth]);
	}

	public function checkDayOfWeek(int $dayOfWeek)
	{
		return $dayOfWeek >= 1 && $dayOfWeek <= 7 && !empty($this->dayOfWeek[$dayOfWeek]);
	}

	public function checkYear(int $year)
	{
		return $year !== null && $year >= 1900 && $year <= 3000 && (!$this->hasYear || !empty($this->year[$year]));
	}


	public function check(int $minute, int $hour, int $dayOfMonth, int $month, int $dayOfWeek, int $year = null)
	{
		return $this->checkMinute($minute)
			&& $this->checkHour($hour)
			&& $this->checkDayOfMonth($dayOfMonth)
			&& $this->checkMonth($month)
			&& $this->checkDayOfWeek($dayOfWeek)
			&& $this->checkYear($year);

	}

	public function checkTimestamp(int $time)
	{
		$date = date("i G j n N Y", $time);
		if ($date === false) throw new InvalidArgumentException;
		$date_components = array_map("intval", explode(" ", $date, 6));
		return $this->check(...$date_components);
	}

	public function checkDateTime(DateTimeInterface $dateTime)
	{
		$date = $dateTime->format("i G j n N Y");
		if ($date === false) throw new InvalidArgumentException;
		$date_components = array_map("intval", explode(" ", $date, 6));
		return $this->check(...$date_components);
	}

	private function parseValue(string $value, int $min, int $max, bool $allowMonths = false, bool $allowDays = false)
	{
		static $months = [
			"jan" => 1,
			"feb" => 2,
			"mar" => 3,
			"apr" => 4,
			"may" => 5,
			"jun" => 6,
			"jul" => 7,
			"aug" => 8,
			"sep" => 9,
			"oct" => 10,
			"nov" => 11,
			"dec" => 12,
		];

		static $days = [
			"mon" => 1,
			"tue" => 2,
			"wed" => 3,
			"thu" => 4,
			"fri" => 5,
			"sat" => 6,
			"sun" => 7,
		];

		$values = array();
		
		if ($allowMonths || $allowDays)
		{
			$value = strtolower($value);
		}

		$components = explode(",", $value, 100);
		foreach ($components as $component)
		{
			$subcomponents = explode("/", $component, 2);
			
			$value_range = $subcomponents[0];
			$interval = isset($subcomponents[1]) ? $subcomponents[1] : null;
			
			$value_range_components = explode("-", $value_range, 2);
			$base = $value_range_components[0];
			$range = isset($value_range_components[1]) ? $value_range_components[1] : null;

			// determine start
			if ($base === "*")
			{
				if ($range !== null)
				{
					throw new InvalidArgumentException("Asterisk cannot be followed by range: {$range}");
				}

				$start = $min;
			}
			else if (ctype_digit($base) && $base >= $min && $base <= $max)
			{
				$start = (int) $base;
			}
			else if ($allowMonths && isset($months[$base]))
			{
				$start = $months[$base];
			}
			else if ($allowDays && isset($days[$base]))
			{
				$start = $days[$base];
			}
			else
			{
				throw new InvalidArgumentException("Invalid value: {$base}");
			}

			// determine end
			if ($range === null)
			{
				$end = $base === "*" ? $max : $start;
			}
			else if (ctype_digit($range) && $range >= $min && $range <= $max)
			{
				$end = (int) $range;
			}
			else if ($allowMonths && isset($months[$range]))
			{
				$end = $months[$range];
			}
			else if ($allowDays && isset($days[$range]))
			{
				$end = $days[$range];
			}
			else
			{
				throw new InvalidArgumentException("Invalid range: {$range}");
			}

			// determine step
			if ($interval === null)
			{
				$step = 1;
			}
			else if (ctype_digit($interval) && $interval >= $min && $interval <= $max)
			{
				$step = $interval;
			}
			else
			{
				throw new InvalidArgumentException("Invalid interval: {$interval}");
			}

			// populate values
			for ($i = $start; $i <= $end; $i += $step)
			{
				$values[$i] = true;
			}
		}

		return $values;
	}

}
