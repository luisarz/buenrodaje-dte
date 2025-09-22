<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Tributario Electrónico</title>
    {{--    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">--}}

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .header {
            width: 100%;
            text-align: center;
            /*padding: 10px;*/
            /*box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.1);*/
        }


        .footer {
            left: 0;
            width: 100%;
            border: 1px solid black; /* Borde sólido de 1px y color #f2f2f2 */
            border-radius: 10px; /* Radio redondeado de 10px */
            text-align: right;
            font-size: 12px;
        }

        .content {
            flex: 1;
            padding-bottom: 100px; /* Espacio para el footer */
        }

        .header img {
            width: 200px;
        }

        .empresa-info, .documento-info, .tabla-productos, .resumen {
            margin: 10px 0;
        }

        .tabla-productos th, .tabla-productos td {
            padding: 5px;
        }

        .tabla-productos th {
            background-color: #f2f2f2;
        }

        .resumen p {
            margin: 5px 0;
            text-align: right;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<!-- Header Empresa -->
<div class="header" style="border: 0px solid #ccc; border-radius: 10px; padding: 0px; font-family: Arial, sans-serif;">


    <table style="width: 100%;">
        <tr>
            {{-- IZQUIERDA: LOGO Y EMPRESA --}}
            <td style="width: 40%; vertical-align: top;">
                <table style="width: 100%; font-size: 12px; font-family: Arial, sans-serif; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0;">
                            <img src="{{ asset($datos['logo'] ?? '') }}" alt="Logo Empresa" style="max-height: 80px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-size: 13px; padding: 2px 0;">
                            {{ $datos['empresa']['nombre'] ?? 'NOMBRE DE EMPRESA' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">
                            {{ $datos['empresa']['descActividad'] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;">
                            <strong>NIT:</strong> @php
                                $nit = $datos['empresa']['nit'] ?? '';
                            @endphp
                            {{ strlen($nit) === 14 ? substr($nit, 0, 4) . '-' . substr($nit, 4, 6) . '-' . substr($nit, 10, 3) . '-' . substr($nit, 13, 1) : $nit }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>NRC:</strong> {{ $datos['empresa']['nrc'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Dirección:</strong> {{ $datos['empresa']['direccion']['complemento'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Teléfono:</strong> {{ $datos['empresa']['telefono'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Correo:</strong> {{ $datos['empresa']['correo'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Sitio web:</strong> {{ $datos['empresa']['web'] ?? '' }}</td>
                    </tr>
                </table>

            </td>

            {{-- DERECHA: DTE INFO --}}
            <td style="width: 60%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                    <th style="width: 100px !important;"></th>
                    <th style="width:200px;"></th>
                    </thead>
                    <tr style="background-color: #e12828; color: white;">
                        <td colspan="2" style="text-align: center; font-size: 14px; font-weight: bold; padding: 5px;">
                            DOCUMENTO TRIBUTARIO ELECTRÓNICO
                        </td>
                    </tr>
                    <tr style="background-color: #e12828; color: white;">
                        <td colspan="2" style="text-align: center; font-size: 16px; font-weight: bold; padding: 5px;">
                            {{ $datos['tipoDocumento'] }}
                        </td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="font-weight: bold;">Código generación:</td>
                        <td>
                            {{ $datos['DTE']['respuestaHacienda']['codigoGeneracion'] ?? $datos['DTE']['identificacion']['codigoGeneracion'] }}
                        </td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="font-weight: bold;">Sello de recepción:</td>
                        <td>{{ $datos['DTE']['respuestaHacienda']['selloRecibido'] ?? 'Contingencia' }}</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="font-weight: bold;">Número de control:</td>
                        <td>{{ $datos['DTE']['identificacion']['numeroControl'] }}</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="font-weight: bold;">Fecha emisión:</td>
                        <td>{{ date('d-m-Y', strtotime($datos['DTE']['identificacion']['fecEmi'])) }}</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="font-weight: bold;">Hora emisión:</td>
                        <td>{{ $datos['DTE']['identificacion']['horEmi'] }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <img src="{{ asset($qr) }}" alt="QR Código"
                                 style="width: 110px; height: 100px; float: left;">
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <thead>
                                <tr>
                                    <th style="width: 40%; text-align: left; padding: 2px 4px;"></th>
                                    <th style="width: 60%; text-align: left; padding: 2px 4px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="2" style="text-align: center; font-weight: bold; padding: 4px 0; background-color: #e12828">
                                        DETALLES ADICIONALES
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; text-transform: uppercase;">
                                        Código Cliente
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px;">2654</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; text-transform: uppercase;">
                                        Tipo Movimiento
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px;">2654</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; text-transform: uppercase;">
                                        Vendedor
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px;">2654</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; text-transform: uppercase;">
                                        Código almacén
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px;">2654</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 2px 4px; font-weight: bold; text-transform: uppercase;">
                                        Orden de compra
                                    </td>
                                    <td style="text-align: left; padding: 2px 4px;">2654</td>
                                </tr>
                                </tbody>
                            </table>


                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>


<!-- Contenido principal -->

<div class="content">
    <div style="border-bottom: 1px solid black; font-weight: bold; font-size: 14px; font-family: Arial, sans-serif; color: #333; padding-bottom: 4px;">
        Información del receptor
    </div>

    <!-- Info Cliente -->
    <div class="cliente-info">
        <table style="width: 100%; border-collapse: collapse; font-size: 10px; font-family: Arial, sans-serif;">
            <thead>
            <th style="width: 80px;"></th>
            <th style="width: 400px;"></th>
            <th style="width: 60px;"></th>
            <th style="width: 100px;"></th>
            </thead>

            <tr>

                <td><b>NOMBRE</b></td>
                <td>{{ $datos['DTE']['receptor']['nombre']??'' }}</td>
                <td> <b>NIT</b></td>
                <td>{{ $datos['DTE']['receptor']['nit'] ?? '' }}</td>
            </tr>
            <tr>

                <td><b>ACTIVIDAD</b></td>
                <td>{{ $datos['DTE']['receptor']['codActividad']??'' }}-{{ $datos['DTE']['receptor']['descActividad']??'' }}</td>
                <td><b>NRC</b></td>
                <td>{{ $datos['DTE']['receptor']['nrc']??'' }}</td>
            </tr>
            <tr>
                <td><b>DIRECCIÓN</b></td>
                <td>{{ $datos['DTE']['receptor']['direccion']['complemento']??'' }}</td>
                <td><b>TELEFONO</b></td>
                <td>{{ $datos['DTE']['receptor']['telefono']??'' }}</td>
            </tr>
            <tr>
                <td><b>REFERENCIAS</b></td>
                <td></td>
                <td><b>CORREO</b></td>
                <td>{{ $datos['DTE']['receptor']['correo']??'' }}</td>
            </tr>
        </table>
    </div>

    <!-- Tabla Productos -->
    <table class="tabla-productos" width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
        <tr>
            <th>No</th>
            <th>Cant</th>
            <th>Unidad de medida</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Precio Unitario</th>
            <th>Desc Item</th>
            <th>Ventas No Sujetas</th>
            <th>Ventas Exentas</th>
            <th>Ventas Gravadas</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($datos['DTE']['cuerpo']??$datos['DTE']['cuerpoDocumento'] as $item)
            @php
                        $unidad = App\Models\UnitMeasurement::where('code', $item['uniMedida'])->value('description') ?? $item['uniMedida'];

            @endphp

            <tr>
                <td>{{ $item['numItem'] }}</td>
                <td>{{ $item['cantidad'] }}</td>
                <td>{{  $unidad }}</td>
                <td>{{ $item['codigo'] }}</td>
                <td>{{ $item['descripcion'] }}</td>
                <td>${{ number_format($item['precioUni'], 2) }}</td>
                <td>${{ number_format($item['montoDescu'], 2) }}</td>
                <td>${{ number_format($item['ventaNoSuj']??$item['noGravado']??0, 2) }}</td>
                <td>${{ number_format($item['ventaExenta']??$item['noGravado']??0, 2) }}</td>
                <td>${{ number_format($item['ventaGravada']??$item['compra']??0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Footer fijo -->
<div class="footer" >


    <table style="width: 100%; border-collapse: collapse; font-size: 10px; font-family: Arial, sans-serif;">
        <tr>
            <td style="width: 60%">
                <table style="width: 100%">
                    <tr>
                        <td colspan="2"><b>VALOR EN LETRAS:</b> {{ $datos["DTE"]['resumen']['totalLetras'] }} DOLARES
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="background-color: #57595B; color: white;  text-align: center;">
                            EXTENSIÓN-INFORMACIÓN ADICIONAL
                        </td>
                    </tr>
                    <tr>
                        @php
                            $ext = $datos['DTE']['extencion'] ?? $datos['DTE']['extension'] ?? null;
                        @endphp
                        <td>Entregado por: {{  $ext['nombEntrega']??'S/N' }}</td>
                        <td>Recibido por:</td>
                    </tr>
                    <tr>
                        <td>N° Documento:</td>
                        <td>N° Documento:</td>
                    </tr>
                    <tr>
                        <td>Condicion Operación</td>
                                                <td>{{$datos["DTE"]['resumen']['condicionOperacion']}}</td>
{{--                        <td>--}}
{{--                            @if(isset($datos['DTE']['resumen']['totalPagar']))--}}
{{--                                ${{ number_format($datos['DTE']['resumen']['totalPagar'], 2) }}--}}
{{--                            @elseif(isset($datos['DTE']['resumen']['montoTotalOperacion']))--}}
{{--                                ${{ number_format($datos['DTE']['resumen']['montoTotalOperacion'], 2) }}--}}
{{--                            @elseif(isset($datos['DTE']['resumen']['totalLetras']))--}}
{{--                                {{ $datos['DTE']['resumen']['totalLetras'] }}--}}
{{--                            @endif--}}
{{--                        </td>--}}
                    </tr>
                    <tr>
                        <td colspan="2">Observaciones:</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%">Total Operaciones:
                <table style="width: 100%">
                    <tr>
                        <td>Total No Sujeto:</td>
                        <td>
                            ${{ number_format($datos['DTE']['resumen']['totalNoSuj']??$datos['DTE']['resumen']['totalNoGravado']??0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Exento:</td>
                        <td>
                            ${{ number_format($datos['DTE']['resumen']['totalExenta']??$datos['DTE']['resumen']['totalNoGravado']??0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Gravadas:</td>
                        <td>
                            ${{ number_format($datos['DTE']['resumen']['totalGravada']??$datos['DTE']['resumen']['totalCompra'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Subtotal:</td>
                        <td>
                            ${{ number_format($datos['DTE']['resumen']['subTotal']??$datos['DTE']['resumen']['totalGravada'], 2) }}</td>
                    </tr>
                    @isset($datos['DTE']['resumen']['tributos'])
                        @foreach($datos['DTE']['resumen']['tributos'] as $tributo)
                            <tr>
                                <td>{{ $tributo['descripcion'] }}:</td>
                                <td>${{ number_format($tributo['valor'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endisset
                    <tr style="background-color: #57595B; color: white;">
                        <td>
                            <b>TOTAL A PAGAR:</b></td>
                        <td>
                            ${{number_format($datos['DTE']['resumen']['totalPagar']??$datos['DTE']['resumen']['montoTotalOperacion'], 2)}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


</div>
<div>
    <p style="text-align:center; font-size: 8px; font-family: Arial, sans-serif; margin-top: 10px; font-weight: bold;">
       EL INCUMPLIMIENTO DEL PAGO DE ESTE DOCUMENTO AL CRÉDITO EN EL PLAZO ESTIPULADO. GENERARÁ UN CARGO POR MORA DEL 2% MENSUAL SOBRE SALDO VENCIDO. TODO CHEQUE RECHAZADO, GENERARÁ UN CARGO ADMINISTRATIVO DE $10.00, NO SE ACEPTAN CAMBIOS NI DEVOLUCIONES DESPUÉS DE 7 DÍAS CALENDARIO, EN MATERIALES ELÉCTRICOS NO ADMITIMOS DEVOLUCIONES
    </p>
</div>
</body>
</html>
