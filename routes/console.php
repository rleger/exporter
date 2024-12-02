<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calendars:import')->twiceDaily();
