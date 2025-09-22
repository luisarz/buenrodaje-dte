<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #2c2c2c;
            background: #fff;
        }

        h2, h3 {
            margin: 0;
            color: #c0392b; /* Rojo corporativo */
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            border-collapse: collapse;
            font-size: 13px;
            background: #fafafa;
        }

        .header td {
            padding: 10px;
            border: 1px solid #e0e0e0;
        }

        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }

        .tabla-productos th {
            background: #c0392b;
            color: white;
            padding: 10px;
            text-align: center;
            border: 1px solid #b52b21;
        }

        .tabla-productos td {
            padding: 8px;
            border: 1px solid #e0e0e0;
        }

        .tabla-productos tr:nth-child(even) {
            background: #fdf2f2; /* Rojo muy suave */
        }

        .tabla-productos-anulado::before {
            content: "ANULADO";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(192, 57, 43, 0.08); /* Rojo con baja opacidad */
            z-index: 0;
            pointer-events: none;
        }

        .footer {
            width: 100%;
            margin-top: 30px;
            font-size: 12px;
        }

        .footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer td {
            padding: 6px;
            vertical-align: top;
        }

        .footer .total-box {
            background: #c0392b;
            color: #fff;
            font-weight: bold;
            text-align: right;
            padding: 8px;
            border-radius: 4px;
        }

        .footer h4 {
            margin: 0;
            padding: 6px;
            background: #e74c3c;
            color: white;
            text-align: center;
            font-size: 13px;
        }

        .observaciones {
            padding: 6px;
            border-top: 1px solid #ccc;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>

<!-- Encabezado -->
<div class="header">
    <table>
        <tr>
            <td colspan="4" style="text-align:center;">
                <h2>{{ $empresa->name }} | {{ $datos->whereHouse->name }}</h2>
                <h3>COTIZACIÓN | <b>{{ $datos->order_number }}</b> | {{ date('d-m-Y H:s:i',strtotime($datos->created_at)) }}</h3>
            </td>
        </tr>
        <tr>
            <td><b>Estado:</b><br>{{ $datos->sale_status??'' }}</td>
            <td><b>Vendedor:</b><br>{{ $datos->seller->name??'' }} {{ $datos->seller->last_name??'' }}</td>
            <td colspan="2"><b>Cliente:</b><br>{{ $datos->customer->name??'' }} {{ $datos->customer->last_name??'' }} <br> {{ $datos->customer->address??'' }}</td>
        </tr>
    </table>
</div>

<!-- Tabla Productos -->
<table class="tabla-productos{{ $datos->sale_status == 'Anulado' ? '-anulado' : '' }}">
    <thead>
    <tr>
        <th>No</th>
        <th>Cant</th>
        <th>Unidad</th>
        <th>Descripción</th>
        <th>Precio Unitario</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($datos->saleDetails as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->quantity }}</td>
            <td>Unidad</td>
            <td>
                {{ $item->inventory->product->name ?? '' }}
                @if(!empty($item->inventory->product->sku))
                    <br><small><b>SKU:</b> {{ $item->inventory->product->sku }}</small>
                @endif
                @if(!empty($item->description))
                    <br><small><b>Descripción:</b> {{ $item->description }}</small>
                @endif
            </td>
            <td style="text-align:right;">${{ number_format($item->price??0, 2) }}</td>
            <td style="text-align:right;">${{ number_format($item->total??0, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Footer -->
<div class="footer">
    <table>
        <tr>
            <td style="width:70%">
                <b>Valor en letras:</b> {{ $montoLetras ?? '' }} <br><br>
                <b>Entregado por:</b> _____________________ &nbsp;&nbsp;
                <b>Recibido por:</b> _____________________ <br><br>
                <b>Condición Operación:</b> {{ $datos["DTE"]['resumen']['condicionOperacion']??'' }}
                <div class="observaciones">
                    <b>Observaciones:</b>
                </div>
            </td>
            <td style="width:30%">
                <table style="width:100%">
                    <tr>
                        <td>Total No Sujeto:</td>
                        <td style="text-align:right;">${{ number_format(0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Exento:</td>
                        <td style="text-align:right;">${{ number_format(0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Gravadas:</td>
                        <td style="text-align:right;">${{ number_format($datos->sale_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align:right;">${{ number_format($datos->sale_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="total-box">TOTAL A PAGAR: ${{ number_format($datos->sale_total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <h4>EXTENSIÓN - INFORMACIÓN ADICIONAL</h4>
</div>

</body>
</html>
