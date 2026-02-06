<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['payer:id,name,username,email', 'payee:id,name,username,email'])
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('invoice_type', $type);
        }

        $invoices = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['invoices' => $invoices]);
        }

        return view('admin.invoices.index', compact('invoices'));
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Fatura zaten ödenmiş.'], 422);
            }
            return back()->withErrors(['error' => 'Fatura zaten ödenmiş.']);
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Fatura ödendi olarak işaretlendi.']);
        }

        return redirect('/admin/invoices')->with('success', 'Fatura ödendi olarak işaretlendi.');
    }

    public function markFailed(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Ödenmiş fatura iptal edilemez.'], 422);
            }
            return back()->withErrors(['error' => 'Ödenmiş fatura iptal edilemez.']);
        }

        $invoice->update([
            'status' => 'cancelled',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Fatura iptal edildi.']);
        }

        return redirect('/admin/invoices')->with('success', 'Fatura iptal edildi.');
    }
}
