<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateApiDoc::class,
        Commands\ClearViolationPdf::class,
        Commands\ClockInClockOutNotification::class,
        Commands\SendNewNotesCountToPropertyManager::class,
        Commands\NewViolationNotification::class,
        //Commands\SendDeliveryReportDataByExcel::class,
        Commands\AutomatedServiceReportDaliy::class,
        Commands\AutomatedServiceReportWeekly::class,
        Commands\AutomatedServiceReportMonthly::class,
        Commands\DaliyRecord::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // $schedule->command('NewViolationNotification:notification')
        //         //->dailyAt('12:00')
        //         ->everyMinute()
        //         ->emailOutputTo('akansh.pandya@galaxyweblinks.in');

        $schedule->command('command:ClearViolationPdf')->timezone('America/New_York')->monthlyOn(1, '06:00');

        $schedule->command('command:ClockInClockOutNotification')->everyThirtyMinutes();

        $schedule->command('command:SendNewNotesCountToPropertyManager')
            ->timezone('America/New_York')->dailyAt('06:00');

        $schedule->command('command:NewViolationNotification')->timezone('America/New_York')->dailyAt('06:00');
        
        //$schedule->command('command:SendDeliveryReportDataByExcel')->timezone('America/New_York')->dailyAt('06:00');

        $schedule->command('command:AutomatedServiceReportDaliy')->timezone('America/New_York')->dailyAt('06:00');

        $schedule->command('command:AutomatedServiceReportWeekly')
            ->timezone('America/New_York')->weekly()->mondays()->at('06:00');

        $schedule->command('command:AutomatedServiceReportMonthly')
            ->timezone('America/New_York')->monthlyOn(1, '06:00');
        
        $schedule->command('command:DaliyRecord')
            ->timezone('America/New_York')->dailyAt('06:00');
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
