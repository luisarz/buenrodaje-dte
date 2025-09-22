<?php

namespace App\Filament\Resources\Payments\Pages;

use Log;
use App\Filament\Resources\Payments\PaymentsResource;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreatePayments extends CreateRecord
{
    protected static string $resource = PaymentsResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Nuevo Abono a cuenta por pagar';
    }

    protected function afterCreate(): void
    {
        $payment = $this->record;
        $data = $this->data;

        if (!empty($data['sales']) && is_array($data['sales'])) {
            $syncData = [];

            foreach ($data['sales'] as $saleData) {
                if (!empty($saleData['sale_id']) && !empty($saleData['amount_payment'])) {
                    $sale = Sale::find($saleData['sale_id']);

                    $totalAbonosAntes = $sale->payments()
                        ->where('payment_id', '!=', $payment->id)
                        ->sum('payment_sale.amount_payment');

                    $saldoAnterior = max(0, $sale->sale_total - $totalAbonosAntes);

                    $totalAbonosActual = $totalAbonosAntes + $saleData['amount_payment'];
                    $saldoActual = max(0, $sale->sale_total - $totalAbonosActual);

                    $syncData[$sale->id] = [
                        'amount_payment' => $saleData['amount_payment'],
                        'amount_before' => $saldoAnterior,
                        'actual_amount' => $saldoActual,
                    ];
                }
            }

            if (!empty($syncData)) {
                $payment->sales()->sync($syncData);
                $payment->load('sales');
                $payment->actualizarSaldos();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Abono: mutateFormDataBeforeCreate', [
            'data' => $data,
        ]);
        return parent::mutateFormDataBeforeCreate($data);
    }

    public function beforeCreate(): void
    {
        $data = $this->data;
        $sales = $data['sales'] ?? [];

        $totalAbonos = collect($sales)->sum('amount_payment');

        if (empty($sales)) {
            $this->notifyError('Debe seleccionar al menos una factura para aplicar el abono.');
            return;
        }

        if ($totalAbonos <= 0) {
            $this->notifyError('Debe ingresar al menos un monto pagado mayor a cero para alguna factura.');
            return;
        }

        $allPositive = collect($sales)->every(fn($p) => isset($p['amount_payment']) && $p['amount_payment'] > 0);
        if (!$allPositive) {
            $this->notifyError('Todos los montos pagados deben ser mayores a cero.');
            return;
        }

        if ($totalAbonos > $data['amount']) {
            $this->notifyError('La suma de abonos por factura no puede ser mayor al monto total del abono.');
            return;
        }

        if ($totalAbonos < $data['amount']) {
            $this->notifyError('La suma de abonos por factura debe ser igual al monto total del abono.');
            return;
        }
    }

    protected function notifyError(string $message): void
    {
        Notification::make()
            ->title($message)
            ->danger()
            ->send();
        $this->halt();
    }
}
