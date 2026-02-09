<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Enviar lembretes todo dia às 18h
        $schedule->command('agendamentos:lembrar')->dailyAt('18:00');
        
        // Gerar relatório diário às 23:30
        $schedule->command('relatorio:diario')->dailyAt('23:30');
        
        // Limpar cache toda semana
        $schedule->command('cache:clear')->weekly();
        
        // Backups automáticos
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('02:00');
        
        // Verificar agendamentos em atraso
        $schedule->command('agendamentos:verificar-atrasos')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}