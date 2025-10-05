<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

use App\Models\Tour;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.app')]
#[Title('Manage Tours')]
class extends Component {
    use Toast;

    // Search
    public string $search = '';

    // Current user for protection
    public int $currentUserVid;
    public ?Admin $currentUserAdmin = null;

    // Bento debug
    public bool $showBentoDebug = false;
    public ?string $debugSetupId = null;

    /**
     * Check permissions
     */
    private function checkPermissions(): bool
    {
        return $this->currentUserAdmin?->canString('fltops_tours') ?? false;
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
     * Toggle bento debug
     */
    public function toggleBentoDebug(): void
    {
        $this->showBentoDebug = !$this->showBentoDebug;
        
        if ($this->showBentoDebug) {
            $this->debugSetupId = $this->generateRandomSetupId();
        }
    }

    /**
     * Generate random setup ID for bento grid
     */
    private function generateRandomSetupId(): string
    {
        $timestamp = time();
        $randomBytes = random_bytes(6);
        $hash = substr(md5($timestamp . bin2hex($randomBytes)), 0, 12);
        
        return strtoupper($hash);
    }

    /**
     * Regenerate bento layout
     */
    public function regenerateBentoLayout(): void
    {
        $this->debugSetupId = $this->generateRandomSetupId();
        $this->success('New layout generated!');
    }

    /**
     * Get tours data for bento grid
     */
    public function getToursDataForBento(): array
    {
        return Tour::all()
            ->map(fn($tour) => $tour->toBentoFormat())
            ->toArray();
    }
}; 
?>

<div>
    <x-header title="Manage Tours" size="h2" subtitle="Display divisional tours on the home page" class="!mb-5" />

    {{-- Main Content Card --}}
    <x-card title="Displayed Tours" shadow separator class="mb-8">
        
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
                    Add Tour
                </x-button>
            </div>
        </x-slot:menu>

        {{-- Tours Table Component --}}
        <livewire:protected.admin.flight-ops.tours_list-table :search="$search" />
        
    </x-card>

    {{-- Bento Debug Card --}}
    <x-card title="Bento Grid Debug" subtitle="Generate and preview bento grid layouts" shadow separator class="border-l-4 border-l-secondary">
        <x-slot:menu>
            <x-button 
                :label="$showBentoDebug ? 'Hide' : 'Show'"
                :icon="$showBentoDebug ? 'phosphor.eye-slash' : 'phosphor.eye'"
                class="btn-sm btn-secondary btn-outline"
                wire:click="toggleBentoDebug"
            />
        </x-slot:menu>

        @if($showBentoDebug)
            <div class="space-y-4">
                {{-- Setup ID Display --}}
                <div class="bg-base-200 p-4 rounded-lg">
                    <label class="text-sm font-semibold text-base-content mb-2 block">Setup ID (Seed)</label>
                    <div class="flex items-center gap-2">
                        <code class="text-lg font-mono bg-base-300 px-3 py-2 rounded flex-1">{{ $debugSetupId }}</code>
                        <x-button 
                            icon="phosphor.arrow-clockwise"
                            class="btn-sm btn-primary"
                            wire:click="regenerateBentoLayout"
                            tooltip="Generate new layout"
                        />
                    </div>
                    <p class="text-sm text-base-content mt-2">
                        Use this Setup ID in your Bento Component to reproduce this exact layout<br>
                        <br>
                        The webmaster must alter the <b>homepage_components-flight-ops.blade.php</b> file.<br>
                        He must alter the <b>$bentoSetupId</b> property at the top of the component file.
                    </p>
                </div>

                {{-- Bento Grid Preview --}}
                @if(count($this->getToursDataForBento()) > 0)
                    <div class="bg-base-200 p-6 rounded-lg">
                        <h4 class="text-sm font-semibold text-base-content/80 mb-4">Layout Preview</h4>
                        <livewire:app_component-bento-grid 
                            :images="$this->getToursDataForBento()" 
                            :setup-id="$debugSetupId"
                            wire:key="bento-debug-{{ $debugSetupId }}"
                        />
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/60">
                        <x-icon name="phosphor.airplane-takeoff" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>
                            No tours available to preview.<br>
                            Create some tours first!
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </x-card>
</div>