<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

use Mary\Traits\Toast;

new 
#[Layout('components.layouts.app')]
#[Title('Settings')]
class extends Component {
    use Toast;
    public string $defaultTab = "settings-tab";

    /* Validation Rules */

    public string $name = '';
    public string $email = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email:rfc,dns|unique:users,email,' . Auth::id(),
        ];
    }


    /* Mount() */
    public function mount(): void
    {
        $user = Auth::user();

        $this->fill($user);
        $this->name = $user->full_name;
    }


    /* CRUD:update */
    public function saveSettings() 
    {
        $user_data = $this->validate();

        Auth::user()->update($user_data);
        request()->session()->regenerate();

        $toast_success = [  
            "title"         => 'Success!',
            "description"   => "Your settings were updated.",
            "position"      => 'toast-top toast-end', 
            "icon"          => 'phosphor.heart',
            "css"           => 'alert-success',
            "timeout"       => 5000 ,
            "redirectTo"    => route('users.settings')
        ];
        $this->success(...$toast_success);
    }
}; ?>

<div>

    <x-header title="Settings" size="h1" subtitle="Edit your account data" class="!mb-5" />

    <x-tabs wire:model="defaultTab">
        <x-tab name="settings-tab" label="Contact information" icon="phosphor.user" active>
            <x-card title="Your information" subtitle="Do you need to edit your data?" shadow separator progress-indicator="saveSettings">
                <div class="w-full md:w-125 mx-auto px-6 py-4">
                    <x-form wire:submit="saveSettings" no-separator>
                        <x-input    label="Name"                    icon="phosphor.user"   wire:model="name"  disabled />
                        <x-input    label="Email"                   icon="phosphor.at"  wire:model="email" />
                
                        <x-slot:actions>
                            <div class="flex justify-between w-full">
                                <x-button label="Save changes" icon="phosphor.paper-plane-tilt" class="btn-primary" type="submit" spinner="saveSettings" />
                            </div>
                        </x-slot:actions>
                    </x-form>
                </div>
            </x-card>
        </x-tab>
    </x-tabs>
</div>