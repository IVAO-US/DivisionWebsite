<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;

use App\Models\VirtualAirline;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    // Search
    public string $search = '';

    // Current user
    public int $currentUserVid;
    public ?Admin $currentUserAdmin = null;

    // Create/Edit modal
    public bool $editModal = false;
    public ?VirtualAirline $editingVA = null;
    
    #[Validate('required|string|min:3|max:255')]
    public string $name = '';
    
    #[Validate('required|string|size:3|regex:/^[A-Z]{3}$/')]
    public string $icaoCode = '';
    
    #[Validate('required|string')]
    public string $hubsInput = '';
    
    #[Validate('required|string|min:10')]
    public string $description = '';
    
    #[Validate('required|url')]
    public string $link = '';
    
    #[Validate('required|url')]
    public string $banner = '';

    // Delete confirmation
    public bool $deleteModal = false;
    public ?VirtualAirline $deletingVA = null;

    // Table headers
    public array $headers = [
        ['key' => 'id', 'label' => 'ID', 'class' => 'font-bold max-w-20'],
        ['key' => 'name', 'label' => 'Virtual Airline', 'class' => 'text-left'],
        ['key' => 'icao_hubs', 'label' => 'ICAO / Hubs', 'class' => 'text-left', 'sortable' => false],
        ['key' => 'link', 'label' => 'Link', 'class' => 'text-left w-40', 'sortable' => false],
        ['key' => 'actions', 'label' => '', 'class' => 'text-right', 'sortable' => false],
    ];
    
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    /**
     * Mount
     */
    public function mount($search = ''): void
    {
        $this->search = $search;
        $this->currentUserVid = Auth::user()->vid;
        $this->currentUserAdmin = Admin::where('vid', $this->currentUserVid)->first();
    }

    /**
     * Get virtual airlines
     */
    public function getVirtualAirlinesProperty()
    {
        return VirtualAirline::query()
            ->when($this->search, function($q) {
                return $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('icao_code', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->get();
    }

    /**
     * Refresh table on events
     */
    #[On('va-created')]
    #[On('va-updated')]
    public function refreshTable(): void
    {
        $this->mount($this->search);
    }

    /**
     * Open create modal
     */
    #[On('open-create-modal')]
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingVA = null;
        $this->editModal = true;
    }

    /**
     * Open edit modal
     */
    public function edit(int $vaId): void
    {
        $this->editingVA = VirtualAirline::find($vaId);
        
        if (!$this->editingVA) {
            $this->error('Virtual Airline not found');
            return;
        }

        $this->name = $this->editingVA->name;
        $this->icaoCode = $this->editingVA->icao_code;
        $this->hubsInput = is_array($this->editingVA->hubs) ? implode(', ', $this->editingVA->hubs) : '';
        $this->description = $this->editingVA->description;
        $this->link = $this->editingVA->link;
        $this->banner = $this->editingVA->banner;
        
        $this->editModal = true;
    }

    /**
     * Save virtual airline (create or update)
     */
    public function save(): void
    {
        $this->validate();

        // Parse hubs from comma-separated string
        $hubs = array_map('trim', explode(',', $this->hubsInput));
        $hubs = array_filter($hubs); // Remove empty values
        $hubs = array_map('strtoupper', $hubs); // Convert to uppercase

        // Validate ICAO codes format (4 characters each)
        foreach ($hubs as $hub) {
            if (!preg_match('/^[A-Z]{4}$/', $hub)) {
                $this->error("Invalid ICAO code format: {$hub}. Must be 4 uppercase letters.");
                return;
            }
        }

        $data = [
            'name' => $this->name,
            'icao_code' => strtoupper($this->icaoCode),
            'hubs' => $hubs,
            'description' => $this->description,
            'link' => $this->link,
            'banner' => $this->banner,
        ];

        if ($this->editingVA) {
            $this->editingVA->update($data);
            $this->success('Virtual Airline updated successfully');
            $this->dispatch('va-updated');
        } else {
            VirtualAirline::create($data);
            $this->success('Virtual Airline created successfully');
            $this->dispatch('va-created');
        }

        $this->closeEditModal();
    }

    /**
     * Close edit modal
     */
    public function closeEditModal(): void
    {
        $this->editModal = false;
        $this->resetForm();
    }

    /**
     * Reset form
     */
    private function resetForm(): void
    {
        $this->editingVA = null;
        $this->name = '';
        $this->icaoCode = '';
        $this->hubsInput = '';
        $this->description = '';
        $this->link = '';
        $this->banner = '';
        $this->resetValidation();
    }

    /**
     * Confirm delete
     */
    public function confirmDelete(int $vaId): void
    {
        $this->deletingVA = VirtualAirline::find($vaId);
        
        if (!$this->deletingVA) {
            $this->error('Virtual Airline not found');
            return;
        }

        $this->deleteModal = true;
    }

    /**
     * Delete virtual airline
     */
    public function delete(): void
    {
        if (!$this->deletingVA) {
            return;
        }

        $vaName = $this->deletingVA->name;
        $this->deletingVA->delete();
        
        $this->success("Virtual Airline '{$vaName}' deleted successfully");
        $this->dispatch('va-updated');
        $this->closeDeleteModal();
    }

    /**
     * Close delete modal
     */
    public function closeDeleteModal(): void
    {
        $this->deleteModal = false;
        $this->deletingVA = null;
    }

    /**
     * Volt with() method
     */
    public function with(): array
    {
        return [
            'virtualAirlines' => $this->virtualAirlines,
        ];
    }
}; 
?>

<div>
    {{-- Virtual Airlines Table --}}
    <x-table 
        :headers="$headers" 
        :rows="$virtualAirlines" 
        :sortBy="$sortBy" 
        no-hover
        class="sm:overflow-x-clip mx-auto"
    >
        @scope('cell_id', $va)
            <span class="badge badge-neutral">{{ $va->id }}</span>
        @endscope

        @scope('cell_name', $va)
            <div class="flex items-center gap-3">
                <img 
                    src="{{ $va->banner }}" 
                    alt="{{ $va->name }}"
                    class="w-16 h-10 object-cover rounded"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2260%22%3E%3Crect width=%22100%22 height=%2260%22 fill=%22%23ddd%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2212%22%3ENo Image%3C/text%3E%3C/svg%3E'"
                >
                <div>
                    <div class="font-semibold">{{ $va->name }}</div>
                    <div class="text-xs text-base-content/60 line-clamp-1">{{ Str::limit($va->description, 100) }}</div>
                </div>
            </div>
        @endscope

        @scope('cell_icao_hubs', $va)
            <div class="space-y-1">
                <x-badge value="{{ $va->icao_code }}" class="badge-primary font-mono" />
                @if(!empty($va->hubs) && is_array($va->hubs))
                    <div class="flex flex-wrap gap-1">
                        @foreach($va->hubs as $hub)
                            <x-badge value="{{ $hub }}" class="badge-neutral badge-sm font-mono" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endscope

        @scope('cell_link', $va)
            <x-button 
                label="Website"
                link="{{ $va->link }}"
                external
                class="btn btn-secondary btn-outline btn-sm" 
            />
        @endscope

        @scope('cell_actions', $va)
            <div class="flex gap-2 justify-end">
                <x-button 
                    icon="phosphor.pen" 
                    class="btn-outline btn-sm btn-secondary"
                    wire:click="edit({{ $va->id }})"
                />
                <x-button 
                    icon="phosphor.trash" 
                    class="btn-outline btn-error btn-sm"
                    wire:click="confirmDelete({{ $va->id }})"
                />
            </div>
        @endscope
    </x-table>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="editModal" :title="$editingVA ? 'Edit Virtual Airline' : 'Create New Virtual Airline'" subtitle="Manage virtual airline details" class="backdrop-blur" box-class="w-4/5 max-w-4xl max-h-9/10 mx-auto">
        <div class="space-y-4">
            <x-input 
                label="Name" 
                wire:model="name" 
                placeholder="Virtual Airline name"
                icon="phosphor.airplane-takeoff"
                required
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="ICAO Code" 
                    wire:model="icaoCode" 
                    placeholder="ABC"
                    icon="phosphor.identification-badge"
                    hint="3-letter code (e.g., AFR, KLM)"
                    maxlength="3"
                    required
                />

                <x-input 
                    label="Hubs (ICAO Codes)" 
                    wire:model="hubsInput" 
                    placeholder="LFPG, EHAM, KJFK"
                    icon="phosphor.map-pin"
                    hint="Comma-separated 4-letter ICAO codes"
                    required
                />
            </div>

            <x-textarea 
                label="Description" 
                wire:model="description" 
                placeholder="Virtual airline description"
                rows="4"
                required
            />

            <x-input 
                label="Website Link" 
                wire:model="link" 
                placeholder="https://example.com"
                icon="phosphor.link"
                required
            />

            <div>
                <x-input 
                    label="Banner Image URL" 
                    wire:model.live.debounce.500ms="banner" 
                    placeholder="https://example.com/image.jpg"
                    icon="phosphor.image"
                    hint="Use assets website to upload your banners"
                    required
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeEditModal" />
            <x-button 
                :label="$editingVA ? 'Update' : 'Create'" 
                class="btn-primary" 
                wire:click="save"
                spinner
            />
        </x-slot:actions>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="deleteModal" title="Confirm Deletion" class="backdrop-blur">
        @if($deletingVA)
            <div class="space-y-4">
                <p class="text-base-content">
                    Are you sure you want to delete the virtual airline <strong>{{ $deletingVA->name }}</strong> ({{ $deletingVA->icao_code }})?
                </p>
                <x-alert icon="phosphor.warning" class="alert-warning">
                    This action cannot be undone.
                </x-alert>
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeDeleteModal" />
            <x-button 
                label="Delete" 
                class="btn-error" 
                wire:click="delete"
                spinner
            />
        </x-slot:actions>
    </x-modal>
</div>