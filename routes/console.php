<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Schedule the purge processing command to run every minute
Schedule::command('purge:process')->everyMinute();
