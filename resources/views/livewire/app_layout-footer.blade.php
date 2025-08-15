<?php
use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    // Current year for copyright
    public function year(): string
    {
        return date('Y');
    }
    
    // Total count of users
    public function userCount(): int
    {
        return User::count();
    }
    
    // Current application version
    public function version(): string
    {
        return '1.0.0';
    }
};
?>

<footer class="bg-base-300 border-t border-base-400">
    <div class="max-w-7xl mx-auto py-6 px-4 lg:px-10">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
            <!-- Left side: branding and copyright -->
            <div class="flex flex-col items-center lg:items-start">
                <div class="text-sm text-base-content/70 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center sm:items-baseline sm:space-x-1">
                        <span>&copy; {{ $this->year() }} <a href="https://www.ivao.aero/" class="font-bold hover:text-primary transition-colors">IVAO</a> United States</span>
                        <span class="hidden sm:block">-</span>
                        <span>Coded by <a class="font-semibold hover:underline" target="_blank" href="https://www.ivao.aero/Member.aspx?Id=200696">Joey Salzmann | 200696</a>.</span>
                    </div>
                </div>
                <div class="text-xs text-base-content/50 mt-1">
                    Version {{ $this->version() }}
                </div>
            </div>
            
            <!-- Right side: links -->
            <div class="flex flex-col lg:flex-row gap-3 lg:gap-8 text-sm mt-4 lg:mt-0 text-center lg:text-right">
                <div class="flex flex-col sm:flex-row gap-3 lg:gap-8">
                    <a href="{{ route('tos') }}" class="hover:text-primary transition-colors">Terms of Service</a>
                    <a href="{{ route('privacy') }}" class="hover:text-primary transition-colors">Privacy Policy</a>
                </div>

                <span class="hidden lg:inline mx-3 text-base-content/30">|</span>

                <div class="flex gap-4 justify-center w-full lg:w-auto mt-3 lg:mt-0">
                    <livewire:app_layout-social-links />
                </div>
            </div>
        </div>
    </div>
</footer>