<div>
    <x-header title="Expense Categories" subtitle="Manage expense category definitions" separator>
        <x-slot:actions>
            <x-button icon="o-plus" label="New Category" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($categories as $cat)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm hover:shadow-md transition-all flex flex-col gap-4" wire:key="cat-{{ $cat->id }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $cat->color }}22; color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base-content">{{ $cat->name }}</h3>
                            <p class="text-xs text-base-content/50">{{ $cat->expenses_count }} expenses</p>
                        </div>
                    </div>
                    <label class="swap swap-rotate">
                        <input type="checkbox" wire:click="toggleActive({{ $cat->id }})" {{ $cat->is_active ? 'checked' : '' }} />
                        <div class="swap-on badge badge-success badge-sm font-bold">Active</div>
                        <div class="swap-off badge badge-ghost badge-sm font-bold">Inactive</div>
                    </label>
                </div>
                @if($cat->description)
                    <p class="text-sm text-base-content/60">{{ $cat->description }}</p>
                @endif
                <div class="flex items-center gap-2 pt-2 border-t border-base-content/5">
                    <div class="w-5 h-5 rounded-md border border-base-content/10" style="background-color: {{ $cat->color }}"></div>
                    <code class="text-xs text-base-content/40">{{ $cat->color }}</code>
                    <div class="flex-1"></div>
                    <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="openModal({{ $cat->id }})" tooltip="Edit" />
                    <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="delete({{ $cat->id }})" wire:confirm="Delete this category?" tooltip="Delete" />
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-tag" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No categories yet</h3>
                <p class="text-base-content/50 text-sm mb-4">Create your first expense category to get started.</p>
                <x-button icon="o-plus" label="Create Category" class="btn-primary" wire:click="openModal()" />
            </div>
        @endforelse
    </div>

    <x-modal wire:model="modal" title="{{ $editingId ? 'Edit Category' : 'New Category' }}" class="backdrop-blur-sm">
        <div class="space-y-4">
            <x-input wire:model="name" label="Category Name *" placeholder="e.g. Event Expenses" class="input-bordered rounded-xl" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label"><span class="label-text font-semibold">Color *</span></label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model="color" class="w-12 h-10 rounded-lg border border-base-content/10 cursor-pointer p-1" />
                        <x-input wire:model="color" class="input-sm input-bordered rounded-xl font-mono" placeholder="#6366f1" />
                    </div>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">Icon *</span></label>
                    <select wire:model="icon" class="select select-sm select-bordered w-full rounded-xl">
                        @foreach($iconOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-xl border border-base-content/5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $color }}22; color: {{ $color }}">
                    <x-icon :name="$icon" class="w-5 h-5" />
                </div>
                <span class="font-semibold text-base-content">{{ $name ?: 'Category Preview' }}</span>
            </div>

            <x-textarea wire:model="description" label="Description (optional)" rows="2" class="textarea-bordered rounded-xl" />

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" wire:model="is_active" class="checkbox checkbox-primary" />
                <span class="label-text font-semibold">Active (visible in expense forms)</span>
            </label>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('modal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? 'Update' : 'Create' }}" icon="o-check" wire:click="save" class="bg-gradient-to-r from-primary to-secondary text-white border-none font-bold" />
        </x-slot:actions>
    </x-modal>
</div>
