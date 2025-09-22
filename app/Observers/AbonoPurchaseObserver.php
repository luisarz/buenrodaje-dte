<?php

namespace App\Observers;

use App\Models\AbonoPurchase;

class AbonoPurchaseObserver
{
    public function created(AbonoPurchase $abonoPurchase)
    {
        $abonoPurchase->abono?->actualizarSaldos();
    }

    public function updated(AbonoPurchase $abonoPurchase)
    {
        $abonoPurchase->abono?->actualizarSaldos();
    }

    public function deleted(AbonoPurchase $abonoPurchase)
    {
        $abonoPurchase->abono?->actualizarSaldos();
    }
}
