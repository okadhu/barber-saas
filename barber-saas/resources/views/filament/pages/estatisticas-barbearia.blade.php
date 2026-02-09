<x-filament-panels::page>
    <x-filament-panels::header :actions="$this->getHeaderActions()">
        <x-slot name="heading">
            Estatísticas - {{ $barbearia->nome }}
        </x-slot>
        <x-slot name="description">
            Análise de desempenho e métricas da barbearia
        </x-slot>
    </x-filament-panels::header>

    <div class="space-y-6">
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($stats as $stat)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $stat->value }}
                    </div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $stat->label }}
                    </div>
                    @if($stat->description)
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $stat->description }}
                        </div>
                    @endif
                    @if($stat->color)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                @if($stat->color === 'success') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($stat->color === 'danger') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @elseif($stat->color === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($stat->color === 'info') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                @endif">
                                {{ $stat->label }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Gráficos e Tabelas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Agendamentos por Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Agendamentos por Status
                </h3>
                <div class="space-y-3">
                    @foreach($agendamentosPorStatus as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $item['color'] }}"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['status'] }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Serviços -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Serviços Mais Populares
                </h3>
                <div class="space-y-3">
                    @foreach($topServicos as $item)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['servico'] }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['total'] }} agendamentos</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Agendamentos por Mês (Gráfico Simples) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Agendamentos por Mês ({{ now()->year }})
                </h3>
                <div class="h-64">
                    <div class="flex items-end h-48 space-x-2">
                        @foreach($agendamentosPorMes as $item)
                            <div class="flex-1 flex flex-col items-center">
                                <div 
                                    class="w-full bg-primary-500 dark:bg-primary-400 rounded-t-lg transition-all hover:opacity-80"
                                    style="height: {{ $item['total'] > 0 ? max(20, ($item['total'] / max(array_column($agendamentosPorMes, 'total'))) * 100) : 0 }}%"
                                    title="{{ $item['mes'] }}: {{ $item['total'] }} agendamentos"
                                ></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $item['mes'] }}</div>
                                <div class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $item['total'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Gerais -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Informações da Barbearia
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Responsável</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $barbearia->responsavel ?? 'Não informado' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Telefone</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $barbearia->telefone ?? 'Não informado' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">E-mail</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $barbearia->email ?? 'Não informado' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Cidade/Estado</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $barbearia->cidade ?? 'Não informado' }}/{{ $barbearia->estado ?? 'Não informado' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>