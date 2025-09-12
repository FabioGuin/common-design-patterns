<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInboxEventsJob;
use App\Services\InboxService;
use Illuminate\Console\Command;

class ProcessInboxCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inbox:process 
                            {--limit=50 : Numero massimo di eventi da processare}
                            {--force : Forza il processing anche se ci sono eventi in processing}
                            {--cleanup : Pulisce eventi processati vecchi dopo il processing}';

    /**
     * The console command description.
     */
    protected $description = 'Processa eventi pendenti nell\'inbox';

    /**
     * Execute the console command.
     */
    public function handle(InboxService $inboxService): int
    {
        $this->info('Avvio processing eventi inbox...');

        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $cleanup = $this->option('cleanup');

        // Mostra statistiche iniziali
        $stats = $inboxService->getInboxStats();
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending', $stats['pending']],
                ['Processing', $stats['processing']],
                ['Processed', $stats['processed']],
                ['Failed', $stats['failed']],
                ['Total', $stats['total']]
            ]
        );

        if ($stats['pending'] === 0) {
            $this->info('Nessun evento pendente da processare.');
            return 0;
        }

        // Verifica eventi stuck se non è forzato
        if (!$force) {
            $stuckEvents = $inboxService->restoreStuckEvents();

            if ($stuckEvents > 0) {
                $this->warn("Ripristinati {$stuckEvents} eventi stuck.");
            }
        }

        // Processa gli eventi
        $this->info("Processing {$stats['pending']} eventi pendenti...");
        
        try {
            ProcessInboxEventsJob::dispatch();
            $this->info('Job di processing avviato con successo.');
        } catch (\Exception $e) {
            $this->error('Errore nell\'avvio del job: ' . $e->getMessage());
            return 1;
        }

        // Attendi un momento e mostra le statistiche aggiornate
        $this->info('Attendo 5 secondi per il processing...');
        sleep(5);

        $updatedStats = $inboxService->getInboxStats();
        $this->table(
            ['Status', 'Count', 'Change'],
            [
                ['Pending', $updatedStats['pending'], $updatedStats['pending'] - $stats['pending']],
                ['Processing', $updatedStats['processing'], $updatedStats['processing'] - $stats['processing']],
                ['Processed', $updatedStats['processed'], $updatedStats['processed'] - $stats['processed']],
                ['Failed', $updatedStats['failed'], $updatedStats['failed'] - $stats['failed']],
                ['Total', $updatedStats['total'], $updatedStats['total'] - $stats['total']]
            ]
        );

        // Cleanup se richiesto
        if ($cleanup) {
            $this->info('Avvio pulizia eventi processati vecchi...');
            $deletedCount = $inboxService->cleanupProcessedEvents(7);
            $this->info("Rimossi {$deletedCount} eventi processati più vecchi di 7 giorni.");
        }

        $this->info('Processing completato!');
        return 0;
    }
}
