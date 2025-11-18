<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use App\Models\VirtualAirline;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;


new 
#[Layout('components.layouts.app')]
#[Title('Manage Virtual Airlines')]
class extends Component {

    // Search query
    public string $search = '';

    /**
     * Check permissions
     */
    private function checkPermissions(): bool
    {
        return $this->currentUserAdmin?->canString('fltops_va') ?? false;
    }

    /**
     * Mount
     */
    public function mount(): void
    {
        $this->currentUserVid = Auth::user()->vid;
        $this->currentUserAdmin = Admin::where('vid', $this->currentUserVid)->first();

        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to manage tours');
            $this->redirect(route('admin.index'));
        }
    }

    /**
     * Get virtual airlines count
     */
    public function getVirtualAirlinesCountProperty(): int
    {
        return VirtualAirline::count();
    }
}; 
?>

<div>
    <x-header title="Manage Virtual Airlines" size="h2" subtitle="Display certified partner virtual airlines" class="!mb-5" />

    {{-- Main Content Card --}}
    <x-card title="Certified Virtual Airlines" shadow separator class="mb-8">
        
        <x-slot:menu class="justify-start lg:justify-end pl-5 lg:pl-0 w-32 sm:w-64">
            <div class="flex gap-3">
                <x-input 
                    wire:model.live.debounce.500ms="search" 
                    icon="phosphor.magnifying-glass" 
                    placeholder="Search..." 
                    class="flex-1"
                />
                <x-button 
                    icon="phosphor.plus-circle" 
                    class="btn-primary"
                    @click="$dispatch('open-create-modal')"
                    responsive
                >
                    Add VA
                </x-button>
            </div>
        </x-slot:menu>

        {{-- Virtual Airlines Table Component --}}
        <livewire:virtual-airlines-list-table :search="$search" />
        
    </x-card>
</div>