<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Tributario Electrónico</title>
    <style>
        /* RESET BÁSICO */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; padding-left: 50px; padding-right: 50px; }

        table { width: 100%; border-collapse: collapse; }

        /* HEADER */
        .header { text-align: center; padding: 18px; border-bottom: 2px solid #B71C1C; margin-bottom: 15px; }
        .header h2 { font-size: 20px; color: #D32F2F; margin-bottom: 5px; }
        .header h3 { font-size: 14px; color: #555; }

        /* INFO TABLAS */
        .info-table { margin: 15px 0; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .info-table td { padding: 10px; font-size: 12px; vertical-align: top; }
        .info-table th { background-color: #f5f5f5; font-weight: bold; text-align: left; padding: 8px; }

        /* PRODUCTOS */
        .tabla-productos { margin: 20px 0; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .tabla-productos th { background-color: #FFCDD2; padding: 10px; text-align: center; }
        .tabla-productos td { padding: 10px; text-align: center; border-bottom: 1px solid #eee; }
        .tabla-productos tr:last-child td { border-bottom: none; }

        /* ANULADO */
        .tabla-productos-anulado::before {
            content: "ANULADO";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(255,0,0,0.1);
            z-index: 0;
            pointer-events: none;
        }

        /* RESUMEN */
        .resumen { margin: 20px 0; display: flex; justify-content: space-between; gap: 20px; }
        .resumen-left, .resumen-right { width: 48%; }
        .resumen p { margin: 6px 0; }

        /* FOOTER */
        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; border-top: 2px solid #B71C1C; padding: 10px; background: #fff0f0; font-size: 11px; }
        .footer table td { padding: 4px; }

        /* ESTILO GENERAL */
        .bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-red { background-color: #D32F2F; color: #fff; text-align: center; padding: 6px 0; border-radius: 4px; }

    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <h2>{{$empresa->name}} <br/> {{$transfer->whereHouseFrom->name ?? ''}}</h2>
    <h3>TRASLADO</h3>
</div>

<!-- INFORMACIÓN DEL TRASLADO -->
<table class="info-table">
    <tr>
        <td>
            <b>Origen</b><br>
            Documento: {{$transfer->transfer_number}}<br>
            Fecha: {{date('d-m-Y H:i:s', strtotime($transfer->transfer_date))}}<br>
            Enviado por: {{$transfer->userSend->name ?? ''}} {{$transfer->userSend->last_name ?? ''}}
        </td>
        <td>
            <b>Destino</b><br>
            Documento: {{$transfer->transfer_number}}<br>
            Fecha Envío: {{date('d-m-Y H:i:s', strtotime($transfer->transfer_date))}}<br>
            Recibido por: {{$transfer->wherehouseTo->name ?? ''}}
        </td>
    </tr>
    <tr>
        <td>Estado Envío: <b>{{$transfer->status_send ?? ''}}</b></td>
        <td>Destino: {{$transfer->wherehouseTo->name ?? ''}}</td>
    </tr>
</table>

<!-- TABLA DE PRODUCTOS -->
<table class="tabla-productos{{ $transfer->status_send == 'Anulado' ? '-anulado' : '' }}">
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
    @foreach ($transfer->transferDetails as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->quantity }}</td>
            <td>Unidad</td>
            <td>{{ $item->inventory->product->name ?? '' }} <b>SKU{{ $item->inventory->product->sku ?? '' }}</b></td>
            <td>${{ number_format($item->price ?? 0, 2) }}</td>
            <td>${{ number_format($item->total ?? 0, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- RESUMEN Y VALOR EN LETRAS -->
<div class="resumen">
    <div class="resumen-left">
        <p><b>VALOR EN LETRAS:</b> {{ $montoLetras ?? '' }}</p>
        <p class="bg-red">EXTENSIÓN - INFORMACIÓN ADICIONAL</p>
        <p>Entregado por: _____________________</p>
        <p>Recibido por: _____________________</p>
        <p>N° Documento: _____________________</p>
        <p>Condición Operación: {{$datos["DTE"]['resumen']['condicionOperacion'] ?? ''}}</p>
        <p>Observaciones: _____________________</p>
    </div>
    <div class="resumen-right text-right">
        <p>Total No Sujeto: ${{ number_format(0,2) }}</p>
        <p>Total Exento: ${{ number_format(0,2) }}</p>
        <p><b>Total Gravadas: ${{ number_format($transfer->total,2) }}</b></p>
    </div>
</div>

<!-- FOOTER FIJO -->
<div class="footer">
    <p class="text-right">Documento generado electrónicamente - Traslado</p>
</div>

</body>
</html>
