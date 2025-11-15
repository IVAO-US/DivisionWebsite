<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule; 

use Livewire\Volt\Component;

use Mary\Traits\Toast;
use App\Traits\HasSEO;

new 
#[Layout('components.layouts.app')]
class extends Component {
    use Toast, HasSEO;

    public function mount(): void
	{
		$this->setSEOWithBreadcrumbs(
			title: 'Members Support',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);
	}
}; ?>

<div>
    <x-header title="Members Support" size="h2" subtitle="Frequently Asked Questions" class="!mb-5" />

    <x-card title="Account Support" subtitle="Common questions about your account" shadow separator>
        <h5 class="mb-4">How do I reset my password?</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.cursor-click" class="w-6 h-6 text-secondary rotate-90" />
                    <span>Start by visiting our <a class="underline hover:text-primary transition-colors" target="_blank" href="https://www.ivao.aero/members/person/password.htm">Forgotten Password</a> page.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.envelope" class="w-6 h-6 text-secondary" />
                    <span>You will receive an email with your new generated passwords.</span>
                </div>
            </div>
        </div>


        <h5 class="mb-4">How do I reactivate my account?</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.mailbox" class="w-6 h-6 text-secondary" />
                    <span>Send an email to <a class="underline hover:text-primary transition-colors" target="_blank" href="mailto:members@ivao.aero">members@ivao.aero</a> requesting account reactivation.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.warning" class="w-6 h-6 text-secondary" />
                    <span>Please send it from the same email address that is listed on your account and include your full name.</span>
                </div>
            </div>
        </div>


        <h5 class="mb-4">Lost VID or email</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.mailbox" class="w-6 h-6 text-secondary" />
                    <span>Send an email to <a class="underline hover:text-primary transition-colors" target="_blank" href="mailto:members@ivao.aero">members@ivao.aero</a> requesting account reactivation.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.identification-card" class="w-6 h-6 text-secondary" />
                    <span>Include your full name and (if possible) email during registration.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.chat-text" class="w-6 h-6 text-secondary" />
                    <span>Explain your situation and IVAO staff will send you your login information.</span>
                </div>
            </div>
        </div>


        <h5 class="mb-4">How do I switch divisions?</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.book-open-text" class="w-6 h-6 text-secondary" />
                    <span>Please read our page dedicated to this procedure: <a class="underline hover:text-primary transition-colors" href="{{ route('division.transfer') }}">Division Transfer</a>.</span>
                </div>
            </div>
        </div>


        <h5 class="mb-4">How do I transfer VATSIM and real life ratings?</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.book-open-text" class="w-6 h-6 text-secondary" />
                    <span>Visit our division wiki article dedicated to this procedure: <a class="underline hover:text-primary transition-colors" target="_blank" href="https://wiki.ivao.aero/en/home/training/main/training_procedures/rating_transfer">Rating Transfer</a>.</span>
                </div>
            </div>
        </div>


        <h5 class="mb-4">Contact Us</h5>
            
        <div class="pl-4 mb-8">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.mailbox" class="w-6 h-6 text-secondary" />
                    <span>Feel free to contact us via email at: <a class="underline hover:text-primary transition-colors" target="_blank" href="mailto:us-hq@ivao.aero">us-hq@ivao.aero</a>.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-icon name="phosphor.discord-logo-fill" class="w-6 h-6 text-secondary" />
                    <span>You can also file a support ticket directly at our <a class="underline hover:text-primary transition-colors" target="_blank" href="https://discord.com/channels/442719713328627712/1332724731895611543">Discord server</a>.</span>
                </div>
            </div>
        </div>
    </x-card>
</div>