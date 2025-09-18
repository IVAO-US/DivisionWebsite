<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use App\Enums\AdminPermission;
use App\Models\User;
use App\Models\Admin;

new 
#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class extends Component {
    use Toast;
    
    public ?User $user = null;
    public ?Admin $admin = null;
    public array $categorizedPermissions = [];
    
    public function mount(): void
    {
        $this->loadAdminData();
        $this->categorizePermissions();
    }
    
    /**
     * Load current user admin data
     */
    private function loadAdminData(): void
    {
        $this->user = Auth::user();
        
        // Check if user is admin
        if (Admin::isAdmin($this->user->vid)) {
            $this->admin = Admin::with('user')->where('vid', $this->user->vid)->first();
        }
    }
    
    /**
     * Organize permissions by category, use this function to order up categories
     */
    private function categorizePermissions(): void
    {
        $categories = [
            'Admins' => [],
            'Application' =>[],
        ];
        
        foreach (AdminPermission::cases() as $permission) {
            // Skip System-wide permissions (like ALL) from display
            if ($permission->category() === 'System-wide') {
                continue;
            }
    
            // Skip global category permissions 
            if (!str_contains($permission->value, '_')) {
                continue;
            }
            
            if ($this->hasPermission($permission)) {
                $category = $permission->category();
                $categories[$category][] = $permission;
            }
        }
        
        // Only keep categories that have permissions
        $this->categorizedPermissions = array_filter($categories, fn($perms) => !empty($perms));
    }
    
    /**
     * Check if user has specific permission
     */
    private function hasPermission(AdminPermission $permission): bool
    {
        if (!$this->admin) {
            return false;
        }
        
        return $this->admin->canString($permission->value);
    }

    /**
     * Get category description from global permission
     */
    private function getCategoryDescription(string $category): string
    {
        $globalPermission = match($category) {
            'Admins' => AdminPermission::tryFrom('admins'),
            'Application' => AdminPermission::tryFrom('app'),
            default => null
        };
        
        if ($globalPermission) {
            return $globalPermission->description();
        }
        
        // Fallback descriptions for categories without global permissions
        return match($category) {
            default => 'Undefined administrative permissions'
        };
    }
    
    /**
     * Check if current user is super admin
     */
    private function isSuperAdmin(): bool
    {
        return $this->admin && $this->admin->canString('*');
    }
    
    /**
     * Get admin info for display
     */
    private function getAdminInfo(): ?array
    {
        if (!$this->admin) {
            return null;
        }
        
        return [
            'vid' => $this->admin->vid,
            'name' => $this->user->first_name . ' ' . $this->user->last_name ?? 'Unknown User',
            'permissions_count' => count($this->admin->permissions ?? []),
            'last_sync' => $this->admin->last_sync?->diffForHumans(),
        ];
    }

    /**
     * Get grid layout classes based on number of categories
     */
    private function getGridLayoutClasses(): string
    {
        $categoryCount = count($this->categorizedPermissions);
        
        return match($categoryCount) {
            1 => 'grid grid-cols-1 max-w-lg mx-auto gap-6',
            2 => 'grid grid-cols-1 md:grid-cols-2 gap-6',
            3 => 'grid grid-cols-1 md:grid-cols-2 gap-6 auto-rows-fr',
            4 => 'grid grid-cols-1 md:grid-cols-2 gap-6 h-fit',
            default => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 auto-rows-fr'
        };
    }

    /**
     * Get specific card classes for layout optimization
     */
    private function getCardClasses(string $category, int $index): string
    {
        $categoryCount = count($this->categorizedPermissions);
        $baseClasses = 'border-l-4 flex flex-col h-full';
        
        // For 3 items, make the middle card (Polls) span 2 rows on medium screens
        if ($categoryCount === 3 && $index === 1) {
            return $baseClasses . ' md:row-span-2 ' . AdminPermission::categoryColorProp($category, 'border-l');
        }
        
        return $baseClasses . ' ' . AdminPermission::categoryColorProp($category, 'border-l');
    }
}; ?>

<div>
    <x-header title="Admin Dashboard" size="h2" subtitle="Administration panel" class="!mb-6" />
    
    <!-- Admin Info Banner -->
    @if($this->admin)
        @php $adminInfo = $this->getAdminInfo(); @endphp
        <x-card class="mb-6 border-1 border-primary">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <h3 class="font-semibold text-lg">
                            {{ $adminInfo['name'] }}
                        </h3>
                        <p class="text-sm text-base-content">VID: {{ $adminInfo['vid'] }}</p>
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    @if($this->isSuperAdmin())
                        <x-badge value="SUPER ADMIN" class="badge-accent ml-2" />
                    @else
                        <x-badge value="ADMIN" class="badge-accent ml-2" />
                    @endif
                </div>
            </div>
        </x-card>
    @endif
    
    @if(empty($this->categorizedPermissions))
        <x-card title="Access Denied!" subtitle="You do not have administrative permissions" shadow class="border-2 border-error">
            <div class="py-4 text-center">
                <x-icon name="phosphor.shield-warning" class="w-16 h-16 mx-auto text-error mb-4" />
                <p class="text-base-content">Contact your administrator to request additional permissions.</p>
            </div>
        </x-card>
    @else
        <!-- Admin Actions Grid -->
        <div class="{{ $this->getGridLayoutClasses() }}">
            @foreach($this->categorizedPermissions as $category => $permissions)
                @php 
                    $index = array_search($category, array_keys($this->categorizedPermissions));
                @endphp
                
                <x-card 
                    title="{{ $category }}" 
                    subtitle="{{ $this->getCategoryDescription($category) }}"
                    shadow 
                    class="{{ $this->getCardClasses($category, $index) }}"
                >
                    <x-slot:menu>
                        <x-icon name="{{ AdminPermission::categoryIcon($category) }}" class="w-5 h-5 text-base-content" />
                    </x-slot:menu>
                    
                    <!-- Content wrapper with flex-grow to push button down -->
                    <div class="flex flex-col h-full">
                        <!-- Permissions list -->
                        <div class="space-y-3 flex-grow">
                            @foreach($permissions as $permission)
                                <div class="flex items-center justify-between p-3 bg-base-100 rounded-lg border border-base-200 hover:border-base-300 transition-colors duration-150">
                                    <div class="flex items-center space-x-3">
                                        <x-icon name="{{ $permission->icon() }}" class="w-4 h-4 {{ AdminPermission::categoryColorProp($category, 'text') }}" />
                                        <div>
                                            <p class="font-medium text-sm">{{ $permission->description() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Action button pushed to bottom -->
                        @if(AdminPermission::categoryRoute($category))
                            <div class="pt-4 border-t border-base-200 mt-auto">
                                <x-button 
                                    label="{{ $this->getCategoryDescription($category) }}" 
                                    icon="phosphor.link" 
                                    link="{{ AdminPermission::categoryRoute($category) }}" 
                                    class="{{ AdminPermission::categoryColorProp($category, 'btn') }} btn-sm btn-outline w-full"
                                />
                            </div>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</div>