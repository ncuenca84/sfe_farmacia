<x-emisor-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Resumen de actividad &mdash; {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>
    </x-slot>

    {{-- ── KPI Cards - Flat Minimal ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        {{-- Ventas --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #ecfdf5;">
                    <svg class="w-6 h-6" style="color: #10b981;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">${{ number_format($ventasMes, 2) }}</p>
                    <p class="text-sm text-gray-400 mt-1">Ventas del mes</p>
                </div>
            </div>
        </div>

        {{-- Facturas --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #eff6ff;">
                    <svg class="w-6 h-6" style="color: #3b82f6;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $facturasMes }}</p>
                    <p class="text-sm text-gray-400 mt-1">Facturas</p>
                </div>
            </div>
        </div>

        {{-- N. Crédito --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #fffbeb;">
                    <svg class="w-6 h-6" style="color: #f59e0b;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $ncMes }}</p>
                    <p class="text-sm text-gray-400 mt-1">Notas de Credito</p>
                </div>
            </div>
        </div>

        {{-- N. Débito --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #fff7ed;">
                    <svg class="w-6 h-6" style="color: #f97316;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $ndMes }}</p>
                    <p class="text-sm text-gray-400 mt-1">Notas de Debito</p>
                </div>
            </div>
        </div>

        {{-- Retenciones --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #f5f3ff;">
                    <svg class="w-6 h-6" style="color: #8b5cf6;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $retencionesMes }}</p>
                    <p class="text-sm text-gray-400 mt-1">Retenciones</p>
                </div>
            </div>
        </div>

        {{-- Guías --}}
        <div class="bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: #fdf2f8;">
                    <svg class="w-6 h-6" style="color: #ec4899;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H6.375c-.621 0-1.125-.504-1.125-1.125V14.25m17.25 4.5h.375a1.125 1.125 0 001.125-1.125v-4.5a1.125 1.125 0 00-.832-1.087l-3.064-.919A2.243 2.243 0 0017.88 9H15V5.25A2.25 2.25 0 0012.75 3h-7.5A2.25 2.25 0 003 5.25v8.625c0 .621.504 1.125 1.125 1.125H5.25"/></svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 tracking-tight">{{ $guiasMes }}</p>
                    <p class="text-sm text-gray-400 mt-1">Guias de Remision</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row 1: Ventas + Tipo ──────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tendencia de Ventas (6 meses)</h3>
            <div class="h-72">
                <canvas id="chartVentas"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Comprobantes por Tipo</h3>
            <div class="h-72 flex items-center justify-center">
                <canvas id="chartTipos"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Charts Row 2: Facturas + Estado ──────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Facturas Emitidas (6 meses)</h3>
            <div class="h-72">
                <canvas id="chartFacturasMes"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Estado Facturas del Mes</h3>
            <div class="h-72 flex items-center justify-center">
                <canvas id="chartEstados"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Top Clientes + Suscripción ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Top Clientes --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-gray-700">Top 5 Clientes del Mes</h3>
                <span class="text-xs text-gray-400">Por volumen de ventas</span>
            </div>
            <div class="space-y-3">
                @forelse($topClientes as $i => $cliente)
                    @php
                        $cardColors = [
                            ['bg' => 'linear-gradient(135deg, #3b82f6, #60a5fa)', 'shadow' => 'rgba(59,130,246,0.15)'],
                            ['bg' => 'linear-gradient(135deg, #22c55e, #4ade80)', 'shadow' => 'rgba(34,197,94,0.15)'],
                            ['bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'shadow' => 'rgba(245,158,11,0.15)'],
                            ['bg' => 'linear-gradient(135deg, #8b5cf6, #a78bfa)', 'shadow' => 'rgba(139,92,246,0.15)'],
                            ['bg' => 'linear-gradient(135deg, #ec4899, #f472b6)', 'shadow' => 'rgba(236,72,153,0.15)'],
                        ];
                        $color = $cardColors[$i] ?? ['bg' => '#9ca3af', 'shadow' => 'rgba(0,0,0,0.05)'];
                        $maxVenta = $topClientes->first()->total_ventas ?: 1;
                        $pct = round(($cliente->total_ventas / $maxVenta) * 100);
                    @endphp
                    <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background: {{ $color['bg'] }}; box-shadow: 0 4px 12px {{ $color['shadow'] }};">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $cliente->razon_social }}</p>
                                <p class="text-sm font-bold text-gray-900 ml-3 flex-shrink-0">${{ number_format($cliente->total_ventas, 2) }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex-1 mr-3">
                                    <div class="w-full rounded-full h-1.5" style="background: #f3f4f6;">
                                        <div class="h-1.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%; background: {{ $color['bg'] }};"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $cliente->num_facturas }} {{ $cliente->num_facturas == 1 ? 'factura' : 'facturas' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <svg class="mx-auto h-10 w-10 text-gray-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <p class="text-sm text-gray-400">Sin datos para este mes</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Suscripción --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-5">Plan / Suscripcion</h3>
            @if($suscripcion)
                <div class="text-center mb-5">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-3" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 10px 25px -5px rgba(99,102,241,0.3);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <p class="text-xl font-bold text-gray-900">{{ $suscripcion->plan->nombre ?? 'N/A' }}</p>
                    <div class="mt-2">
                        @if($suscripcion->estado === \App\Enums\EstadoSuscripcion::ACTIVA)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: #ecfdf5; color: #047857;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #10b981;"></span>
                                Activa
                            </span>
                        @elseif($suscripcion->estado === \App\Enums\EstadoSuscripcion::VENCIDA)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: #fef2f2; color: #b91c1c;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #ef4444;"></span>
                                Vencida
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: #f3f4f6; color: #374151;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #6b7280;"></span>
                                {{ ucfirst(strtolower($suscripcion->estado->value)) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl p-4 mb-4" style="background: #f9fafb;">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="text-gray-500 font-medium">Comprobantes usados</span>
                        <span class="font-bold text-gray-700">{{ $suscripcion->comprobantes_usados ?? 0 }} / {{ $suscripcion->plan->max_comprobantes ?? 'Ilim.' }}</span>
                    </div>
                    @if($suscripcion->plan->max_comprobantes)
                        @php $porcentaje = min(100, (($suscripcion->comprobantes_usados ?? 0) / max(1, $suscripcion->plan->max_comprobantes)) * 100); @endphp
                        <div class="w-full rounded-full h-3" style="background: #e5e7eb;">
                            @php
                                $barColor = $porcentaje > 90 ? 'linear-gradient(90deg, #ef4444, #f87171)' : ($porcentaje > 70 ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #22c55e, #4ade80)');
                            @endphp
                            <div class="h-3 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%; background: {{ $barColor }};"></div>
                        </div>
                        @if($porcentaje > 90)
                            <p class="text-xs mt-2 font-medium" style="color: #ef4444;">Uso elevado - considere ampliar su plan</p>
                        @endif
                    @endif
                </div>

                <div class="rounded-xl p-4" style="background: #f9fafb;">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-medium">Vencimiento</span>
                        <span class="text-sm font-bold text-gray-700">{{ $suscripcion->fecha_fin ? \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    @if($suscripcion->fecha_fin)
                        @php $diasRestantes = (int) now()->diffInDays($suscripcion->fecha_fin, false); @endphp
                        @if($diasRestantes >= 0 && $diasRestantes <= 15)
                            <p class="text-xs mt-1 font-medium" style="color: #d97706;">Vence en {{ $diasRestantes }} {{ $diasRestantes === 1 ? 'dia' : 'dias' }}</p>
                        @endif
                    @endif
                </div>
            @else
                <div class="text-center py-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-3" style="background: #f3f4f6;">
                        <svg class="w-8 h-8" style="color: #d1d5db;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">Sin suscripcion activa</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.font.family = "'Inter', 'Segoe UI', system-ui, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#9ca3af';
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.pointStyleWidth = 8;

            const tooltipStyle = {
                backgroundColor: '#1f2937',
                titleColor: '#f3f4f6',
                bodyColor: '#d1d5db',
                cornerRadius: 8,
                padding: 12,
            };

            // ── Tendencia de Ventas ──────────────────────────────
            new Chart(document.getElementById('chartVentas'), {
                type: 'line',
                data: {
                    labels: @json($mesesLabels),
                    datasets: [{
                        label: 'Ventas ($)',
                        data: @json($mesesVentas),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...tooltipStyle,
                            callbacks: {
                                label: ctx => '$ ' + ctx.parsed.y.toLocaleString('es-EC', { minimumFractionDigits: 2 })
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: { callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // ── Comprobantes por Tipo ────────────────────────────
            const tiposData = @json($distribucionTipos);
            new Chart(document.getElementById('chartTipos'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(tiposData),
                    datasets: [{
                        data: Object.values(tiposData),
                        backgroundColor: ['#3b82f6', '#f59e0b', '#f97316', '#8b5cf6', '#06b6d4'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } },
                        tooltip: tooltipStyle
                    }
                }
            });

            // ── Facturas por Mes ─────────────────────────────────
            new Chart(document.getElementById('chartFacturasMes'), {
                type: 'bar',
                data: {
                    labels: @json($mesesLabels),
                    datasets: [{
                        label: 'Facturas',
                        data: @json($mesesCantidad),
                        backgroundColor: 'rgba(59, 130, 246, 0.75)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipStyle
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: { stepSize: 1 }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // ── Estado de Facturas ───────────────────────────────
            const estadosData = @json($estadosFacturas);
            const estadoColorMap = {
                'AUTORIZADO': '#10b981', 'CREADA': '#9ca3af', 'FIRMADA': '#3b82f6',
                'NO AUTORIZADO': '#ef4444', 'DEVUELTA': '#f59e0b', 'ANULADA': '#374151',
                'EN PROCESO': '#06b6d4', 'PROCESANDOSE': '#f97316',
            };
            const estadoLabels = Object.keys(estadosData);
            const estadoColors = estadoLabels.map(l => estadoColorMap[l] || '#d1d5db');

            new Chart(document.getElementById('chartEstados'), {
                type: 'doughnut',
                data: {
                    labels: estadoLabels,
                    datasets: [{
                        data: Object.values(estadosData),
                        backgroundColor: estadoColors,
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } },
                        tooltip: tooltipStyle
                    }
                }
            });
        });
    </script>
    @endpush
</x-emisor-layout>
