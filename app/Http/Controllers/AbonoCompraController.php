<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Branch;
use App\Models\Payments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AbonoCompraController extends Controller
{
    public function printAbono($id_abono){

        $abono = Abono::withTrashed()
            ->with(['purchases' => fn($query) => $query->withTrashed()])
            ->findOrFail($id_abono);


        $sucursal = $this->warehouse($abono->purchases[0]->first());

        $isLocalhost = in_array(request()->getHost(), ['127.0.0.1', 'localhost']);


        $pdf = Pdf::loadView('abonos.abono-print-pdf', compact('abono', 'sucursal'))
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => !$isLocalhost,
            ]);
        return $pdf->stream('abono_' . $abono->id . '.pdf');
    }
    public function printPayment($id_payment)
    {
        $payment = Payments::withTrashed()
            ->with(['sales' => fn($query) => $query->withTrashed()])
            ->findOrFail($id_payment);


        $sucursal = $this->warehouse($payment->sales[0]->first());

        $isLocalhost = in_array(request()->getHost(), ['127.0.0.1', 'localhost']);


        $pdf = Pdf::loadView('payments.payment-print-pdf', compact('payment', 'sucursal'))
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => !$isLocalhost,
            ]);
        return $pdf->stream('abono_' . $payment->id . '.pdf');
    }
    public function warehouse($wareHauseId)
    {
        return Branch::find($wareHauseId);
    }
}
