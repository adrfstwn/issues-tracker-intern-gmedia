<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\UpdateProject;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('update:project', function () {
    $startTime = microtime(true);

    $job = new UpdateProject();
    $job->handle();

    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    $this->info("Command completed in {$duration} seconds.");
})->purpose('Update project cache')->everyTwoMinutes();


