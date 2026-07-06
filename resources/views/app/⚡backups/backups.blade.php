<div class="space-y-6">
    {{-- Header with Stats --}}
    <div class="bg-white dark:bg-base-200 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Backup Management') }}</h1>
            <x-button
                wire:click="$set('showCreateModal', true)"
                icon="o-plus"
                class="btn-primary">
                {{ __('Create Backup') }}
            </x-button>
        </div>

        {{-- Enhanced Stats Cards with Better Size Display --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-md">
                        <x-icon name="o-archive-box" class="w-6 h-6 text-blue-600 dark:text-blue-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-300">{{ __('Total Backups') }}</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $this->stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-100 dark:border-green-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-800 rounded-md">
                        <x-icon name="o-check-circle" class="w-6 h-6 text-green-600 dark:text-green-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600 dark:text-green-300">{{ __('Completed') }}</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $this->stats['completed'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-100 dark:border-red-800">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-800 rounded-md">
                        <x-icon name="o-x-circle" class="w-6 h-6 text-red-600 dark:text-red-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600 dark:text-red-300">{{ __('Failed') }}</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $this->stats['failed'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-100 dark:border-yellow-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-800 rounded-md">
                        <x-icon name="o-clock" class="w-6 h-6 text-yellow-600 dark:text-yellow-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-300">{{ __('Running') }}</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $this->stats['running'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-100 dark:border-purple-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-md">
                        <x-icon name="o-calendar-days" class="w-6 h-6 text-purple-600 dark:text-purple-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-300">{{ __('Recent (7d)') }}</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $this->stats['recent'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Enhanced Total Size Display --}}
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-base-300 dark:to-base-200 p-4 rounded-lg border border-gray-200 dark:border-base-300">
                <div class="flex items-center">
                    <div class="p-2 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-base-100 dark:to-base-200 rounded-md">
                        <x-icon name="o-server" class="w-6 h-6 text-gray-600 dark:text-gray-300" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Total Storage Used') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            @if($this->stats['total_size'] > 0)
                                @php
                                    $totalSizeMB = $this->stats['total_size'] / (1024*1024);
                                    $totalSizeGB = $totalSizeMB / 1024;
                                @endphp
                                @if($totalSizeGB >= 1)
                                    {{ number_format($totalSizeGB, 2) }} GB
                                @else
                                    {{ number_format($totalSizeMB, 1) }} MB
                                @endif
                            @else
                                0 MB
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format($this->stats['total_size']) }} bytes
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Actions --}}
    <div class="bg-white dark:bg-base-200 rounded-lg shadow p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                {{-- Search --}}
                <x-input
                    wire:model.live="search"
                    placeholder="{{ __('Search backups...') }}"
                    icon="o-magnifying-glass"
                    class="w-full sm:w-64" />

                {{-- Status Filter --}}
                <x-select
                    wire:model.live="statusFilter"
                    :options="[
                        ['id' => 'all', 'name' => __('All Status')],
                        ['id' => 'completed', 'name' => __('Completed')],
                        ['id' => 'running', 'name' => __('Running')],
                        ['id' => 'failed', 'name' => __('Failed')],
                        ['id' => 'pending', 'name' => __('Pending')]
                    ]"
                    option-value="id"
                    option-label="name" />

                {{-- Type Filter --}}
                <x-select
                    wire:model.live="typeFilter"
                    :options="[
                        ['id' => 'all', 'name' => __('All Types')],
                        ['id' => 'manual', 'name' => __('Manual')],
                        ['id' => 'scheduled', 'name' => __('Scheduled')]
                    ]"
                    option-value="id"
                    option-label="name" />
            </div>

            <div class="flex space-x-2">
                @if(count($selectedBackups) > 0)
                    <x-button
                        wire:click="deleteSelected"
                        wire:confirm="{{ __('Are you sure you want to delete the selected backups?') }}"
                        icon="o-trash"
                        class="btn-error">
                        {{ __('Delete Selected') }} ({{ count($selectedBackups) }})
                    </x-button>
                @endif

                <x-button
                    wire:click="showCleanupModal"
                    icon="o-trash"
                    class="btn-warning">
                    {{ __('Cleanup') }}
                </x-button>

                <x-button
                    wire:click="enableScheduledBackups"
                    icon="o-calendar"
                    class="btn-info">
                    {{ __('Schedule Backup') }}
                </x-button>
            </div>
        </div>
    </div>

    {{-- Enhanced Backups Table with Prominent File Sizes --}}
    <div class="bg-white dark:bg-base-200 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-base-300">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <x-checkbox wire:model.live="selectAll" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Backup Details') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            📦 {{ __('File Size & Duration') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Created') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            📥 {{ __('Download & Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-base-200 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->backups as $backup)
                        <tr class="hover:bg-gray-50 dark:hover:bg-base-300 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-checkbox wire:model.live="selectedBackups" value="{{ $backup->id }}" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                            <x-icon name="o-archive-box" class="h-5 w-5 text-blue-600 dark:text-blue-300" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $backup->display_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ ucfirst($backup->type) }} •
                                            {{ is_array($backup->includes) ? implode(', ', $backup->includes) : 'Full' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($backup->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        <x-icon name="o-check-circle" class="w-4 h-4 mr-1" />
                                        {{ __('Completed') }}
                                    </span>
                                @elseif($backup->status === 'running')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                        <x-icon name="o-clock" class="w-4 h-4 mr-1 animate-spin" />
                                        {{ __('Running') }}
                                    </span>
                                @elseif($backup->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                        <x-icon name="o-x-circle" class="w-4 h-4 mr-1" />
                                        {{ __('Failed') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        <x-icon name="o-ellipsis-horizontal-circle" class="w-4 h-4 mr-1" />
                                        {{ __('Pending') }}
                                    </span>
                                @endif
                            </td>
                            {{-- Enhanced File Size Display --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex flex-col space-y-1">
                                    @if($backup->status === 'completed' && $backup->file_size)
                                        {{-- Prominent file size --}}
                                        <div class="font-bold text-lg text-blue-600 dark:text-blue-400 flex items-center">
                                            <x-icon name="o-archive-box" class="w-4 h-4 mr-1" />
                                            {{ $backup->formatted_file_size }}
                                        </div>
                                        {{-- Detailed byte count --}}
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($backup->file_size) }} bytes
                                        </div>
                                    @else
                                        <div class="text-gray-400 dark:text-gray-500 italic">
                                            {{ $backup->status === 'running' ? __('Calculating...') : __('Unknown') }}
                                        </div>
                                    @endif

                                    {{-- Duration with icon --}}
                                    @if($backup->duration)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                            <x-icon name="o-clock" class="w-3 h-3 mr-1" />
                                            {{ $backup->duration }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div>{{ $backup->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $backup->created_at->format('g:i A') }}</div>
                                @if($backup->creator)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('by') }} {{ $backup->creator->name }}</div>
                                @endif
                            </td>
                            {{-- Enhanced Actions with Prominent Download --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($backup->status === 'completed')
                                        @if($backup->exists())
                                            {{-- Prominent Download Button --}}
                                            <div class="flex flex-col items-center space-y-1">
                                                <x-button
                                                    wire:click="downloadBackup({{ $backup->id }})"
                                                    icon="o-arrow-down-tray"
                                                    class="btn-sm btn-success text-white font-medium"
                                                    tooltip="{{ __('Download') }} {{ $backup->formatted_file_size }} {{ __('backup file') }}">
                                                    📥 {{ __('Download') }}
                                                </x-button>
                                                <span class="text-xs text-green-600 dark:text-green-400 font-bold">
                                                    {{ $backup->formatted_file_size }}
                                                </span>
                                            </div>
                                        @else
                                            {{-- File Missing Warning --}}
                                            <x-button
                                                icon="o-exclamation-triangle"
                                                class="btn-sm btn-warning"
                                                tooltip="{{ __('Backup file not found on disk') }}"
                                                disabled>
                                                📄 {{ __('Missing') }}
                                            </x-button>
                                        @endif
                                    @elseif($backup->status === 'running')
                                        {{-- Processing Indicator --}}
                                        <x-button
                                            icon="o-arrow-path"
                                            class="btn-sm btn-info loading"
                                            tooltip="{{ __('Backup in progress...') }}"
                                            disabled>
                                            ⏳ {{ __('Processing') }}
                                        </x-button>
                                    @elseif($backup->status === 'pending')
                                        {{-- Pending Indicator --}}
                                        <x-button
                                            icon="o-clock"
                                            class="btn-sm btn-ghost"
                                            tooltip="{{ __('Backup queued for processing') }}"
                                            disabled>
                                            ⏰ {{ __('Queued') }}
                                        </x-button>
                                    @endif

                                    {{-- Error Info Button --}}
                                    @if($backup->status === 'failed' && $backup->error_message)
                                        <x-button
                                            icon="o-exclamation-triangle"
                                            class="btn-sm btn-ghost text-error"
                                            tooltip="{{ $backup->error_message }}" />
                                    @endif

                                    {{-- Delete Button --}}
                                    <x-button
                                        wire:click="deleteBackup({{ $backup->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this backup? This action cannot be undone.') }}"
                                        icon="o-trash"
                                        class="btn-sm btn-ghost text-error hover:btn-error hover:text-white"
                                        tooltip="{{ __('Delete Backup') }}" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-icon name="o-archive-box" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No backups found') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating your first backup.') }}</p>
                                <div class="mt-6">
                                    <x-button
                                        wire:click="$set('showCreateModal', true)"
                                        icon="o-plus"
                                        class="btn-primary">
                                        🚀 {{ __('Create Your First Backup') }}
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->backups->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-base-300">
                {{ $this->backups->links() }}
            </div>
        @endif
    </div>

    {{-- Create Backup Modal --}}
    <x-modal wire:model="showCreateModal" title="{{ __('Create New Backup') }}" persistent class="backdrop-blur">
        <div class="space-y-4">
            <div>
                <x-radio
                    wire:model="backupType"
                    :options="[
                        ['id' => 'both', 'name' => __('📦 Database + Files'), 'description' => __('Complete backup including database and application files')],
                        ['id' => 'database', 'name' => __('🗃️ Database Only'), 'description' => __('Backup only the database content')],
                        ['id' => 'files', 'name' => __('📁 Files Only'), 'description' => __('Backup only application files')]
                    ]"
                    option-value="id"
                    option-label="name"
                    option-sub-label="description" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('showCreateModal', false)" />
            <x-button
                label="🚀 {{ __('Create Backup') }}"
                wire:click="createBackup"
                class="btn-primary"
                spinner="createBackup" />
        </x-slot:actions>
    </x-modal>

    {{-- Cleanup Modal --}}
    <x-modal wire:model="showCleanupModal" title="🗑️ {{ __('Cleanup Old Backups') }}" persistent class="backdrop-blur">
        <div class="space-y-4">
            <x-input
                wire:model="cleanupDays"
                label="{{ __('Delete backups older than (days)') }}"
                type="number"
                min="1" />

            <x-select
                wire:model="cleanupType"
                label="{{ __('Backup type to cleanup') }}"
                :options="[
                    ['id' => 'all', 'name' => __('All backup types')],
                    ['id' => 'manual', 'name' => __('Manual backups only')],
                    ['id' => 'scheduled', 'name' => __('Scheduled backups only')]
                ]"
                option-value="id"
                option-label="name" />
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('showCleanupModal', false)" />
            <x-button
                label="🗑️ {{ __('Cleanup Backups') }}"
                wire:click="cleanupOldBackups"
                class="btn-warning"
                spinner="cleanupOldBackups" />
        </x-slot:actions>
    </x-modal>
</div>
