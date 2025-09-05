<?php

namespace App\Http\Controllers;

use App\Services\Commands\CommandInterface;
use App\Services\Commands\WriteTextCommand;
use App\Services\Commands\DeleteTextCommand;
use App\Services\Commands\FormatTextCommand;
use App\Services\Commands\MacroCommand;
use App\Services\Invokers\CommandInvoker;
use App\Services\Receivers\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private CommandInvoker $invoker;
    private Document $document;
    
    public function __construct()
    {
        $this->invoker = new CommandInvoker();
        $this->document = new Document();
    }
    
    /**
     * Mostra la pagina principale con esempi di utilizzo del Command Pattern
     */
    public function index(): View
    {
        $examples = [
            'undo_redo' => $this->getUndoRedoExample(),
            'macro_commands' => $this->getMacroCommandsExample(),
            'command_queue' => $this->getCommandQueueExample(),
            'logging' => $this->getLoggingExample()
        ];
        
        return view('documents.index', compact('examples'));
    }
    
    /**
     * Esegue un comando
     */
    public function executeCommand(Request $request)
    {
        try {
            $commandType = $request->get('command_type');
            $text = $request->get('text', '');
            $position = (int) $request->get('position', 0);
            $length = (int) $request->get('length', 0);
            
            $command = $this->createCommand($commandType, $text, $position, $length);
            
            if ($command) {
                $this->invoker->executeCommand($command);
                
                return response()->json([
                    'success' => true,
                    'document_content' => $this->document->getContent(),
                    'can_undo' => $this->invoker->canUndo(),
                    'can_redo' => $this->invoker->canRedo(),
                    'command_description' => $command->getDescription()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Invalid command type'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Annulla l'ultimo comando
     */
    public function undo()
    {
        try {
            $success = $this->invoker->undo();
            
            return response()->json([
                'success' => $success,
                'document_content' => $this->document->getContent(),
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ripristina l'ultimo comando annullato
     */
    public function redo()
    {
        try {
            $success = $this->invoker->redo();
            
            return response()->json([
                'success' => $success,
                'document_content' => $this->document->getContent(),
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Esegue una macro di comandi
     */
    public function executeMacro(Request $request)
    {
        try {
            $commands = $request->get('commands', []);
            $macro = new MacroCommand();
            
            foreach ($commands as $cmd) {
                $command = $this->createCommand(
                    $cmd['type'],
                    $cmd['text'] ?? '',
                    $cmd['position'] ?? 0,
                    $cmd['length'] ?? 0
                );
                
                if ($command) {
                    $macro->addCommand($command);
                }
            }
            
            $this->invoker->executeCommand($macro);
            
            return response()->json([
                'success' => true,
                'document_content' => $this->document->getContent(),
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo(),
                'macro_description' => $macro->getDescription()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ottiene la cronologia dei comandi
     */
    public function getHistory()
    {
        try {
            $history = $this->invoker->getHistory();
            $historyDescriptions = array_map(function($command) {
                return $command->getDescription();
            }, $history);
            
            return response()->json([
                'success' => true,
                'history' => $historyDescriptions,
                'current_position' => $this->invoker->canUndo() ? count($history) - 1 : -1
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crea un comando specifico
     */
    private function createCommand(string $type, string $text, int $position, int $length): ?CommandInterface
    {
        return match ($type) {
            'write' => new WriteTextCommand($this->document, $text, $position),
            'delete' => new DeleteTextCommand($this->document, $position, $length),
            'format' => new FormatTextCommand($this->document, $position, $length, 'bold'),
            default => null
        };
    }
    
    /**
     * Ottiene esempi per la pagina principale
     */
    private function getUndoRedoExample(): array
    {
        return [
            'title' => 'Undo/Redo System',
            'description' => 'Sistema completo di annullamento e ripristino delle operazioni',
            'features' => [
                'Esecuzione comandi',
                'Annullamento operazioni',
                'Ripristino operazioni',
                'Cronologia completa'
            ]
        ];
    }
    
    private function getMacroCommandsExample(): array
    {
        return [
            'title' => 'Macro Commands',
            'description' => 'Sequenze di comandi eseguibili come un\'unica operazione',
            'features' => [
                'Combinazione di comandi',
                'Esecuzione batch',
                'Undo/Redo per macro',
                'Riutilizzo di sequenze'
            ]
        ];
    }
    
    private function getCommandQueueExample(): array
    {
        return [
            'title' => 'Command Queue',
            'description' => 'Coda di comandi per esecuzione differita',
            'features' => [
                'Esecuzione differita',
                'Priorità comandi',
                'Batch processing',
                'Gestione errori'
            ]
        ];
    }
    
    private function getLoggingExample(): array
    {
        return [
            'title' => 'Command Logging',
            'description' => 'Logging e auditing di tutte le operazioni',
            'features' => [
                'Tracciamento operazioni',
                'Audit trail',
                'Debug facilitato',
                'Analisi performance'
            ]
        ];
    }
}
