<x-filament-panels::page>
    <x-filament::header :actions="$this->getCachedHeaderActions()">
        <x-slot name="heading">
            Dashboard
        </x-slot>
        <x-slot name="description">
            Bem-vindo ao sistema de gestão de barbearias
        </x-slot>
    </x-filament::header>

    <div class="space-y-6">
        @if ($this->hasHeaderWidgets())
            <x-filament-widgets::widgets
                :columns="$this->getColumns()"
                :data="$this->getWidgetData()"
                :widgets="$this->getHeaderWidgets()"
            />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if ($this->hasFooterWidgets())
                <x-filament-widgets::widgets
                    :columns="$this->getColumns()"
                    :data="$this->getWidgetData()"
                    :widgets="$this->getFooterWidgets()"
                />
            @endif
        </div>

        <!-- Ações Rápidas -->
        <x-filament::section>
            <x-slot name="heading">
                Ações Rápidas
            </x-slot>
            <x-slot name="description">
                Acesse rapidamente as funcionalidades principais
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::button 
                    icon="heroicon-o-calendar" 
                    color="primary" 
                    tag="a"
                    href="{{ route('filament.admin.resources.agendamentos.create') }}">
                    Novo Agendamento
                </x-filament::button>
                
                <x-filament::button 
                    icon="heroicon-o-user-plus" 
                    color="success" 
                    tag="a"
                    href="{{ route('filament.admin.resources.clientes.create') }}">
                    Novo Cliente
                </x-filament::button>
                
                <x-filament::button 
                    icon="heroicon-o-scissors" 
                    color="warning" 
                    tag="a"
                    href="{{ route('filament.admin.resources.servicos.create') }}">
                    Novo Serviço
                </x-filament::button>
                
                <x-filament::button 
                    icon="heroicon-o-building-storefront" 
                    color="danger" 
                    tag="a"
                    href="{{ route('filament.admin.resources.barbearias.create') }}">
                    Nova Barbearia
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>