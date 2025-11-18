<?php
use Livewire\Component;
use Livewire\WithPagination;

use App\Models\User;
use App\Models\Admin;

new class extends Component {
    use WithPagination;

    public string $search = '';

    /* Table headers */
    public array $headers = [
        ['key' => 'vid', 'label' => 'VID', 'class' => 'font-bold max-w-20'],
        ['key' => 'name', 'label' => 'Name', 'class' => 'text-left', 'sortable' => false],
        ['key' => 'division', 'label' => 'Division', 'class' => 'text-left max-w-52', 'sortable' => false],
    ];
    public array $sortBy = ['column' => 'vid', 'direction' => 'asc'];

    public function mount(string $search = '')
    {
        $this->search = $search;
    }

    public function updated($property)
    {
        if ($property === 'search') {
            $this->resetPage();
        }
    }

    public function selectUser(int $vid, string $name): void
    {
        $this->dispatch('user-selected', vid: $vid, name: $name)->to('protected.admin.manage');;
    }

    public function transferVidToManualEntry(): void
    {
        if (is_numeric($this->search)) {
            $this->dispatch('vid-transfer', vid: $this->search)->to('protected.admin.manage');
        }
    }

    public function with(): array
    {
        $users = collect();
        
        if (strlen($this->search) >= 3) {
            $users = User::query()
                ->where(function ($query) {
                    $query->where('vid', 'like', '%' . $this->search . '%')
                          ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                })
                ->orderBy('vid')
                ->paginate(10);
        }

        return [
            'users' => $users,
        ];
    }
}; ?>

<div>
    @if($users->count() > 0)
        <x-table :rows="$users" :headers="$headers" :sortBy="$sortBy"  no-hover class="mx-auto mt-4">
            {{-- Table Rows --}}
            @scope('cell_vid', $user)
                {{ $user->vid }}
            @endscope

            @scope('cell_name', $user)
                <div class="font-medium">{{ $user->full_name }}</div>
            @endscope

            @scope('cell_division', $user)
                <div class="flex items-center gap-2">
                    <span class="uppercase text-sm">{{ $user->division }}</span>
                </div>
            @endscope

            @scope('actions', $user)
                <x-button 
                    icon="phosphor.check" 
                    class="btn-primary btn-sm" 
                    wire:click="selectUser({{ $user->vid }}, '{{ addslashes($user->full_name) }}')"
                    tooltip="Select"
                />
            @endscope

        </x-table>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    @elseif(strlen($search) >= 3)
        <div class="text-center text-gray-500 py-8">
            <x-icon name="phosphor.users" class="w-12 h-12 mx-auto mb-2 opacity-50" />
            <p>No users found matching your search criteria</p>
            <p class="text-sm">Try adjusting your search terms</p>
            @if(is_numeric($this->search))
                <div class="mt-4 p-3 bg-base-200 border border-base-200 rounded-lg">
                    <p class="text-sm text-base-content">
                        <span class="font-semibold">VID detected:</span> Add this user manually?
                    </p>
                    <x-button 
                        label="Transfer VID to Manual Entry" 
                        class="btn-primary btn-sm mt-2" 
                        wire:click="transferVidToManualEntry"
                    />
                </div>
            @endif
        </div>
    @endif
</div>