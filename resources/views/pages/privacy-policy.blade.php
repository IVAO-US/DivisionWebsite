<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Livewire\Component;

use Mary\Traits\Toast;
use App\Traits\HasSEO;

new 
#[Layout('components.layouts.app')]
class extends Component {
    use Toast, HasSEO;

    public function mount(): void
	{
		$this->setSEOWithBreadcrumbs(
			title: 'Privacy Policy',
			description: config('seotools.meta.defaults.description'),
			image: asset('assets/seo/snapshot.jpg'),
			keywords: config('seotools.meta.defaults.keywords')
		);
	}
}; ?>

<div>

    <x-header title="Privacy Policy" size="h2" subtitle="We value your privacy" class="!mb-5" />

    <x-card title="Learn how we protect your personal data" subtitle="Please note that the IVAO Terms of Service supersede these terms when conflicting." class="text-justify" shadow separator>

        <x-header title="Introduction" size="h3" class="!mb-2" />
        <p class="mb-5">
            This Privacy Policy explains how we collect, use, and protect your information when you use our application.<br>
            We are committed to ensuring the privacy and security of your data in compliance with the General Data Protection Regulation (GDPR) and other applicable privacy laws.
        </p>
    
        <x-header title="Controller Information" size="h3" class="!mb-2" />
        <div class="mb-5">
            <p><strong>Controller Name</strong>: IVAO NPO - HQ ATC Operations</p>
            <p><strong>Contact Email</strong>: <a class="hover:underline" href="mailto:a-srdep@ivao.aero">a-srdep@ivao.aero</a></p>
        </div>
    
        <x-header title="Data Collection" size="h3" class="!mb-2" />
        <p class="mb-2">
            Our application is designed with privacy in mind. We <strong>do not</strong> collect, sell, or share any personal data. <br>
            The only data processing that occurs is:
        </p>
        <div class="mb-5 pl-4">
            <p><strong>Essential Cookies</strong>: Our application uses only the cookies that are required by our website to function properly.<br>
            These cookies are necessary for the website to work and cannot be switched off in our systems.</p>
        </div>
    
        <x-header title="Technical Details of Cookies" size="h3" class="!mb-2" />
        <p class="mb-2">This website requires certain cookies to operate properly:</p>
        <ul class="list-disc pl-8 mb-2">
            <li><strong>XSRF-TOKEN</strong>: Used to prevent cross-site request forgery attacks.</li>
            <li><strong>Session</strong>: Contains encrypted session data to maintain your logged-in state and other session-related information.</li>
        </ul>
        <p class="mb-5">
            These cookies do not track your browsing activity on other websites, nor do they identify you personally beyond what is required to maintain your session within our application.
        </p>
    
        <x-header title="Data Storage and Security" size="h3" class="!mb-2" />
        <ul class="list-disc pl-8 mb-5">
            <li>All data processing occurs within the European Union through our hosting provider.</li>
            <li>We implement appropriate security measures to protect against unauthorized access to or unauthorized alteration, 
            disclosure, or destruction of data.</li>
        </ul>
    
        <x-header title="Your Rights Under GDPR" size="h3" class="!mb-2" />
        <p class="mb-2">Even though we collect minimal data, you still have the following rights:</p>
        <ul class="list-disc pl-8 mb-2">
            <li><strong>Right to Access</strong>: You can request confirmation of whether we process any personal data relating to you.</li>
            <li><strong>Right to Erasure</strong>: You can request that we delete any personal data we may hold about you.</li>
            <li><strong>Right to Object</strong>: You can object to the processing of your personal data.</li>
            <li><strong>Right to Data Portability</strong>: You can request a copy of your data in a structured, commonly used, and machine-readable format.</li>
            <li><strong>Right to Complain</strong>: You have the right to lodge a complaint with a supervisory authority.</li>
        </ul>
        <p class="mb-5">
            To exercise any of these rights, please contact us using the email information below.
        </p>
    
        <x-header title="Changes to This Privacy Policy" size="h3" class="!mb-2" />
        <p class="mb-5">
            We may update this Privacy Policy from time to time.<br>
            We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date.
        </p>
    
        <x-header title="Contact Us" size="h3" class="!mb-2" />
        <p class="mb-2">If you have any questions about this Privacy Policy, please contact us:</p>
        <ul class="list-disc pl-8 mb-5">
            <li>By email: <a class="hover:underline" href="mailto:a-srdep@ivao.aero">a-srdep@ivao.aero</a></li>
        </ul>
    
        <hr class="my-6">
    
        <p class="text-sm text-gray-600 italic">Last Updated: June 11th, 2025</p>

    </x-card>
</div>