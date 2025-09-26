<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

new 
#[Layout('components.layouts.app')]
#[Title('Division Transfer')]
class extends Component {
    /* User information */
    public User $user;
    public function mount(): void
    {
        if(Auth::check()) {
            $this->user = Auth::user();
            $this->fill($this->user);
        }
    }
}; ?>

<div>
    <x-header title="Division Transfer" size="h2" subtitle="Thinking about joining us?" class="!mb-5" />

    <x-card title="Division Transfer Procedure" subtitle="How to change your IVAO Division?" shadow separator>
        <x-alert icon="phosphor.warning" class="w-full alert-error border-error bg-error mb-6 ">
            <h5 class="text-error-content mb-4">Transfer Limitation</h5>
            You are only allowed to switch divisions every <b>12 months</b>.<br>
            Please make sure that you really wish to transfer before applying.<br><br>
            <a target="_blank" href="https://wiki.ivao.aero/en/home/members/faqs#division-change" class="link">Read more here</a>
        </x-alert>

        <x-alert icon="phosphor.warning-circle" class="w-full alert-warning border-warning bg-warning mb-6 ">
            <h6 class="text-warning-content mb-4">Important notice about Exams</h6>
            Please be aware that IVAO automatically enables an exam-block for the first <b>90 days</b> after a divisions transfer.
        </x-alert>

        <h5 class="mb-4">Requirements:</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-success" />
                    <span>Active IVAO account</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-success" />
                    <span>Clean Suspension History</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-success" />
                    <span>Fluent in English</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.check-circle" class="w-6 h-6 text-success" />
                    <span>Experience flying or controlling in US Airspace</span>
                </div>
            </div>
        </div>

        <!-- Process Section -->
        <div class="mb-6">
            <h5 class="mb-4">Process:</h5>
            
            <p class="mb-4">
                In order to apply send <strong>one single</strong> email to following addresses:
            </p>
            
            <div class="pl-5 mb-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.mailbox" class="w-6 h-6 text-secondary" />
                        <span>US-hq@ivao.aero</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.mailbox" class="w-6 h-6 text-secondary" />
                        @guest
                            <div>
                                <span>XX-hq@ivao.aero</span>
                                <div class="text-xs mt-1">
                                    >> where <b>XX</b> is the two-letter designator of <b>your current division</b>.
                                </div>
                            </div>
                        @endguest
                        @auth
                            <div>
                                <span>{{ $user->division }}-hq@ivao.aero</span>
                            </div>
                        @endauth
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.copy-duotone" class="w-6 h-6 text-secondary" />
                        <span><b>Carbon-Copy:</b> members@ivao.aero</span>
                    </div>
                </div>
            </div>
            
            <p class="mb-4">
                In your email, include the following information:
            </p>
            
            <div class="pl-5 mb-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.textbox" class="w-6 h-6 text-secondary" />
                        @guest
                            <div>
                                <span><b>Subject:</b> Division Transfer ######</span>
                                <div class="text-xs mt-1">
                                    >> Replace ###### with <b>your own VID</b>.
                                </div>
                            </div>
                        @endguest
                        @auth
                            <div>
                                <span><b>Subject:</b> Division Transfer {{ $user->vid }}</span>
                            </div>
                        @endauth
                    </div>

                    <div class="flex items-center gap-3">
                        <x-icon name="phosphor.file-text" class="w-6 h-6 text-secondary" />
                        <span>Please include a short reasoning as why you wish to transfer.</span>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>