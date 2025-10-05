<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;

use App\Models\Tour;
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
    public ?Tour $editingTour = null;
    
    #[Validate('required|string|min:3|max:255')]
    public string $title = '';
    
    #[Validate('required|string|min:10')]
    public string $description = '';
    
    #[Validate('required|url')]
    public string $link = '';
    
    #[Validate('required|url')]
    public string $banner = '';
    
    public bool $bentoPriority = false;

    // Delete confirmation
    public bool $deleteModal = false;
    public ?Tour $deletingTour = null;

    // Table headers
    public array $headers = [
        ['key' => 'id', 'label' => 'ID', 'class' => 'font-bold max-w-20'],
        ['key' => 'title', 'label' => 'Title', 'class' => 'text-left'],
        ['key' => 'link', 'label' => 'Link', 'class' => 'text-left w-40', 'sortable' => false],
        ['key' => 'bento_priority', 'label' => 'Priority', 'class' => 'text-left', 'sortable' => false],
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
     * Get tours
     */
    public function getToursProperty()
    {
        return Tour::query()
            ->when($this->search, function($q) {
                return $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->get();
    }

    /**
     * Refresh table on events
     */
    #[On('tour-created')]
    #[On('tour-updated')]
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
        $this->editingTour = null;
        $this->editModal = true;
    }

    /**
     * Open edit modal
     */
    public function edit(int $tourId): void
    {
        $this->editingTour = Tour::find($tourId);
        
        if (!$this->editingTour) {
            $this->error('Tour not found');
            return;
        }

        $this->title = $this->editingTour->title;
        $this->description = $this->editingTour->description;
        $this->link = $this->editingTour->link;
        $this->banner = $this->editingTour->banner;
        $this->bentoPriority = $this->editingTour->bento_priority;
        
        $this->editModal = true;
    }

    /**
     * Save tour (create or update)
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'banner' => $this->banner,
            'bento_priority' => $this->bentoPriority,
        ];

        if ($this->editingTour) {
            $this->editingTour->update($data);
            $this->success('Tour updated successfully');
            $this->dispatch('tour-updated');
        } else {
            Tour::create($data);
            $this->success('Tour created successfully');
            $this->dispatch('tour-created');
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
        $this->editingTour = null;
        $this->title = '';
        $this->description = '';
        $this->link = '';
        $this->banner = '';
        $this->bentoPriority = false;
        $this->resetValidation();
    }

    /**
     * Confirm delete
     */
    public function confirmDelete(int $tourId): void
    {
        $this->deletingTour = Tour::find($tourId);
        
        if (!$this->deletingTour) {
            $this->error('Tour not found');
            return;
        }

        $this->deleteModal = true;
    }

    /**
     * Delete tour
     */
    public function delete(): void
    {
        if (!$this->deletingTour) {
            return;
        }

        $tourTitle = $this->deletingTour->title;
        $this->deletingTour->delete();
        
        $this->success("Tour '{$tourTitle}' deleted successfully");
        $this->dispatch('tour-updated');
        $this->closeDeleteModal();
    }

    /**
     * Close delete modal
     */
    public function closeDeleteModal(): void
    {
        $this->deleteModal = false;
        $this->deletingTour = null;
    }

    /**
     * Volt with() method
     */
    public function with(): array
    {
        return [
            'tours' => $this->tours,
        ];
    }
}; 
?>

<div>
    {{-- Tours Table --}}
    <x-table 
        :headers="$headers" 
        :rows="$tours" 
        :sortBy="$sortBy" 
        no-hover
        class="sm:overflow-x-clip mx-auto">
    >
        @scope('cell_id', $tour)
            <span class="badge badge-neutral">{{ $tour->id }}</span>
        @endscope

        @scope('cell_title', $tour)
            <div class="flex items-center gap-3">
                <img 
                    src="{{ $tour->banner }}" 
                    alt="{{ $tour->title }}"
                    class="w-16 h-10 object-cover rounded"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2260%22%3E%3Crect width=%22100%22 height=%2260%22 fill=%22%23ddd%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2212%22%3ENo Image%3C/text%3E%3C/svg%3E'"
                >
                <div>
                    <div class="font-semibold">{{ $tour->title }}</div>
                    <div class="text-xs text-base-content/60 line-clamp-1">{{ Str::limit($tour->description, 100) }}</div>
                </div>
            </div>
        @endscope

        @scope('cell_link', $tour)
            <x-button 
                label="Tour page"
                link="{{ $tour->link }}"
                external
                class="btn btn-secondary btn-outline btn-sm" 
            />
        @endscope

        @scope('cell_bento_priority', $tour)
            @if($tour->bento_priority)
                <x-badge value="Priority" class="badge-accent" />
            @else
                <x-badge value="Normal" class="badge-neutral" />
            @endif
        @endscope

        @scope('cell_actions', $tour)
            <div class="flex gap-2 justify-end">
                <x-button 
                    icon="phosphor.pen" 
                    class="btn-outline btn-sm btn-secondary"
                    wire:click="edit({{ $tour->id }})"
                />
                <x-button 
                    icon="phosphor.trash" 
                    class="btn-outline btn-error btn-sm"
                    wire:click="confirmDelete({{ $tour->id }})"
                />
            </div>
        @endscope
    </x-table>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="editModal" :title="$editingTour ? 'Edit Tour' : 'Create New Tour'" subtitle="Manage tour details" class="backdrop-blur" box-class="w-4/5 max-w-4xl max-h-9/10 mx-auto">
        <div class="space-y-4">
            <x-input 
                label="Title" 
                wire:model="title" 
                placeholder="Tour title"
                icon="phosphor.text-aa"
                required
            />

            <x-textarea 
                label="Description" 
                wire:model="description" 
                placeholder="Tour description"
                rows="4"
                required
            />

            <x-input 
                label="Link" 
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

            <x-checkbox 
                label="Bento Priority (Larger tiles in grid)" 
                wire:model="bentoPriority"
            />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeEditModal" />
            <x-button 
                :label="$editingTour ? 'Update' : 'Create'" 
                class="btn-primary" 
                wire:click="save"
                spinner
            />
        </x-slot:actions>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="deleteModal" title="Confirm Deletion" class="backdrop-blur">
        @if($deletingTour)
            <div class="space-y-4">
                <p class="text-base-content">
                    Are you sure you want to delete the tour <strong>{{ $deletingTour->title }}</strong>?
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