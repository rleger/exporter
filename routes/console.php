<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calendars:import')->hourly();

Schedule->command('backup:clean')->daily()->at('01:00');
Schedule->command('backup:run')->daily()->at('01:30');
