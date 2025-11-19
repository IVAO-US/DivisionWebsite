<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

use App\Models\Tour;
use App\Models\Admin;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;

new 
#[Layout('layouts.app')]
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
            // Load current saved seed or generate new one
            $savedSeed = AppSetting::get('homepage_tours_bento_seed');
            $this->debugSetupId = $savedSeed ?? $this->generateRandomSetupId();
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
     * Save current seed to database
     */
    public function saveBentoSeed(): void
    {
        if (empty($this->debugSetupId)) {
            $this->error('No layout to save. Please generate a layout first.');
            return;
        }

        AppSetting::set('homepage_tours_bento_seed', $this->debugSetupId, 'string');
        
        $this->success('Bento layout saved successfully! This layout will now be displayed on the homepage.');
    }

    /**
     * Get current saved seed from database
     */
    public function getCurrentSavedSeed(): ?string
    {
        return AppSetting::get('homepage_tours_bento_seed');
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
        <livewire:tours-list-table :search="$search" />
        
    </x-card>

    {{-- Bento Debug Card --}}
    <x-card title="Bento Grid Layout Manager" subtitle="Generate, preview and save bento grid layouts for the homepage" shadow separator class="border-l-4 border-l-secondary">
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
                {{-- Current Saved Seed Info --}}
                @if($currentSavedSeed = $this->getCurrentSavedSeed())
                    <x-alert title="Active Homepage Layout" icon="phosphor.check-circle" class="alert-success">
                        The current homepage is using seed: <code class="font-mono bg-success/20 px-2 py-1 rounded">{{ $currentSavedSeed }}</code>
                    </x-alert>
                @else
                    <x-alert title="No Saved Layout" icon="phosphor.warning" class="alert-warning">
                        No layout has been saved yet. The homepage is using the default fallback seed.
                    </x-alert>
                @endif

                {{-- Setup ID Display --}}
                <div class="bg-base-200 p-4 rounded-lg">
                    <label class="text-sm font-semibold mb-2 block">Setup ID (Seed)</label>
                    <div class="flex items-center gap-2">
                        <code class="text-lg font-mono bg-base-300 px-3 py-2 rounded flex-1">{{ $debugSetupId }}</code>
                        <x-button 
                            icon="phosphor.arrow-clockwise"
                            class="btn-sm btn-primary"
                            wire:click="regenerateBentoLayout"
                            tooltip="Generate new random layout"
                        />
                        <x-button 
                            icon="phosphor.floppy-disk"
                            class="btn-sm btn-success"
                            wire:click="saveBentoSeed"
                            tooltip="Save this layout to homepage"
                        />
                    </div>
                    <p class="text-sm mt-2 opacity-70">
                        Click "Save" to use this layout on the homepage, or click "Regenerate" to try a different arrangement.
                    </p>
                </div>

                {{-- Bento Grid Preview --}}
                @if(count($this->getToursDataForBento()) > 0)
                    <div class="bg-base-200 p-6 rounded-lg">
                        <h4 class="text-sm font-semibold opacity-80 mb-4">Layout Preview</h4>
                        <livewire:bento-grid 
                            :images="$this->getToursDataForBento()" 
                            :setup-id="$debugSetupId"
                            wire:key="bento-debug-{{ $debugSetupId }}"
                        />
                    </div>
                @else
                    <div class="text-center py-8 opacity-60">
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