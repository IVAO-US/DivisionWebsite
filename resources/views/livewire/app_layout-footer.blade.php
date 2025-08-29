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
    <div class="mx-auto py-6 px-4 lg:px-10">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            
            <div class="flex flex-col items-center lg:items-start lg:flex-1">
                <div class="text-sm text-base-content/70 text-center lg:text-left">
                    <div class="flex flex-col sm:flex-row items-center lg:items-baseline sm:space-x-1">
                        <span>&copy; {{ $this->year() }} <a href="https://www.ivao.aero/" class="font-bold hover:text-secondary transition-colors">IVAO</a> <a href="https://us.ivao.aero/" class="font-bold hover:text-accent transition-colors">United States</a></span>
                    </div>
                </div>
                <div class="text-xs text-base-content/50 mt-1">
                    Version {{ $this->version() }} | By <a class="font-semibold hover:underline" target="_blank" href="https://www.ivao.aero/Member.aspx?Id=200696">Joey Salzmann - 200696</a>.
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 lg:gap-8 text-sm items-center justify-center text-center lg:flex-1 mt-4 lg:mt-0">
                <a href="{{ route('tos') }}" class="hover:text-primary transition-colors">Terms of Service</a>
                <span class="hidden sm:inline text-base-content/30">|</span>
                <a href="{{ route('privacy') }}" class="hover:text-primary transition-colors">Privacy Policy</a>
            </div>

            <div class="flex justify-center lg:justify-end lg:flex-1 mt-3 lg:mt-0">
                <livewire:app_layout-social-links :singleLine="true" />
            </div>
            
        </div>
    </div>
</footer>