{{-- resources/views/abonos/print.blade.php --}}
        <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Abono #{{ $abono->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
            position: relative;
        }

        /* Marca de agua ANULADO */
        @if($abono->deleted_at)
        body::before {
            content: "ANULADO";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.15);
            pointer-events: none;
            z-index: 9999;
            user-select: none;
            white-space: nowrap;
        }
        @endif

        /* Encabezado como tabla */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 40%;
            text-align: left; /* Logo a la izquierda */
            padding-right: 10px;
        }
        .logo-cell img {
            max-width: 100%;
            max-height: 80px;
            object-fit: contain;
        }
        .company-cell {
            width: 60%;
            text-align: right; /* Texto a la derecha */
            font-size: 12px;
            color: #555;
        }
        .company-cell h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            text-transform: uppercase;
            color: #2c3e50;
        }
        .company-cell p {
            margin: 2px 0;
        }

        /* Sección títulos y párrafos */
        h3 {
            margin-top: 25px;
            margin-bottom: 5px;
            font-size: 18px;
            border-bottom: 2px solid #2c3e50;
            display: inline-block;
            padding-bottom: 3px;
        }
        p strong {
            color: #2c3e50;
        }

        /* Tabla detalle */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
        }
        thead th {
            background-color: #2c3e50;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-weight: normal;
        }
        tbody td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        /* Total */
        .total {
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
        }

        /* Footer */
        .footer  {
            font-size: 12px;
            color: #888;
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            text-align: center;
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

{{-- DATOS DEL ABONO --}}
<h3>Recibo de Abono #{{ $abono->id }}</h3>
<p>
    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($abono->fecha_abono)->format('d/m/Y') }}<br>

    @if(!empty($abono->entity))
        <strong>Depositante:</strong> {{ $abono->entity }}<br>
    @endif

    @if(!empty($abono->document_type_entity))
        <strong>Tipo Documento:</strong> {{ $abono->document_type_entity }}<br>
    @endif

    @if(!empty($abono->document_number))
        <strong>Número Documento:</strong> {{ $abono->document_number }}<br>
    @endif

    <strong>Método de Pago:</strong> {{ $abono->method }}<br>

    @if(!empty($abono->numero_cheque))
        <strong>No. Cheque:</strong> {{ $abono->numero_cheque }}<br>
    @endif

    @if(!empty($abono->referencia))
        <strong>Referencia:</strong> {{ $abono->referencia }}<br>
    @endif

    @if(!empty($abono->descripcion))
        <strong>Descripción:</strong> {{ $abono->descripcion }}<br>
    @endif
</p>

{{-- DETALLE DE COMPRAS --}}
<table>
    <thead>
    <tr>
        <th>Fecha Compra</th>
        <th>No. Documento</th>
        <th>Proveedor</th>
        <th>NRC Proveedor</th>
        <th>Saldo Anterior</th>
        <th>Monto Pagado</th>
        <th>Saldo Actual</th>
    </tr>
    </thead>
    <tbody>
    @foreach($abono->purchases as $purchase)
        <tr>
            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
            <td>{{ $purchase->document_number }}</td>
            <td>{{ $purchase->provider->comercial_name ?? '' }}</td>
            <td>{{ $purchase->provider->nrc ?? '' }}</td>
            <td class="text-right">${{ number_format((float) $purchase->pivot->saldo_anterior, 2) }}</td>
            <td class="text-right">${{ number_format((float) $purchase->pivot->monto_pagado, 2) }}</td>
            <td class="text-right">${{ number_format((float) $purchase->pivot->saldo_actual, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- TOTAL --}}
<div class="total">
    Total Abonado: ${{ number_format((float) $abono->monto, 2) }}
</div>

{{-- PIE DE PÁGINA --}}
<div class="footer">
    <p>Gracias por su pago. Este documento es válido como comprobante.</p>
    <p>Fecha de impresión: {{ date('d-m-Y H:i:s') }}</p>
</div>

</body>
</html>
