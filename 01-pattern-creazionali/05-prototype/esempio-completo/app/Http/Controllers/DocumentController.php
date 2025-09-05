<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportDocument;
use App\Models\ContractDocument;
use App\Models\InvoiceDocument;

class DocumentController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Prototype Pattern Demo',
            'data' => [
                'pattern_description' => 'Prototype clona oggetti esistenti invece di crearli da zero',
                'document_types' => ['report', 'contract', 'invoice']
            ]
        ]);
    }

    public function test()
    {
        $prototypes = [
            new ReportDocument(),
            new ContractDocument(),
            new InvoiceDocument()
        ];

        $clonedDocuments = [];
        foreach ($prototypes as $prototype) {
            $cloned = $prototype->clone();
            $clonedDocuments[] = $cloned->toArray();
        }

        return response()->json([
            'success' => true,
            'message' => 'Prototype Test Completed',
            'data' => [
                'documents_cloned' => count($clonedDocuments),
                'documents' => $clonedDocuments
            ]
        ]);
    }

    public function cloneDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:report,contract,invoice',
            'title' => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        $type = $request->input('type');
        $prototype = match($type) {
            'report' => new ReportDocument(),
            'contract' => new ContractDocument(),
            'invoice' => new InvoiceDocument(),
        };

        $cloned = $prototype->clone();
        
        if ($request->has('title')) {
            $cloned->title = $request->input('title');
        }
        if ($request->has('content')) {
            $cloned->content = $request->input('content');
        }

        return response()->json([
            'success' => true,
            'message' => 'Document cloned successfully',
            'data' => $cloned->toArray()
        ]);
    }

    public function show()
    {
        return view('prototype.example');
    }
}
