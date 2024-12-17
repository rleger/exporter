<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calendars:import')->hourly();

$schedule->command('backup:clean')->daily()->at('01:00');
$schedule->command('backup:run')->daily()->at('01:30');
