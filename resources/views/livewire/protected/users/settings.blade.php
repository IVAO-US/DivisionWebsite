<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

use App\Models\User;
use App\Models\UserSetting;

use App\Enums\ATCRating;
use App\Enums\PilotRating;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.app')]
#[Title('My Profile')]
class extends Component {
    use Toast;
    public string $defaultTab = "ivaoData-tab";

    /* Model wire */
    public string $vid = '';
    public string $name = '';
    public string $division = '';
    public string $staff = '';
    public string $email = '';
    public string $rating_atc = '';
    public string $hours_atc = '';
    public string $rating_pilot = '';
    public string $hours_pilot = '';
    public string $gca = '';

    /* User::Accessors & Enums */
    public string $staff_positions = '';
    public string $division_logo = '';
    public ATCRating $rating_atc_enum;
    public PilotRating $rating_pilot_enum;
    
    /* User settings */
    public string   $custom_email = '';
    public string   $discord = '';
    public bool     $allow_notifications = true;


    /* Mount() */
    public function mount(): void
    {
        /* Retrieving raw user data */
        $user = Auth::user();

        $this->fill($user);
        $this->loadUserSettings($user);

        $this->name             = $user->full_name;
        $this->staff_positions  = $user->staff_positions;
        $this->division_logo    = $user->division_logo;

        $this->rating_atc_enum  = ATCRating::fromInt($user->rating_atc);
        $this->rating_pilot_enum  = PilotRating::fromInt($user->rating_pilot);
    }
        /**
         * Load user settings
         */
        private function loadUserSettings(User $user): void
        {
            $settings = $user->settings;
            
            if ($settings) {
                $this->custom_email = $settings->custom_email ?? '';
                $this->discord = $settings->discord ?? '';
                $this->allow_notifications = $settings->allow_notifications ?? true;
            }
        }

    /* Validation Rules -> users_settings */
    protected function rules(): array
    {
        return [
            'custom_email'          => 'nullable|email:rfc,dns|unique:user_settings,custom_email,' . Auth::user()->vid . ',vid|unique:users,email',
            'discord'               => 'nullable|string|max:255',
            'allow_notifications'   => 'boolean',
        ];
    }

    /* CRUD:update */
    public function updateSettings() 
    {
        $validated_data = $this->validate();
        
        $user = Auth::user();
        
        $settings = $user->getOrCreateSettings();
        
        // Update settings
        $settings->update([
            'custom_email' => $validated_data['custom_email'],
            'discord' => $validated_data['discord'],
            'allow_notifications' => $validated_data['allow_notifications'],
        ]);

        $toast_success = [  
            "title"         => 'Success!',
            "description"   => "Your settings were updated.",
            "position"      => 'toast-top toast-end', 
            "icon"          => 'phosphor.heart',
            "css"           => 'alert-success',
            "timeout"       => 5000,
            "redirectTo"    => null
        ];
        $this->success(...$toast_success);
    }
}; ?>

<div>
    <x-header title="Your Profile" size="h2" subtitle="Edit your account data" class="!mb-5" />

    <x-tabs wire:model="defaultTab"
            label-div-class="bg-base-100 p-3 rounded-lg font-semibold" 
            active-class="bg-primary p-2 rounded !text-white" 
            label-class="font-semibold" 
            >
        <x-tab name="ivaoData-tab" label="Personal Information" icon="phosphor.user" active>
            <x-card title="Your personal information" subtitle="Retrieved from your IVAO account" shadow separator>
                <div class="w-full md:w-125 mx-auto">
                    <x-form no-separator>
                        <x-input    label="VID"         icon="phosphor.hash"      wire:model="vid"   readonly class="font-semibold"/>
                        <x-input    label="Name"        icon="phosphor.user"    wire:model="name"  readonly />
                        <x-input label="Division" class="!hidden">
                            <x-slot:append>
                                <x-avatar image="{{ $this->division_logo }}"  class="join-item !h-[50px] !w-[50px] !rounded-none" />
                            </x-slot:append>
                        </x-input>
                        <x-input    label="Staff Positions"   icon="phosphor.medal-military"   wire:model="staff_positions"  readonly />
                        <x-input    label="Email"       icon="phosphor.at"   wire:model="email"  readonly />
                    </x-form>
                </div>
            </x-card>
        </x-tab>
        <x-tab name="atcData-tab" label="ATC Data" icon="phosphor.radio" active>
            <x-card title="ATC Career" subtitle="Sync'ed from your IVAO profile" shadow separator>
                <div class="w-full md:w-125 mx-auto">
                    <x-form no-separator>
                        <x-input label="ATC Rating" class="!hidden">
                            <x-slot:append>
                                <x-avatar image="{{ $this->rating_atc_enum->imagePath() }}" alt="{{ $this->rating_atc_enum->fullName() }}" class="join-item !h-[30px] !w-[105px] !rounded-none">
                                    <x-slot:title class="!text-base !font-semibold text-secondary">
                                        {{ $this->rating_atc_enum->name }} - {{ $this->rating_atc_enum->fullName() }}
                                    </x-slot:title>
                                </x-avatar>
                            </x-slot:append>
                        </x-input>
                        <x-input    label="ATC Hours"   icon="phosphor.timer"   wire:model="hours_atc"  readonly />
                        <x-input    label="GCAs"    icon="phosphor.flag"   wire:model="gca"  readonly />
                    </x-form>
                </div>
            </x-card>
        </x-tab>
        <x-tab name="pilotData-tab" label="Pilot Data" icon="phosphor.paper-plane-tilt" active>
            <x-card title="Pilot Career" subtitle="Sync'ed from your IVAO profile" shadow separator>
                <div class="w-full md:w-125 mx-auto">
                    <x-form no-separator>
                        <x-input label="Pilot Rating" class="!hidden">
                            <x-slot:append>
                                <x-avatar image="{{ $this->rating_pilot_enum->imagePath() }}" alt="{{ $this->rating_pilot_enum->fullName() }}" class="join-item !h-[30px] !w-[105px] !rounded-none">
                                    <x-slot:title class="!text-base !font-semibold text-secondary">
                                        {{ $this->rating_pilot_enum->name }} - {{ $this->rating_pilot_enum->fullName() }}
                                    </x-slot:title>
                                </x-avatar>
                            </x-slot:append>
                        </x-input>
                        <x-input    label="Pilot Hours"   icon="phosphor.timer"   wire:model="hours_pilot"  readonly />
                    </x-form>
                </div>
            </x-card>
        </x-tab>
        <x-tab name="settings-tab" label="Settings" icon="phosphor.gear" active>
            <x-card title="Edit your settings" subtitle="Customize your experience" shadow separator progress-indicator="updateSettings">
                <div class="w-full md:w-125 mx-auto">
                    <x-form wire:submit="updateSettings" no-separator>
                        <p> The following settings can be edited by yourself.<br>
                            We may use these information to reach out.<br>
                            <br>
                            <span class="text-info font-semibold">Information:</span> your IVAO registration email will show by default but you can add another email.
                        </p>
                        <x-input    label="Email"           icon="phosphor.at"               wire:model="custom_email"   placeholder="{{ $this->email }}" />
                        <x-input    label="Discord"         icon="phosphor.game-controller"             wire:model="discord" class="mb-2" />
                        <x-toggle   label="Notifications"   hint="Enable email notifications"   wire:model="allow_notifications" class="mt-2" />
                        <x-slot:actions>
                            <div class="flex justify-between w-full">
                                <x-button label="Update Settings" icon="phosphor.paper-plane-tilt" class="btn-primary" type="submit" spinner="updateSettings" />
                            </div>
                        </x-slot:actions>
                    </x-form>
                </div>
            </x-card>
        </x-tab>
    </x-tabs>
</div>