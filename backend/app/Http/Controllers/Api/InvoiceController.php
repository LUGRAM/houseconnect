<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use App\Models\{Invoice, Payment};
use App\Services\Invoices\InvoiceGeneratorService;

class InvoiceController extends Controller
{
    protected InvoiceGeneratorService $invoiceService;

    public function __construct(InvoiceGeneratorService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Liste des factures de l’utilisateur connecté.
     */
    public function index()
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->with('payment')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $invoices,
        ]);
    }

    /**
     * Détail d’une facture.
     */
    public function show($id)
    {
        $invoice = Invoice::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('payment')
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $invoice,
        ]);
    }

    /**
     * Génère une facture pour un paiement donné.
     */
    public function generateFromPayment($paymentId)
    {
        $payment = Payment::where('id', $paymentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $invoice = $this->invoiceService->generateFromPayment($payment);

        return response()->json([
            'status' => true,
            'message' => 'Facture générée avec succès.',
            'data' => $invoice,
        ]);
    }

    /**
     * Factures mensuelles (admin ou cron).
     */
    public function generateMonthly()
    {
        $count = $this->invoiceService->generateMonthlyInvoices();

        return response()->json([
            'status' => true,
            'message' => "{$count} factures mensuelles générées.",
        ]);
    }

    /**
     * Marquer les factures en retard.
     */
    public function markOverdue()
    {
        $count = $this->invoiceService->markOverdueInvoices();

        return response()->json([
            'status' => true,
            'message' => "{$count} factures mises à jour comme en retard.",
        ]);
    }
}
