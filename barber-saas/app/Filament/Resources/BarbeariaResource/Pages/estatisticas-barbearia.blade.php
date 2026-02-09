<x-filament-panels::page>
    <x-filament-panels::header :actions="$this->getHeaderActions()">
        <x-slot name="heading">
            Estatísticas - {{ $record->nome }}
        </x-slot>
        <x-slot name="description">
            Visualize as estatísticas e métricas da barbearia
        </x-slot>
    </x-filament-panels::header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <!-- Clientes -->
        <x-filament::section icon="heroicon-o-user-group" icon-color="primary">
            <x-slot name="heading">
                Total de Clientes
            </x-slot>
            <x-slot name="description">
                Clientes ativos cadastrados
            </x-slot>
            <div class="text-3xl font-bold text-primary-600">
                {{ $estatisticas['clientes'] }}
            </div>
        </x-filament::section>

        <!-- Serviços -->
        <x-filament::section icon="heroicon-o-scissors" icon-color="success">
            <x-slot name="heading">
                Serviços Disponíveis
            </x-slot>
            <x-slot name="description">
                Total de serviços ativos
            </x-slot>
            <div class="text-3xl font-bold text-success-600">
                {{ $estatisticas['servicos'] }}
            </div>
        </x-filament::section>

        <!-- Barbeiros -->
        <x-filament::section icon="heroicon-o-user" icon-color="warning">
            <x-slot name="heading">
                Barbeiros
            </x-slot>
            <x-slot name="description">
                Total de barbeiros ativos
            </x-slot>
            <div class="text-3xl font-bold text-warning-600">
                {{ $estatisticas['barbeiros'] }}
            </div>
        </x-filament::section>

        <!-- Agendamentos do Mês -->
        <x-filament::section icon="heroicon-o-calendar" icon-color="info">
            <x-slot name="heading">
                Agendamentos (Mês)
            </x-slot>
            <x-slot name="description">
                Total de agendamentos este mês
            </x-slot>
            <div class="text-3xl font-bold text-info-600">
                {{ $estatisticas['agendamentos_mes'] }}
            </div>
        </x-filament::section>

        <!-- Agendamentos da Semana -->
        <x-filament::section icon="heroicon-o-calendar-days" icon-color="danger">
            <x-slot name="heading">
                Agendamentos (Semana)
            </x-slot>
            <x-slot name="description">
                Últimos 7 dias
            </x-slot>
            <div class="text-3xl font-bold text-danger-600">
                {{ $estatisticas['agendamentos_semana'] }}
            </div>
        </x-filament::section>

        <!-- Receita do Mês -->
        <x-filament::section icon="heroicon-o-currency-dollar" icon-color="success">
            <x-slot name="heading">
                Receita (Mês)
            </x-slot>
            <x-slot name="description">
                Total arrecadado este mês
            </x-slot>
            <div class="text-3xl font-bold text-success-600">
                R$ {{ number_format($estatisticas['receita_mes'], 2, ',', '.') }}
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Métricas Avançadas -->
        <x-filament::section>
            <x-slot name="heading">
                Métricas de Desempenho
            </x-slot>
            <x-slot name="description">
                Indicadores importantes
            </x-slot>
            <div class="space-y-4">
                <div>
                    <div class="text-sm font-medium text-gray-500">Taxa de Confirmação</div>
                    <div class="flex items-center space-x-2">
                        <div class="text-2xl font-bold">{{ $estatisticas['taxa_confirmacao'] }}%</div>
                        @if($estatisticas['taxa_confirmacao'] >= 80)
                            <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-success-500" />
                        @else
                            <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-danger-500" />
                        @endif
                    </div>
                </div>
                
                <div>
                    <div class="text-sm font-medium text-gray-500">Serviço Mais Popular</div>
                    <div class="text-lg font-semibold">{{ $estatisticas['servico_mais_popular'] }}</div>
                </div>
                
                <div>
                    <div class="text-sm font-medium text-gray-500">Barbeiro Mais Ocupado</div>
                    <div class="text-lg font-semibold">{{ $estatisticas['barbeiro_mais_ocupado'] }}</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Comparativo com Mês Anterior -->
        <x-filament::section>
            <x-slot name="heading">
                Comparativo Mensal
            </x-slot>
            <x-slot name="description">
                Receita deste mês vs mês anterior
            </x-slot>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm font-medium text-gray-500">Mês Atual</div>
                    <div class="text-lg font-bold text-success-600">
                        R$ {{ number_format($estatisticas['receita_mes'], 2, ',', '.') }}
                    </div>
                </div>
                
                <div class="flex justify-between items-center">
                    <div class="text-sm font-medium text-gray-500">Mês Anterior</div>
                    <div class="text-lg font-bold text-gray-600">
                        R$ {{ number_format($estatisticas['receita_mes_passado'], 2, ',', '.') }}
                    </div>
                </div>
                
                @php
                    $diferenca = $estatisticas['receita_mes'] - $estatisticas['receita_mes_passado'];
                    $percentual = $estatisticas['receita_mes_passado'] > 0 
                        ? round(($diferenca / $estatisticas['receita_mes_passado']) * 100, 2) 
                        : 0;
                @endphp
                
                <div class="mt-4 p-4 rounded-lg {{ $diferenca >= 0 ? 'bg-success-50' : 'bg-danger-50' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium {{ $diferenca >= 0 ? 'text-success-700' : 'text-danger-700' }}">
                                {{ $diferenca >= 0 ? 'Crescimento' : 'Queda' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Comparado ao mês anterior
                            </div>
                        </div>
                        <div class="text-2xl font-bold {{ $diferenca >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $diferenca >= 0 ? '+' : '' }}{{ number_format($diferenca, 2, ',', '.') }}
                            <div class="text-sm">
                                ({{ $percentual >= 0 ? '+' : '' }}{{ $percentual }}%)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>