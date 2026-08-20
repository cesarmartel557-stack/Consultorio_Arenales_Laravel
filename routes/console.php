<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:remind')->dailyAt('09:00');
