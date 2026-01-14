<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Validate;

use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Services\HeadlineService;
use App\Enums\AdminPermission;

new 
#[Layout('layouts.app')]
#[Title('Manage Headline')]
class extends Component {
    use Toast;
    
    #[Validate('nullable|string|max:50')]
    public ?string $motd = '';
    
    public ?array $currentHeadline = null;
    
    public function mount(): void
    {
        $this->checkPermissions();
        $this->loadMotd();
        $this->loadCurrentHeadline();
    }
    
    /**
     * Check if current user has headline permissions
     */
    private function checkPermissions(): void
    {
        if (!Admin::hasPermission(Auth::user()->vid, AdminPermission::APP_HEADLINE)) {
            abort(403, 'Insufficient permissions for headline management');
        }
    }
    
    /**
     * Load current MOTD from database
     */
    private function loadMotd(): void
    {
        $this->motd = AppSetting::get('headline_motd', '');
    }
    
    /**
     * Load current active headline
     */
    private function loadCurrentHeadline(): void
    {
        $this->currentHeadline = HeadlineService::getCurrentHeadline();
    }
    
    /**
     * Save MOTD
     */
    public function saveMotd(): void
    {
        $this->validate();
        
        AppSetting::set('headline_motd', $this->motd, 'string');
        HeadlineService::clearCache();
        
        $this->loadCurrentHeadline();
        $this->success('Message of the Day saved successfully!');
    }
    
    /**
     * Clear MOTD
     */
    public function clearMotd(): void
    {
        $this->motd = '';
        AppSetting::set('headline_motd', null, 'string');
        HeadlineService::clearCache();
        
        $this->loadCurrentHeadline();
        $this->success('Message of the Day cleared!');
    }
    
    /**
     * Refresh current headline preview
     */
    public function refreshPreview(): void
    {
        HeadlineService::clearCache();
        $this->loadCurrentHeadline();
        $this->success('Preview refreshed!');
    }
    
    /**
     * Get headline type badge class
     */
    public function getHeadlineTypeBadge(): array
    {
        if (!$this->currentHeadline) {
            return ['label' => 'No Headline', 'class' => 'badge-ghost'];
        }
        
        return match($this->currentHeadline['type']) {
            'division_session' => ['label' => 'Division Session', 'class' => 'badge-primary'],
            'online_day' => ['label' => 'Online Day', 'class' => 'badge-secondary'],
            'motd' => ['label' => 'MOTD', 'class' => 'badge-accent'],
            default => ['label' => 'Unknown', 'class' => 'badge-ghost'],
        };
    }
}; 
?>

<div>
    <x-header title="Manage Headline" size="h2" subtitle="Configure the dynamic headline banner" class="!mb-5" />

    {{-- Current Headline Preview --}}
    <x-card title="Current Headline" subtitle="Live preview of what users see" shadow separator class="mb-6 border-l-4 border-l-primary">
        <x-slot:menu>
            <x-button 
                icon="phosphor.arrow-clockwise" 
                class="btn-sm btn-ghost"
                wire:click="refreshPreview"
                spinner
                tooltip="Refresh preview"
            />
        </x-slot:menu>
        
        @if($currentHeadline)
            <div class="space-y-4">
                {{-- Preview Banner --}}
                <div class="bg-accent rounded-lg">
                    <div class="text-white text-center font-semibold px-3 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <x-icon name="{{ $currentHeadline['icon'] }}" class="w-5 h-5 font-bold xl:text-lg -scale-x-100" />
                            <span class="font-bold xl:text-lg">{{ $currentHeadline['title'] }}</span>
                            <span>{{ $currentHeadline['message'] }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Headline Info --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold">Active Type:</span>
                    @php $badge = $this->getHeadlineTypeBadge(); @endphp
                    <x-badge :value="$badge['label']" :class="$badge['class']" />
                </div>
            </div>
        @else
            <x-alert 
                title="No Headline Active" 
                description="The headline banner is currently hidden. Configure a MOTD below or wait for an Online Day or Division Session to start." 
                class="alert-info"
                icon="phosphor.info"
            />
        @endif
    </x-card>

    {{-- Priority Information --}}
    <x-card title="Headline Priority" subtitle="Understanding how headlines are displayed" shadow separator class="mb-6">
        <div class="space-y-3">
            <div class="flex items-start gap-3">
                <div class="badge badge-primary badge-lg">1</div>
                <div>
                    <p class="font-semibold">Division Sessions</p>
                    <p class="text-sm">Events, trainings, exams, and GCAs happening right now</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <div class="badge badge-secondary badge-lg">2</div>
                <div>
                    <p class="font-semibold">Online Day</p>
                    <p class="text-sm">Recurring Online Day event during active hours (configured in config/online-day.php)</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <div class="badge badge-accent badge-lg">3</div>
                <div>
                    <p class="font-semibold">Message of the Day (MOTD)</p>
                    <p class="text-sm">Custom message configured below, displayed when no higher priority headlines are active</p>
                </div>
            </div>
        </div>
    </x-card>

    {{-- MOTD Configuration --}}
    <x-card title="Message of the Day (MOTD)" subtitle="Configure a custom message to display in the headline" shadow separator>
        <div class="space-y-4">
            <x-textarea 
                label="MOTD Message" 
                wire:model="motd" 
                placeholder="Enter your message of the day..."
                rows="3"
                hint="This message will be displayed when no Division Sessions or Online Day are active (max 50 characters)"
            />
            
            <div class="flex gap-2">
                <x-button 
                    label="Save MOTD" 
                    icon="phosphor.floppy-disk"
                    class="btn-primary" 
                    wire:click="saveMotd"
                    spinner
                />
                
                @if($motd)
                    <x-button 
                        label="Clear MOTD" 
                        icon="phosphor.trash"
                        class="btn-outline btn-error" 
                        wire:click="clearMotd"
                        spinner
                    />
                @endif
            </div>
        </div>
    </x-card>
</div>