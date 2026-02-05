<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pint {--test}', function () {
    $command = [base_path('vendor/bin/pint')];

    if ($this->option('test')) {
        $command[] = '--test';
    }

    $process = new Process($command);
    $process->setTimeout(null);
    $process->run(function ($type, $buffer) {
        $this->output->write($buffer);
    });

    return $process->isSuccessful() ? self::SUCCESS : self::FAILURE;
})->purpose('Run Laravel Pint');
