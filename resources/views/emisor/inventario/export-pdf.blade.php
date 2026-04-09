<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tipo === 'valorizado' ? 'Inventario Valorizado' : 'Reporte de Stock' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; color: #555; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #e2e8f0; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #94a3b8; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .total-row { background-color: #f0f9ff; font-weight: bold; }
        .stock-bajo { color: #dc2626; font-weight: bold; }
        .normal { color: #16a34a; }
        .pvp { color: #d97706; font-size: 9px; }
        .fecha { font-size: 10px; color: #666; }
        .total-box { margin-top: 15px; text-align: right; padding: 10px; background: #f0f9ff; border: 1px solid #bfdbfe; }
        .total-box .label { font-size: 12px; color: #1e40af; }
        .total-box .value { font-size: 18px; font-weight: bold; color: #1e3a5f; }
    </style>
</head>
<body>
    <h1>{{ $emisor->razon_social }}</h1>
    <h2>{{ $tipo === 'valorizado' ? 'Inventario Valorizado' : 'Reporte de Stock' }} <span class="fecha">- {{ now()->format('d/m/Y H:i') }}</span></h2>

    @if($tipo === 'valorizado')
    <div class="total-box">
        <span class="label">Total Inventario Valorizado:</span>
        <span class="value">${{ number_format($totalValorizado, 2) }}</span>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Establecimiento</th>
                <th class="text-right">Stock</th>
                @if($tipo === 'stock')
                <th class="text-right">Stock Min.</th>
                @endif
                <th class="text-right">Costo Prom.</th>
                @if($tipo === 'valorizado')
                <th class="text-right">Valor Total</th>
                @else
                <th>Estado</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($inventarios as $inv)
            @php
                $costoUsado = (float) $inv->costo_promedio > 0
                    ? (float) $inv->costo_promedio
                    : (float) ($inv->producto->precio_unitario ?? 0);
            @endphp
            <tr>
                <td>{{ $inv->producto->codigo_principal ?? '-' }}</td>
                <td>{{ $inv->producto->nombre }}</td>
                <td>{{ $inv->establecimiento->codigo ?? '' }} - {{ $inv->establecimiento->nombre ?? '' }}</td>
                <td class="text-right">{{ number_format($inv->stock_actual, 2) }}</td>
                @if($tipo === 'stock')
                <td class="text-right">{{ number_format($inv->stock_minimo, 2) }}</td>
                @endif
                <td class="text-right">
                    ${{ number_format($costoUsado, 4) }}
                    @if((float) $inv->costo_promedio <= 0 && $costoUsado > 0)
                        <span class="pvp">(PVP)</span>
                    @endif
                </td>
                @if($tipo === 'valorizado')
                <td class="text-right font-bold">${{ number_format((float) $inv->stock_actual * $costoUsado, 2) }}</td>
                @else
                <td>
                    @if($inv->stockBajo())
                        <span class="stock-bajo">Stock Bajo</span>
                    @else
                        <span class="normal">Normal</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No hay registros.</td>
            </tr>
            @endforelse
        </tbody>
        @if($tipo === 'valorizado' && $inventarios->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL:</td>
                <td class="text-right">${{ number_format($totalValorizado, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
