<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOutboxEventsJob;
use App\Services\OutboxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessOutboxCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'outbox:process 
                            {--limit=50 : Numero massimo di eventi da processare}
                            {--force : Forza il processing anche se ci sono eventi in processing}
                            {--cleanup : Pulisce eventi pubblicati vecchi dopo il processing}';

    /**
     * The console command description.
     */
    protected $description = 'Processa eventi pendenti nell\'outbox';

    /**
     * Execute the console command.
     */
    public function handle(OutboxService $outboxService): int
    {
        $this->info('Avvio processing eventi outbox...');

        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $cleanup = $this->option('cleanup');

        // Mostra statistiche iniziali
        $stats = $outboxService->getOutboxStats();
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending', $stats['pending']],
                ['Processing', $stats['processing']],
                ['Published', $stats['published']],
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
            $stuckEvents = OutboxEvent::where('status', 'processing')
                ->where('processing_started_at', '<', now()->subMinutes(5))
                ->count();

            if ($stuckEvents > 0) {
                $this->warn("Ci sono {$stuckEvents} eventi in processing da più di 5 minuti.");
                $this->warn("Usa --force per processarli comunque o risolvi manualmente.");
                return 1;
            }
        }

        // Processa gli eventi
        $this->info("Processing {$stats['pending']} eventi pendenti...");
        
        try {
            ProcessOutboxEventsJob::dispatch();
            $this->info('Job di processing avviato con successo.');
        } catch (\Exception $e) {
            $this->error('Errore nell\'avvio del job: ' . $e->getMessage());
            return 1;
        }

        // Attendi un momento e mostra le statistiche aggiornate
        $this->info('Attendo 5 secondi per il processing...');
        sleep(5);

        $updatedStats = $outboxService->getOutboxStats();
        $this->table(
            ['Status', 'Count', 'Change'],
            [
                ['Pending', $updatedStats['pending'], $updatedStats['pending'] - $stats['pending']],
                ['Processing', $updatedStats['processing'], $updatedStats['processing'] - $stats['processing']],
                ['Published', $updatedStats['published'], $updatedStats['published'] - $stats['published']],
                ['Failed', $updatedStats['failed'], $updatedStats['failed'] - $stats['failed']],
                ['Total', $updatedStats['total'], $updatedStats['total'] - $stats['total']]
            ]
        );

        // Cleanup se richiesto
        if ($cleanup) {
            $this->info('Avvio pulizia eventi pubblicati vecchi...');
            $deletedCount = $outboxService->cleanupPublishedEvents(7);
            $this->info("Rimossi {$deletedCount} eventi pubblicati più vecchi di 7 giorni.");
        }

        $this->info('Processing completato!');
        return 0;
    }
}
