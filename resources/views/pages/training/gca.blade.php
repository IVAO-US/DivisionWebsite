<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

use Mary\Traits\Toast;
use App\Traits\HasSEO;

new 
#[Layout('layouts.app')]
class extends Component {
    use Toast, HasSEO;

    /* User information */
    public User $user;

    public function mount(): void
	{
		$this->setSEOWithBreadcrumbs(
			title: 'Guest Controller Approval',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);

        if(Auth::check()) {
            $this->user = Auth::user();
            $this->fill($this->user);
        }
    }
}; ?>

<div>
    <x-header title="Guest Controller Approval" size="h2" subtitle="The US Division is part of the GCA Program" class="!mb-5" use-h1 />

    <x-card title="How to get your GCA?" subtitle="Protocol as per IVAO Rules & Regulations" shadow separator>
        
        <h5 class="mb-4">Requirements</h5>
            
        <div class="pl-4 mb-4">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.medal" class="w-6 h-6 text-success" />
                    <span>Minimum rating of <b>ADC</b></span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.translate" class="w-6 h-6 text-success" />
                    <span>Fluent in English</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.book-open" class="w-6 h-6 text-success" />
                    <span>Knowledge of <a target="_blank" href="https://wiki.us.ivao.aero/en/atc/sop" class="font-semibold underline">FAA procedures</a></span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-success" />
                    <span>Currently hold position check may be performed</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.calendar-check" class="w-6 h-6 text-success" />
                    <span>Control <b>3 hours per month</b> or more</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.warning" class="w-6 h-6 text-success" />
                    <span>No more than 4 GCAs total including US GCA (effective 31st-2024)</span>
                </div>
                
            </div>
        </div>

        <x-alert icon="phosphor.warning" class="w-full alert-warning border-warning bg-warning mb-8">
            The GCA can be withdrawn at any time by Division HQ if the requirements are not met.
        </x-alert>

        <!-- Application Section -->
        <div class="mb-6">
            <h5 class="mb-4">Application</h5>

            <div class="mb-4">
                Send an email to: <a href="mailto:us-training@ivao.aero" class="font-semibold underline">us-training@ivao.aero</a>
            </div>
            
            <div class="pl-5 mb-6">
                <div class="space-y-3">
                    
                    <div class="mb-4">
                        In your email include:
                    </div>

                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.user" class="w-6 h-6 text-secondary" />
                        <span>Your name</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.identification-badge" class="w-6 h-6 text-secondary" />
                        @guest
                            <span>Your VID</span>
                        @endguest
                        @auth
                            <span>Your VID: <b>{{ $user->vid }}</b></span>
                        @endauth
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.map-pin" class="w-6 h-6 text-secondary" />
                        <span>Desired airport/position</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.chat-centered-text" class="w-6 h-6 text-secondary" />
                        <span>Motivation</span>
                    </div>
                </div>
            </div>
            
            <div>
                Your submission will be reviewed as soon as possible.
            </div>
        </div>
    </x-card>
</div>