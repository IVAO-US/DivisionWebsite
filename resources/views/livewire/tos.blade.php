<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

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
			title: 'Terms of Service',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);
	}
}; ?>

<div>

    <x-header title="Terms of Service" size="h2" subtitle="Read about our rules and regulations" class="!mb-5" />

    <x-card title="You accept these terms of service when using this application" subtitle="Please note that the IVAO Terms of Service supersede these terms when conflicting." class="text-justify" shadow separator>

        <x-header title="1. Acceptance of Terms" size="h3" class="!mb-2" />
        <p class="mb-5">
            By accessing or using this application, you agree to be bound by these Terms of Service.<br>
            If you do not agree to all of these terms, you may not use this application.
        </p>

        <x-header title="2. Description of Service" size="h3" class="!mb-2" />
        <p class="mb-5">
            This application is provided free of charge.<br>
            We reserve the right to modify, suspend, or discontinue the service at any time without notice.
        </p>

        <x-header title="3. User Accounts and Responsibilities" size="h3" class="!mb-2" />
        <strong>3.1 Account Security</strong>
        <p class="mb-2">
            You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
        </p>
        <strong>3.2 User Content</strong>
        <p class="mb-2">
            You are solely responsible for any content you post, upload, share, store, or otherwise provide through the application.<br>
            We do not claim ownership of your content.
        </p>
        <strong>3.3 Prohibited Content</strong>
        <p>You agree not to post content that:</p>
            <ul class="list-disc pl-5 mb-5">
                <li>Is illegal, harmful, threatening, abusive, harassing, defamatory, or invasive of privacy</li>
                <li>Infringes on intellectual property rights of others</li>
                <li>Contains malware, viruses, or other malicious code</li>
                <li>Impersonates any person or entity</li>
            </ul>

        <x-header title="4. Limitation of Liability" size="h3" class="!mb-2" />
        <p class="mb-2">
            To the maximum extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including lost profits, arising out of or relating to your use of or inability to use the application.
        </p>
        <div class="mb-2">
            <strong>4.1 User Content Liability</strong>
            <p>We are not responsible for any content posted by users of the application.<br>
                Each user is solely responsible for their content and actions within the application.</p>
        </div>
        <div class="mb-5">
            <strong>4.2 Service Availability</strong>
            <p>We do not guarantee that the application will be error-free or uninterrupted.<br>
                We are not liable for any loss or damage caused by failure of access to or operation of the application.</p>
        </div>

        <x-header title="5. Data Privacy" size="h3" class="!mb-2" />
        <p class="mb-5">
            We do not collect, share, or sell personal data to third parties.<br>
            For more information on how we handle data, please refer to our <a href="{{ route('privacy') }}">Privacy Policy</a>.
        </p>

        <x-header title="6. User Isolation" size="h3" class="!mb-2" />
        <p class="mb-5">
            User activities and content are not visible to other users unless explicitly shared.<br>
            We take reasonable measures to maintain user privacy within the application.
        </p>

        <x-header title="7. Intellectual Property" size="h3" class="!mb-2" />
        <p class="mb-5">
            The application and its original content, features, and functionality are and will remain our exclusive property.<br>
            The application is protected by copyright, trademark, and other intellectual property laws.<br>
            We may terminate or suspend your account and access to the application immediately, without prior notice, for conduct that we believe violates these Terms of Service.
        </p>

        <x-header title="9. Changes to Terms" size="h3" class="!mb-2" />
        <p class="mb-5">
            We reserve the right to modify these terms at any time.<br>
            We will provide notice of significant changes by updating the "Last Updated" date at the top of these terms.
        </p>

        <x-header title="10. Governing Law" size="h3" class="!mb-2" />
        <p class="mb-5">
            These Terms shall be governed by and construed in accordance with the laws of the country of registration of the IVAO NPO, without regard to its conflict of law principles.
        </p>

        <x-header title="11. Contact Information" size="h3" class="!mb-2" />
        <p class="mb-5">
            For questions about these Terms of Service, please <a class="hover:underline" href="mailto:a-srdep@ivao.aero">contact us</a>.
        </p>
    
        <hr class="my-6">
    
        <p class="text-sm text-gray-600 italic">Last Updated: June 11th, 2025</p>
    </x-card>
</div>