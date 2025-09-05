<?php

namespace App\Http\Controllers;

use App\Services\OrderApproval\ApprovalResult;
use App\Services\OrderApproval\CreditCheckHandler;
use App\Services\OrderApproval\DirectorApprovalHandler;
use App\Services\OrderApproval\InventoryCheckHandler;
use App\Services\OrderApproval\ManagerApprovalHandler;
use App\Services\OrderApproval\OrderRequest;
use App\Services\OrderApproval\ValidationHandler;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Mostra la pagina principale con esempi di utilizzo del Chain of Responsibility
     */
    public function index(): View
    {
        $examples = [
            'validation' => $this->getValidationExample(),
            'credit_check' => $this->getCreditCheckExample(),
            'inventory_check' => $this->getInventoryCheckExample(),
            'manager_approval' => $this->getManagerApprovalExample(),
            'director_approval' => $this->getDirectorApprovalExample(),
            'full_chain' => $this->getFullChainExample()
        ];
        
        return view('orders.index', compact('examples'));
    }
    
    /**
     * Processa un ordine attraverso la catena di responsabilità
     */
    public function processOrder(Request $request)
    {
        try {
            // Crea la richiesta ordine
            $orderRequest = new OrderRequest(
                orderId: $request->get('order_id', rand(1000, 9999)),
                customerName: $request->get('customer_name', 'John Doe'),
                customerEmail: $request->get('customer_email', 'john@example.com'),
                totalAmount: (float) $request->get('total_amount', 500),
                items: $request->get('items', [
                    ['name' => 'Product A', 'quantity' => 2, 'price' => 100, 'available' => true],
                    ['name' => 'Product B', 'quantity' => 1, 'price' => 300, 'available' => true]
                ]),
                customerId: $request->get('customer_id', 'CUST001'),
                customerCredit: (float) $request->get('customer_credit', 1000)
            );
            
            // Crea la catena di responsabilità
            $chain = $this->createApprovalChain();
            
            // Processa l'ordine
            $result = $chain->handle($orderRequest);
            
            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'No handler could process the order'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'order_id' => $orderRequest->orderId,
                'result' => $result->toArray(),
                'order_details' => [
                    'customer_name' => $orderRequest->customerName,
                    'total_amount' => $orderRequest->totalAmount,
                    'items_count' => count($orderRequest->items)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Testa un singolo gestore
     */
    public function testHandler(Request $request)
    {
        $handlerType = $request->get('handler_type', 'validation');
        
        try {
            // Crea la richiesta ordine
            $orderRequest = new OrderRequest(
                orderId: $request->get('order_id', rand(1000, 9999)),
                customerName: $request->get('customer_name', 'John Doe'),
                customerEmail: $request->get('customer_email', 'john@example.com'),
                totalAmount: (float) $request->get('total_amount', 500),
                items: $request->get('items', [
                    ['name' => 'Product A', 'quantity' => 2, 'price' => 100, 'available' => true]
                ]),
                customerId: $request->get('customer_id', 'CUST001'),
                customerCredit: (float) $request->get('customer_credit', 1000)
            );
            
            // Crea il gestore specifico
            $handler = $this->createHandler($handlerType);
            
            // Processa la richiesta
            $result = $handler->handle($orderRequest);
            
            return response()->json([
                'success' => true,
                'handler_type' => $handlerType,
                'result' => $result ? $result->toArray() : null,
                'order_details' => [
                    'customer_name' => $orderRequest->customerName,
                    'total_amount' => $orderRequest->totalAmount,
                    'items_count' => count($orderRequest->items)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crea la catena di approvazione completa
     */
    private function createApprovalChain()
    {
        $validation = new ValidationHandler();
        $creditCheck = new CreditCheckHandler();
        $inventoryCheck = new InventoryCheckHandler();
        $managerApproval = new ManagerApprovalHandler();
        $directorApproval = new DirectorApprovalHandler();
        
        // Crea la catena
        $validation->setNext($creditCheck)
                  ->setNext($inventoryCheck)
                  ->setNext($managerApproval)
                  ->setNext($directorApproval);
        
        return $validation;
    }
    
    /**
     * Crea un gestore specifico
     */
    private function createHandler(string $handlerType)
    {
        return match ($handlerType) {
            'validation' => new ValidationHandler(),
            'credit_check' => new CreditCheckHandler(),
            'inventory_check' => new InventoryCheckHandler(),
            'manager_approval' => new ManagerApprovalHandler(),
            'director_approval' => new DirectorApprovalHandler(),
            default => throw new \InvalidArgumentException("Unknown handler type: {$handlerType}")
        };
    }
    
    /**
     * Ottiene esempi per la pagina principale
     */
    private function getValidationExample(): array
    {
        return [
            'title' => 'Validation Handler',
            'description' => 'Valida i dati dell\'ordine prima di procedere',
            'features' => [
                'Controllo dati obbligatori',
                'Validazione formato email',
                'Controllo quantità e prezzi',
                'Validazione struttura item'
            ]
        ];
    }
    
    private function getCreditCheckExample(): array
    {
        return [
            'title' => 'Credit Check Handler',
            'description' => 'Verifica che il cliente abbia credito sufficiente',
            'features' => [
                'Controllo credito disponibile',
                'Confronto con importo ordine',
                'Calcolo deficit/surplus',
                'Blocco ordini insufficienti'
            ]
        ];
    }
    
    private function getInventoryCheckExample(): array
    {
        return [
            'title' => 'Inventory Check Handler',
            'description' => 'Verifica la disponibilità dei prodotti in magazzino',
            'features' => [
                'Controllo disponibilità item',
                'Validazione stock',
                'Identificazione prodotti mancanti',
                'Blocco ordini non disponibili'
            ]
        ];
    }
    
    private function getManagerApprovalExample(): array
    {
        return [
            'title' => 'Manager Approval Handler',
            'description' => 'Richiede approvazione manager per ordini sopra soglia',
            'features' => [
                'Soglia configurabile',
                'Approvazione automatica/semi-automatica',
                'Logging decisioni',
                'Metadati approvazione'
            ]
        ];
    }
    
    private function getDirectorApprovalExample(): array
    {
        return [
            'title' => 'Director Approval Handler',
            'description' => 'Richiede approvazione direttore per ordini molto grandi',
            'features' => [
                'Soglia alta configurabile',
                'Approvazione di alto livello',
                'Tracciamento decisioni',
                'Controllo finale'
            ]
        ];
    }
    
    private function getFullChainExample(): array
    {
        return [
            'title' => 'Full Chain',
            'description' => 'Catena completa di approvazione con tutti i gestori',
            'features' => [
                'Flusso completo end-to-end',
                'Gestione errori a cascata',
                'Logging dettagliato',
                'Flessibilità configurabile'
            ]
        ];
    }
}
