<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Crea un utente di esempio
        $user = User::create([
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Crea template di esempio
        $this->createTemplates($user);
        
        // Crea documenti di esempio
        $this->createDocuments($user);
    }

    private function createTemplates(User $user): void
    {
        // Template per articoli
        $articleTemplate = DocumentTemplate::create([
            'name' => 'Template Articolo',
            'description' => 'Template per articoli di blog',
            'content' => '# {{title}}\n\n## Introduzione\n{{introduction}}\n\n## Contenuto principale\n{{main_content}}\n\n## Conclusione\n{{conclusion}}\n\n---\n*Articolo scritto da {{author}} il {{date}}*',
            'metadata' => [
                'type' => 'article',
                'required_fields' => ['title', 'introduction', 'main_content', 'conclusion'],
                'optional_fields' => ['author', 'date', 'tags']
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true,
                'word_count' => true
            ],
            'tags' => ['blog', 'article', 'content'],
            'category' => 'content',
            'is_active' => true,
            'created_by' => $user->id
        ]);

        // Template per report
        $reportTemplate = DocumentTemplate::create([
            'name' => 'Template Report',
            'description' => 'Template per report aziendali',
            'content' => '# REPORT: {{title}}\n\n## Executive Summary\n{{summary}}\n\n## Dati e Analisi\n{{data_analysis}}\n\n## Raccomandazioni\n{{recommendations}}\n\n## Conclusioni\n{{conclusions}}\n\n---\n*Report generato il {{date}} da {{author}}*',
            'metadata' => [
                'type' => 'report',
                'required_fields' => ['title', 'summary', 'data_analysis', 'recommendations'],
                'optional_fields' => ['conclusions', 'author', 'date']
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true,
                'word_count' => true,
                'page_numbers' => true
            ],
            'tags' => ['report', 'business', 'analysis'],
            'category' => 'business',
            'is_active' => true,
            'created_by' => $user->id
        ]);

        // Template per proposte
        $proposalTemplate = DocumentTemplate::create([
            'name' => 'Template Proposta',
            'description' => 'Template per proposte commerciali',
            'content' => '# PROPOSTA: {{title}}\n\n## Panoramica del Progetto\n{{overview}}\n\n## Soluzione Proposta\n{{solution}}\n\n## Timeline\n{{timeline}}\n\n## Budget\n{{budget}}\n\n## Prossimi Passi\n{{next_steps}}\n\n---\n*Proposta inviata il {{date}} da {{author}}*',
            'metadata' => [
                'type' => 'proposal',
                'required_fields' => ['title', 'overview', 'solution', 'budget'],
                'optional_fields' => ['timeline', 'next_steps', 'author', 'date']
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true,
                'word_count' => true,
                'confidential' => true
            ],
            'tags' => ['proposal', 'business', 'commercial'],
            'category' => 'business',
            'is_active' => true,
            'created_by' => $user->id
        ]);
    }

    private function createDocuments(User $user): void
    {
        $articleTemplate = DocumentTemplate::where('name', 'Template Articolo')->first();
        $reportTemplate = DocumentTemplate::where('name', 'Template Report')->first();

        // Crea documenti di esempio
        $article1 = Document::create([
            'title' => 'Introduzione a Laravel',
            'content' => '# Introduzione a Laravel\n\n## Introduzione\nLaravel è un framework PHP moderno e potente...\n\n## Contenuto principale\nLaravel offre molte funzionalità out-of-the-box...\n\n## Conclusione\nLaravel è la scelta ideale per sviluppatori PHP moderni.\n\n---\n*Articolo scritto da Mario Rossi il 2024-01-15*',
            'template_id' => $articleTemplate->id,
            'status' => 'published',
            'metadata' => [
                'type' => 'article',
                'author' => 'Mario Rossi',
                'date' => '2024-01-15',
                'tags' => ['laravel', 'php', 'framework']
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true,
                'word_count' => true
            ],
            'tags' => ['laravel', 'php', 'tutorial'],
            'author_id' => $user->id,
            'version' => 1
        ]);

        $article1->createVersion('Initial version');

        $report1 = Document::create([
            'title' => 'Report Q1 2024',
            'content' => '# REPORT: Report Q1 2024\n\n## Executive Summary\nIl primo trimestre del 2024 ha mostrato risultati positivi...\n\n## Dati e Analisi\nLe vendite sono aumentate del 15% rispetto al trimestre precedente...\n\n## Raccomandazioni\nRaccomandiamo di investire in marketing digitale...\n\n## Conclusioni\nIl Q1 2024 è stato un successo per l\'azienda.\n\n---\n*Report generato il 2024-04-01 da Mario Rossi*',
            'template_id' => $reportTemplate->id,
            'status' => 'published',
            'metadata' => [
                'type' => 'report',
                'author' => 'Mario Rossi',
                'date' => '2024-04-01',
                'quarter' => 'Q1',
                'year' => '2024'
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true,
                'word_count' => true,
                'page_numbers' => true
            ],
            'tags' => ['report', 'q1', '2024', 'business'],
            'author_id' => $user->id,
            'version' => 1
        ]);

        $report1->createVersion('Initial version');

        // Crea un documento senza template
        $document1 = Document::create([
            'title' => 'Note Personali',
            'content' => '# Le mie note personali\n\nQui scrivo le mie idee e riflessioni...',
            'template_id' => null,
            'status' => 'draft',
            'metadata' => [
                'type' => 'personal',
                'private' => true
            ],
            'settings' => [
                'format' => 'markdown',
                'auto_save' => true
            ],
            'tags' => ['personal', 'notes'],
            'author_id' => $user->id,
            'version' => 1
        ]);

        $document1->createVersion('Initial version');
    }
}
