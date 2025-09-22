{{-- resources/views/abonos/print.blade.php --}}
        <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Abono #{{ $payment->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #3a3a3a;
            margin: 30px 40px;
            position: relative;
            background-color: #fff;
        }

        /* Marca de agua ANULADO */
        @if($payment->deleted_at)
        body::before {
            content: "ANULADO";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 130px;
            font-weight: 900;
            color: rgba(220, 20, 60, 0.12);
            pointer-events: none;
            z-index: 9999;
            user-select: none;
            white-space: nowrap;
        }
        @endif

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 30%;
            text-align: left;
            padding-right: 15px;
        }
        .logo-cell img {
            max-width: 180px;
            max-height: 80px;
            object-fit: contain;
        }
        .company-cell {
            width: 70%;
            text-align: right;
            font-size: 12px;
            color: #666;
            line-height: 1.3;
        }
        .company-cell h2 {
            margin: 0 0 8px 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #b22222;
            letter-spacing: 1px;
            font-weight: 700;
            line-height: 1.1;
        }
        .company-cell p {
            margin: 3px 0;
        }

        /* Títulos y textos */
        h3 {
            margin-top: 5px;
            margin-bottom: 5px;
            font-size: 20px;
            border-bottom: 3px solid #b22222;
            color: #b22222;
            display: inline-block;
            padding-bottom: 4px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        p strong {
            color: #8b0000;
        }

        /* Tabla datos del abono */
        .payment-details {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-top: 0px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #444;
        }
        .payment-details td, .payment-details th {
            border: 1px solid #ddd;
            padding: 4px 10px;
            vertical-align: middle;
        }
        .payment-details th {
            background-color: #b22222;
            color: white;
            font-weight: 600;
            text-align: left;
            width: 180px;
        }
        .payment-details td {
            background-color: #fafafa;
        }

        /* Tabla detalle ventas compacta */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px; /* fuente más pequeña */
            color: #444;
        }
        thead th {
            background-color: #b22222;
            color: #fff;
            padding: 8px; /* padding reducido */
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        tbody td {
            border-bottom: 1px solid #ddd;
            padding: 6px 6px; /* padding reducido */
            text-align: center;
        }
        tbody tr:hover {
            background-color: #ffe5e5;
        }
        .text-right {
            text-align: right;
            padding-right: 10px;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        /* Total */
        .total {
            margin-top: 25px;
            font-size: 18px;
            font-weight: 700;
            text-align: right;
            color: #b22222;
            letter-spacing: 0.05em;
        }

        /* Footer */
        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            text-align: center;
            font-style: italic;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>

{{-- ENCABEZADO EMPRESA --}}
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <img src="{{ asset('storage/'.$sucursal[0]->logo) }}" alt="Logo">
        </td>
        <td class="company-cell">
            <h2>{{ $sucursal[0]->name }}</h2>
            <p><strong>NIT:</strong> {{ $sucursal[0]->nit }} | <strong>NRC:</strong> {{ $sucursal[0]->nrc }}</p>
            <p>{{ $sucursal[0]->address }}</p>
            <p>Tel: {{ $sucursal[0]->phone }} | Email: {{ $sucursal[0]->email }}</p>
            <p>Web: {{ $sucursal[0]->web }}</p>
        </td>
    </tr>
</table>

{{-- DATOS DEL ABONO EN TABLA --}}
<h3>Recibo de Abono #{{ $payment->id }}</h3>

<table class="payment-details">
    <tbody>
    <tr>
        <th>Fecha Abono</th>
        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
    </tr>
    @if(!empty($payment->customer_name))
        <tr>
            <th>Depositante</th>
            <td>{{ $payment->customer_name }}</td>
        </tr>
    @endif
    @if(!empty($payment->customer_document_type))
        <tr>
            <th>Tipo Documento</th>
            <td>{{ $payment->customer_document_type }}</td>
        </tr>
    @endif
    @if(!empty($payment->customer_document_number))
        <tr>
            <th>Número Documento</th>
            <td>{{ $payment->customer_document_number }}</td>
        </tr>
    @endif
    <tr>
        <th>Método de Pago</th>
        <td>{{ $payment->method }}</td>
    </tr>
    @if(!empty($payment->number_check))
        <tr>
            <th>No. Cheque</th>
            <td>{{ $payment->number_check }}</td>
        </tr>
    @endif
    @if(!empty($payment->reference))
        <tr>
            <th>Referencia</th>
            <td>{{ $payment->reference }}</td>
        </tr>
    @endif
    @if(!empty($payment->description))
        <tr>
            <th>Descripción</th>
            <td>{{ $payment->description }}</td>
        </tr>
    @endif
    </tbody>
</table>

{{-- DETALLE DE VENTAS --}}
<table>
    <thead>
    <tr>
        <th>Fecha Venta</th>
        <th>No. Orden</th>
        <th>Cliente</th>
        <th>NRC Cliente</th>
        <th>Saldo Anterior</th>
        <th>Monto Pagado</th>
        <th>Saldo Actual</th>
    </tr>
    </thead>
    <tbody>
    @foreach($payment->sales as $sale)
        <tr>
            <td>{{ \Carbon\Carbon::parse($sale->purchase_date)->format('d/m/Y') }}</td>
            <td>{{ $sale->order_number }}</td>
            <td>{{ $sale->customer->name ?? '' }} {{ $sale->customer->last_name ?? '' }}</td>
            <td>{{ $sale->customer->nrc ?? '' }}</td>
            <td class="text-right">${{ number_format((float) $sale->pivot->amount_before, 2) }}</td>
            <td class="text-right">${{ number_format((float) $sale->pivot->amount_payment, 2) }}</td>
            <td class="text-right">${{ number_format((float) $sale->pivot->actual_amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- TOTAL --}}
<div class="total">
    Total Abonado: ${{ number_format((float) $payment->amount, 2) }}
</div>

{{-- PIE DE PÁGINA --}}
<div class="footer">
    <p>Gracias por su pago. Este documento es válido como comprobante.</p>
    <p>Fecha de impresión: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
