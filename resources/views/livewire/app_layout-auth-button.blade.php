<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Blade;

use App\Models\User;
use App\Http\Controllers\IvaoController;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    /* User information */
    public User $user;
    public function mount(): void
    {
        if(Auth::check()) {
            $this->user = Auth::user();
            $this->fill($this->user);
        }
    }

    /* User menu items are defined here */
    public function userMenuItems(): string
    {
        $blade = implode("\n", [
            '<x-menu-item title="Settings" icon="lucide.wrench" :link="$settingsUrl" />',
            '<x-menu-separator />',
            '<x-menu-item title="Log out" icon="lucide.power" wire:click="logout" />',
        ]);

        return Blade::render($blade, [
            'settingsUrl' => route('users.settings'),
        ]);
    }

    /* Log in the user */
    public function login()
    {
        try {
            $authUrl = IvaoController::getAuthUrl();
            return redirect($authUrl);
        } catch (\Exception $e) {
            $toast_error = [  
                "title"         => 'Error!',
                "description"   => "Unable to connect to IVAO authentication service.",
                "position"      => 'toast-top toast-end', 
                "icon"          => 'lucide.heart-crack',
                "css"           => 'alert-error',
                "timeout"       => 5000,
                "redirectTo"    => null
            ];
            $this->error(...$toast_error);
        }
    }

    /* Log out the user */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $toast_success = [  
            "title"         => 'Success!',
            "description"   => "You have been logged out.",
            "position"      => 'toast-top toast-end', 
            "icon"          => 'lucide.door-open',
            "css"           => 'alert-success',
            "timeout"       => 5000,
            "redirectTo"    => route('home')
        ];
        $this->success(...$toast_success);
    }
};
?>
<div>
    @auth
        {{-- Desktop --}}
        <div class="hidden lg:block">
            <x-dropdown right>
                <x-slot:trigger>
                    <x-button icon="lucide.user-2" label="{{ $user->first_name }}" class="rounded-lg bg-accent text-accent-content border-accent" />
                </x-slot:trigger>

                @php echo $this->userMenuItems(); @endphp
            </x-dropdown>
        </div>

        {{-- Mobile --}}
        <div class="lg:hidden">
            <x-menu-sub title="{{ $user->first_name }}" icon="lucide.user-2" icon-classes="p-0.5 font-semibold bg-accent text-accent rounded-xl">
                @php echo $this->userMenuItems(); @endphp
            </x-menu-sub>
        </div>
	@endauth

    @guest
        {{-- Desktop --}}
        <div class="hidden lg:block">
            <x-button 
                label="Log in" 
                icon="lucide.key-round" 
                class="btn-accent" 
                wire:click="login" 
                spinner />
        </div>

        {{-- Mobile --}}
        <div class="lg:hidden">
            <x-menu-item 
                title="Log in" 
                icon="lucide.key-round" 
                class="text-accent" 
                wire:click="login" 
                spinner />
        </div>
    @endguest
</div>