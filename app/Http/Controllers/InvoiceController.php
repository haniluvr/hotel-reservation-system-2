<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->middleware('auth');
    }

    /**
     * Download invoice for a reservation
     */
    public function download($reservationId)
    {
        $reservation = Reservation::with(['user', 'room.hotel', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        return $this->invoiceService->downloadInvoice($reservation);
    }

    /**
     * View invoice
     */
    public function show($reservationId)
    {
        $reservation = Reservation::with(['user', 'room.hotel', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        $data = [
            'reservation' => $reservation,
            'invoice_number' => 'INV-' . $reservation->reservation_number,
            'invoice_date' => now()->format('F d, Y'),
            'company' => [
                'name' => 'Belmont Hotel',
                'address' => '123 Main Street, Manila, Philippines',
                'phone' => '+63 2 1234 5678',
                'email' => 'info@belmonthotel.com',
            ],
        ];

        return view('invoices.show', $data);
    }
}

