# CronSchedule.php
Ability to parse a cron schedule in PHP.

## Supported Format
https://en.wikipedia.org/wiki/Cron

CronSchedule.php supports minute (numeric 00-59), hour (numeric 00-23), day of month (1-31), month (numeric 1-12 or textual abbreviation [Jan, Feb, ...]), day of week (numeric 1-7 or textual abbreviation [Mon, Tue, ...]), and optionally, a year (1900 to 3000).

CronSchedule.php supports ranges on every parameter and intervals.

## Instantiation

To instantiate a new cron schedule:

```php
$schedule = new \Theriault\CronSchedule("* * * * *"); // run every minute
$schedule = new \Theriault\CronSchedule("* */2 * * *"); // run every other hour 
$schedule = new \Theriault\CronSchedule("* 2,4,6,8 * * *"); // run at 2AM, 4AM, 6AM, 8AM
$schedule = new \Theriault\CronSchedule("* */2 * * *"); // run every other hour (e.g. 12AM, 2AM, 4AM, 6AM, etc)
```

### Check if a timestamp falls within the schedule
```php
if ($schedule->checkTimestamp(time()) { // check if the current time is included in the schedule
  echo "Run this now!";
}
```

### Check if a DateTimeInterface object falls within the schedule
```php
if ($schedule->checkDateTime(new DateTime("today"))) { // also supports DateTimeInterface objects
  echo "Run this now!";
}
```
