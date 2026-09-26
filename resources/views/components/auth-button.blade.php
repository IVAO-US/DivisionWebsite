<?php
use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Blade;

use App\Models\User;
use App\Http\Controllers\IvaoController;
use Illuminate\Support\Facades\Auth;

use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    /*
     * The signed-in account, null for a visitor (mount() fills it only when
     * someone is signed in). Nullable with a default, so no path ever reads
     * it uninitialized, and locked, as the browser never writes it: a forged
     * update ("user.name") then gets Livewire's 419 before the property is
     * read, deep paths included. Nullable alone still ends in a 500, as
     * Livewire has no synthesizer to write into null.
     */
    #[Locked]
    public ?User $user = null;

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
            '<x-menu-item title="IVAO Account"  icon="phosphor.identification-badge"  link="https://www.ivao.aero/Member.aspx" external />',
            '<x-menu-item title="My Profile"    icon="phosphor.user-circle-gear" :link="$settingsUrl" />',
            '<x-menu-separator />',
            '<x-menu-item title="Log out" class="underline decoration-dashed underline-offset-3" icon="phosphor.power" wire:click="logout" />',
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
                "icon"          => 'phosphor.heart-break',
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
            "icon"          => 'phosphor.door-open',
            "css"           => 'alert-success',
            "timeout"       => 5000,
            "redirectTo"    => route('home')
        ];
        $this->success(...$toast_success);
    }
};
?>
<div>
    {{--
        The menu follows the account the component holds, not the session:
        a visitor's snapshot replayed in a session signed in since (a failed
        "Log in" after signing in from another tab, a forged $refresh) holds
        no account, and reading one would end in a 500.
    --}}
    @if ($user)
        {{-- Desktop --}}
        <div class="hidden lg:block">
            <x-dropdown no-x-anchor right>
                <x-slot:trigger>
                    <a class="btn rounded-lg bg-accent text-accent-content border-accent flex items-center gap-2 cursor-pointer">
                        <x-phosphor-headset class="w-5 h-5" />
                        {{ $user->first_name }}
                    </a>
                </x-slot:trigger>

                @php echo $this->userMenuItems(); @endphp
            </x-dropdown>
        </div>

        {{-- Mobile --}}
        <div class="lg:hidden bg-base-content/30 rounded-md">
            <x-menu-sub title="{{ $user->first_name }}" icon="phosphor.headset" icon-classes="font-semibold text-accent rounded-xl" class="!py-0">
                @php echo $this->userMenuItems(); @endphp
            </x-menu-sub>
        </div>
    @else
        {{-- Desktop --}}
        <div class="hidden lg:block">
            <x-button 
                label="Log in" 
                icon="phosphor.key" 
                class="btn-accent" 
                wire:click="login" 
                spinner />
        </div>

        {{-- Mobile --}}
        <div class="lg:hidden px-4">
            <x-button 
                label="Log in" 
                icon="phosphor.key" 
                class="btn-accent" 
                wire:click="login" 
                spinner />
        </div>
    @endif
</div>