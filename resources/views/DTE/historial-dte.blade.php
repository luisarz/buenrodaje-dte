<style>
    /* Tabla responsiva y estilizada */
    .custom-table-container {
        overflow-x: auto;
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .custom-table th, .custom-table td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
        vertical-align: top;
    }

    .custom-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        text-transform: uppercase;
        color: #333;
    }

    .custom-table tr:nth-child(even) {
        background-color: #fafafa;
    }

    .status-rechazado {
        background-color: #FECACA; /* rojo claro */
    }

    .status-recibos {
        background-color: #DCFCE7; /* verde success similar a Tailwind 4 */
    }

    .status-aprobado {
        background-color: #86EFAC; /* verde claro */
    }

    .status-default {
        background-color: #FDE68A; /* amarillo claro */
    }

    pre.json-pre {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        overflow-x: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* Modal */
    .custom-modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .custom-modal-content h2 {
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .custom-modal-content button {
        margin-top: 15px;
        padding: 8px 16px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .custom-modal-content button:hover {
        background-color: #dc2626;
    }
</style>

<div class="custom-table-container">
    <table class="custom-table">
        <thead>
        <tr>
            <th>Estado</th>
            <th>Código Generación</th>
            <th>Fecha Procesamiento</th>
            <th>Descripción Mensaje</th>
            <th>Observaciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($historial as $item)
            @php
                $estado = strtoupper($item['estado']);
                $statusClass = match($estado) {
                    'RECHAZADO' => 'status-rechazado',
                    'PROCESADO', 'PROCESADOS' => 'status-recibos',
                    'APROBADO' => 'status-aprobado',
                    default => 'status-default',
                };

                $raw = $item['observaciones'];
                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    if (is_string($decoded)) $decoded = json_decode($decoded, true);
                } else {
                    $decoded = $raw;
                }
            @endphp
            <tr class="{{ $statusClass }}">
                <td>{{ $item['estado'] }}</td>
                <td>{{ $item['codigoGeneracion'] }}</td>
                <td>{{ $item['fhProcesamiento'] }}</td>
                <td>{{ $item['descripcionMsg'] }}</td>
                <td>
@php
    // Quitar comillas externas y decodificar JSON
    $jsonString = trim($item['observaciones'], '"');
    $data = json_decode(stripslashes($jsonString), true);
@endphp

<pre>
{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>



                </td>


            </tr>
        @endforeach
        </tbody>
    </table>
</div>


