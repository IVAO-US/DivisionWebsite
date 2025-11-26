<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;

new 
#[Layout('layouts.app')]
#[Title('Manage Admins')]
class extends Component {
    use Toast;

    public string $search = '';

    // Current user for protection  
    public int $currentUserVid;
    public ?Admin $currentUserAdmin = null;

    private function checkPermissions(): bool
    {
        return $this->currentUserAdmin?->canString('admins_edit_permissions') ?? false;
    }

    public function mount(): void
    {
        $this->currentUserVid = Auth::user()->vid;
        $this->currentUserAdmin = Admin::where('vid', $this->currentUserVid)->first();
    }
    
    // Modal properties
    public bool $addAdminModal = false;
    public int|string $selectedVid = '';
    public string $selectedName = '';
    public string $userSearch = '';
    public bool $isManualEntry = false;
    
    // Add new admin
    public function addAdmin(): void
    {
        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to add administrators');
            return;
        }

        $this->validate([
            'selectedVid' => 'required|integer|min_digits:6',
            'selectedName' => 'required|string|min:2',
        ]);

        // Check if user is already an admin
        if (Admin::where('vid', $this->selectedVid)->exists()) {
            $this->error('This user is already an admin');
            return;
        }

        // Create user if it doesn't exist
        $user = User::where('vid', $this->selectedVid)->first();
        if (!$user) {
            // Split name into first_name and last_name
            $nameParts = explode(' ', trim($this->selectedName), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Create minimal user record
            $user = User::create([
                'vid' => $this->selectedVid,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $this->selectedVid . '@temp.local', // Temporary email, will be updated on SSO login
                'rating_atc' => 1, // Default ATC rating
                'rating_pilot' => 1, // Default Pilot rating
                'country' => 'XX', // Unknown country
                'division' => 'XX', // Unknown division
                'gca' => null,
                'hours_atc' => null,
                'hours_pilot' => null,
                'staff' => null,
            ]);

            // Create user settings
            $user->settings()->create([
                'vid' => $this->selectedVid,
                'allow_notifications' => true,
            ]);
        }

        // Create new admin record
        Admin::create([
            'vid' => $this->selectedVid,
            'permissions' => []
        ]);

        if (!User::where('vid', $this->selectedVid)->whereNotNull('updated_at')->where('updated_at', '>', now()->subMinutes(1))->exists()) {
            $this->success('Admin added successfully (user will be updated on first SSO login)');
        } else {
            $this->success('Admin added successfully');
        }
        
        $this->closeAddAdminModal();
        $this->dispatch('admin-added'); // Refresh the main table
    }
    
    public function updatedSelectedVid(): void
    {
        // Reset name and manual entry flag first
        $this->isManualEntry = false;
        
        if ($this->selectedVid && is_numeric($this->selectedVid) && $this->selectedVid > 0) {
            $user = User::where('vid', $this->selectedVid)->first();
            if ($user) {
                $this->selectedName = $user->full_name;
                $this->isManualEntry = false;
            } else {
                // User doesn't exist - enable manual entry mode
                $this->selectedName = ''; // Clear name for manual entry
                $this->isManualEntry = true;
            }
        } else {
            $this->selectedName = '';
            $this->isManualEntry = false;
        }
    }
    
    #[On('user-selected')]
    public function selectUser(int $vid, string $name): void
    {
        $this->selectedVid = $vid;
        $this->selectedName = $name;
        $this->isManualEntry = false; // User selected from table, not manual entry
    }

    #[On('vid-transfer')]
    public function handleVidTransfer(string $vid): void
    {
        $this->selectedVid = $vid;
        $this->selectedName = '';
        $this->isManualEntry = true;
        $this->userSearch = ''; // Clear search
    }
    
    public function openAddAdminModal(): void
    {
        $this->resetAddAdminForm();
        $this->addAdminModal = true;
    }
    
    public function closeAddAdminModal(): void
    {
        $this->addAdminModal = false;
        $this->resetAddAdminForm();
    }
    
    private function resetAddAdminForm(): void
    {
        $this->selectedVid = '';
        $this->selectedName = '';
        $this->userSearch = '';
        $this->isManualEntry = false;
    }
}; ?>

<div>
    <x-header title="Manage Admins" size="h2" subtitle="App Administrators" class="!mb-5" />

    {{-- Main Content Card --}}
    <x-card title="Active Admins" shadow separator class="mb-8">
        
        <x-slot:menu class="justify-start lg:justify-end pl-5 lg:pl-0 w-32 sm:w-64">
            <div class="flex gap-3">
                <x-input wire:model.live='search' icon="phosphor.magnifying-glass" placeholder="Search" />
                <x-button 
                    icon="phosphor.user-plus" 
                    class="btn-primary" 
                    wire:click="openAddAdminModal"
                    tooltip="Add New Admin"
                />
            </div>
        </x-slot:menu>

        {{-- Admins List Table --}}
        <livewire:admins-list-table :search="$search" wire:key="admin-search-{{ $search }}" />
    </x-card>

    {{-- Add Admin Modal --}}
    <x-modal wire:model="addAdminModal" title="Add New Admin" separator class="backdrop-blur" box-class="w-4/5 max-w-4xl max-h-9/10 mx-auto">

        <x-card subtitle="Search User" shadow separator>
            {{-- User Selection Table --}}
            <div class="mb-6">
                <x-input 
                    wire:model.live="userSearch" 
                    icon="phosphor.magnifying-glass" 
                    placeholder="VID or Name"
                />
                
                @if(strlen($userSearch) >= 3)
                    <livewire:users-list-table :search="$userSearch" wire:key="user-search-{{ $userSearch }}" />
                @else
                    <div class="text-center text-gray-500 py-8">
                        <x-icon name="phosphor.magnifying-glass" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Enter at least 3 characters to search for users</p>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Selected User Form --}}
        @if($selectedVid)
            <x-card subtitle="{{ $isManualEntry ? 'Manual Entry' : 'Selected User' }}">
                <div class="grid grid-cols-2 gap-4">
                    <x-input 
                        label="VID" 
                        wire:model="selectedVid" 
                        readonly 
                    />
                    <x-input 
                        label="Name" 
                        wire:model="selectedName" 
                        :readonly="!$isManualEntry"
                        placeholder="{{ $isManualEntry ? 'Enter full name...' : '' }}"
                    />
                </div>
                @if($isManualEntry)
                    <x-alert title="User not found in database" description="A minimal user record will be created. All user data will be automatically updated when they first log in via SSO." icon="phosphor.warning"  class="alert-warning mt-4" />
                @endif
            </x-card>
        @else
            {{-- Manual VID Entry --}}
            <x-card subtitle="Manual Entry" shadow separator >
                <x-input 
                    label="VID" 
                    wire:model.live.debounce.1000ms="selectedVid" 
                    placeholder="Enter VID manually..."
                    type="number"
                />
                
                {{-- Loading indicator --}}
                <div wire:loading wire:target="selectedVid" class="mt-2 text-sm text-gray-500 flex items-center">
                    <x-icon name="phosphor.spinner" class="w-4 h-4 mr-2 animate-spin" />
                    Checking VID...
                </div>
            </x-card>
        @endif

        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeAddAdminModal" />
            <x-button 
                label="Add Admin" 
                class="btn-primary" 
                wire:click="addAdmin"
            />
        </x-slot:actions>
    </x-modal>
</div>