<?php

namespace App\Services;

use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF invoice for a reservation
     */
    public function generateInvoice(Reservation $reservation): string
    {
        $reservation->load(['user', 'room', 'payment']);

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

        $pdf = Pdf::loadView('invoices.show', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
        
        return $pdf->output();
    }

    /**
     * Save invoice to storage and return path
     */
    public function saveInvoice(Reservation $reservation): string
    {
        $invoiceContent = $this->generateInvoice($reservation);
        $filename = 'invoices/INV-' . $reservation->reservation_number . '.pdf';
        
        Storage::disk('public')->put($filename, $invoiceContent);
        
        return $filename;
    }

    /**
     * Download invoice as PDF
     */
    public function downloadInvoice(Reservation $reservation)
    {
        $reservation->load(['user', 'room', 'payment']);

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

        $pdf = Pdf::loadView('invoices.show', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
        
        return $pdf->download('invoice-' . $reservation->reservation_number . '.pdf');
    }
}

