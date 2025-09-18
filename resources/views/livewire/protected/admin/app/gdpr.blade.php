<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session as LaravelSession;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Admin;
use App\Models\UserSetting;
use App\Models\GdprDeletionLog;
use App\Enums\AdminPermission;

new 
#[Layout('components.layouts.app')]
#[Title('GDPR Management')]
class extends Component {
    use Toast, WithPagination;
    
    // User search and selection
    public string $userSearch = '';
    public ?User $selectedUser = null;
    public array $searchResults = [];
    
    // Control key management
    public string $controlKey = '';
    public string $controlKeyInput = '';
    
    // Deletion process
    public bool $showConfirmationModal = false;
    public bool $showSuccessModal = false;
    public string $deletionReason = '';
    
    // Deletion logs
    public int $perPage = 10;
    
    public function mount(): void
    {
        $this->checkPermissions();
    }
    
    /**
     * Check if current user has GDPR permissions
     */
    private function checkPermissions(): void
    {
        if (!Admin::hasPermission(Auth::user()->vid, AdminPermission::APP_GDPR)) {
            abort(403, 'Insufficient permissions for GDPR management');
        }
    }
        
    /**
     * Search users when input is updated
     */
    public function updatedUserSearch(): void
    {
        if (strlen($this->userSearch) >= 2) {
            $this->searchResults = User::where(function($query) {
                $query->where('vid', 'like', '%' . $this->userSearch . '%')
                      ->orWhere('first_name', 'like', '%' . $this->userSearch . '%')
                      ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                      ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $this->userSearch . '%']);
            })
            ->where('email', 'not like', 'deleted-user-%') // Exclude already anonymized users
            ->limit(10)
            ->get()
            ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    /**
     * Select a user for deletion
     */
    public function selectUser(int $userVid): void
    {
        $this->selectedUser = User::with(['settings', 'admin'])
            ->where('vid', $userVid)
            ->first();
        
        if (!$this->selectedUser) {
            $this->error('User not found');
            return;
        }
        
        // Check if user is already anonymized
        if (str_starts_with($this->selectedUser->email, 'deleted-user-')) {
            $this->error('This user has already been processed for GDPR deletion');
            $this->selectedUser = null;
            return;
        }
        
        // Generate new control key
        $this->controlKey = Str::random(32);
        $this->controlKeyInput = '';
        $this->searchResults = [];
        $this->userSearch = '';
        
        $this->success("User selected: {$this->selectedUser->full_name} - VID: {$this->selectedUser->vid}");
    }
    
    /**
     * Clear selected user
     */
    public function clearUser(): void
    {
        $this->selectedUser = null;
        $this->controlKey = '';
        $this->controlKeyInput = '';
        $this->deletionReason = '';
        $this->searchResults = [];
    }
    
    /**
     * Open confirmation modal
     */
    public function openConfirmationModal(): void
    {
        if (!$this->selectedUser) {
            $this->error('No user selected');
            return;
        }
        
        if (empty($this->controlKeyInput)) {
            $this->error('Please enter the control key');
            return;
        }
        
        if ($this->controlKeyInput !== $this->controlKey) {
            $this->error('Invalid control key');
            return;
        }
        
        // Check if user is admin - prevent deletion
        if ($this->selectedUser->admin) {
            $this->error('Cannot delete admin users. Please remove admin privileges first in the Admin Management section.');
            return;
        }
        
        $this->showConfirmationModal = true;
    }
    
    /**
     * Execute GDPR deletion
     */
    public function executeDeletion(): void
    {
        if (!$this->selectedUser) {
            $this->error('No user selected');
            return;
        }
        
        if ($this->controlKeyInput !== $this->controlKey) {
            $this->error('Invalid control key');
            return;
        }
        
        // Final admin check
        if ($this->selectedUser->admin) {
            $this->error('Cannot delete admin users. Please remove admin privileges first.');
            return;
        }
        
        try {
            DB::transaction(function () {
                $deletedData = [];
                
                // Store original user data for logging
                $originalUserData = [
                    'vid' => $this->selectedUser->vid,
                    'full_name' => $this->selectedUser->full_name,
                    'email' => $this->selectedUser->email,
                    'first_name' => $this->selectedUser->first_name,
                    'last_name' => $this->selectedUser->last_name,
                    'country' => $this->selectedUser->country,
                    'division' => $this->selectedUser->division
                ];
                
                // 1. Delete user settings
                if ($this->selectedUser->settings) {
                    $deletedData['user_settings'] = [
                        'custom_email' => $this->selectedUser->settings->custom_email,
                        'discord' => $this->selectedUser->settings->discord,
                        'allow_notifications' => $this->selectedUser->settings->allow_notifications
                    ];
                    $this->selectedUser->settings()->delete();
                }
                
                // 2. Clear user sessions
                $deletedSessions = DB::table('sessions')
                    ->where('user_id', $this->selectedUser->id)
                    ->get()
                    ->count();
                
                if ($deletedSessions > 0) {
                    DB::table('sessions')->where('user_id', $this->selectedUser->id)->delete();
                    $deletedData['sessions'] = $deletedSessions;
                }
                
                // 3. Anonymize user data
                $anonymizedId = 'deleted-user-' . time() . '-' . Str::random(8);
                
                $this->selectedUser->update([
                    'first_name' => 'Deleted',
                    'last_name' => 'User',
                    'email' => $anonymizedId . '@deleted.local',
                    'gca' => null,
                    'staff' => null,
                    'remember_token' => null,
                ]);
                
                $deletedData['user'] = $originalUserData;
                
                // 4. Log the deletion
                GdprDeletionLog::create([
                    'user_vid' => $originalUserData['vid'],
                    'user_full_name' => $originalUserData['full_name'],
                    'user_email' => $originalUserData['email'],
                    'admin_vid' => Auth::user()->vid,
                    'admin_name' => Auth::user()->full_name,
                    'control_key' => $this->controlKey,
                    'deleted_data' => $deletedData,
                    'reason' => $this->deletionReason ?: null,
                    'executed_at' => now(),
                ]);
            });
            
            $this->showConfirmationModal = false;
            $this->showSuccessModal = true;
            
            // Clear form
            $this->clearUser();
            
        } catch (\Exception $e) {
            $this->error('Deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Close success modal
     */
    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
    }
    
    /**
     * Get deletion logs
     */
    public function getDeletionLogs()
    {
        return GdprDeletionLog::orderBy('executed_at', 'desc')->paginate($this->perPage);
    }
    
    /**
     * Get user data summary for display
     */
    public function getUserDataSummary(): array
    {
        if (!$this->selectedUser) {
            return [];
        }
        
        $summary = [
            'Personal Data' => [
                'Name' => $this->selectedUser->full_name,
                'Email' => $this->selectedUser->email,
                'VID' => $this->selectedUser->vid,
                'Country' => $this->selectedUser->country,
                'Division' => $this->selectedUser->division
            ],
            'Settings' => [],
            'Roles' => [],
            'Activity' => []
        ];
        
        // User settings
        if ($this->selectedUser->settings) {
            $summary['Settings'] = [
                'Custom Email' => $this->selectedUser->settings->custom_email ?: 'None',
                'Discord' => $this->selectedUser->settings->discord ?: 'None',
                'Notifications' => $this->selectedUser->settings->allow_notifications ? 'Enabled' : 'Disabled'
            ];
        } else {
            $summary['Settings'] = ['No custom settings'];
        }
        
        // Admin role check
        if ($this->selectedUser->admin) {
            $permissionCount = is_array($this->selectedUser->admin->permissions) ? count($this->selectedUser->admin->permissions) : 0;
            $summary['Roles']['Admin'] = "{$permissionCount} permission(s)";
        } else {
            $summary['Roles'] = ['No admin privileges'];
        }
        
        // Sessions count
        $sessionCount = DB::table('sessions')->where('user_id', $this->selectedUser->id)->count();
        if ($sessionCount > 0) {
            $summary['Activity']['Active Sessions'] = $sessionCount;
        }
        
        return $summary;
    }
        
    /**
     * Component render
     */
    public function with(): array
    {
        return [
            'deletionLogs' => $this->getDeletionLogs(),
            'userDataSummary' => $this->getUserDataSummary()
        ];
    }
}; ?>

<div>
    <x-header title="GDPR Management" size="h2" subtitle="Handle 'Right to be Forgotten' requests" class="!mb-5" />
        
    <x-tabs selected="deletion-tab">
        <x-tab name="deletion-tab" label="User Deletion" icon="phosphor.user-focus">
            <!-- Warning Alert -->
            <div>
                <x-alert 
                    title="Critical Operation" 
                    description="GDPR deletion is irreversible and will permanently remove/anonymize user data while preserving application integrity." 
                    icon="phosphor.warning" 
                    class="alert-error border-error bg-error mb-6"
                >
                    <x-slot:actions>
                        <div class="flex items-center gap-2 text-error-content">
                            <x-icon name="phosphor.lock" class="w-4 h-4" />
                            <span class="text-sm font-medium">Irreversible<br>Permanent</span>
                        </div>
                    </x-slot:actions>
                </x-alert>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-card title="1. Select User" subtitle="Search and select user for GDPR deletion" shadow separator>
                    <div class="space-y-4">
                        <div class="relative">
                            <x-input 
                                label="Search User" 
                                placeholder="Enter VID, name, or email..."
                                wire:model.live.debounce.300ms="userSearch"
                                icon="phosphor.magnifying-glass-light"
                                hint="Minimum 2 characters"
                                required
                            />
                            
                            @if(strlen($userSearch) >= 2 && count($searchResults) > 0)
                                <div class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    @foreach($searchResults as $user)
                                        <div 
                                            class="p-3 hover:bg-base-200 cursor-pointer border-b border-base-200 last:border-b-0 transition-colors"
                                            wire:click="selectUser({{ $user['vid'] }})"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="font-medium text-base-content">
                                                        {{ $user['first_name'] }} {{ $user['last_name'] }}
                                                    </div>
                                                    <div class="text-sm text-base-content/70">
                                                        VID: {{ $user['vid'] }} • {{ $user['email'] }}
                                                    </div>
                                                </div>
                                                <x-icon name="phosphor.caret-right" class="w-4 h-4 text-base-content/50" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if(strlen($userSearch) >= 2 && count($searchResults) === 0)
                                <div class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg p-3">
                                    <div class="text-center text-base-content/60">
                                        <x-icon name="phosphor.magnifying-glass-light" class="w-8 h-8 mx-auto mb-2" />
                                        <p>No users found</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @if($selectedUser)
                            <div class="bg-success/20 border border-success/50 rounded-lg p-4">
                                <h4 class="font-semibold text-success mb-2">✓ User Selected</h4>
                                <p><strong>{{ $selectedUser->full_name }}</strong> - VID: <a href="https://www.ivao.aero/Member.aspx?Id={{ $selectedUser->vid }}" target="_blank" class="font-semibold text-primary">{{ $selectedUser->vid }}</a></p>
                                <p class="text-sm">{{ $selectedUser->email }}</p>
                                
                                @if($selectedUser->admin)
                                    <div class="mt-2 p-2 bg-error/20 border border-error/50 rounded">
                                        <p class="text-error text-sm font-medium">⚠️ This user has admin privileges</p>
                                        <p class="text-error text-xs">Remove admin access first before GDPR deletion</p>
                                    </div>
                                @endif
                                
                                <x-button 
                                    wire:click="clearUser" 
                                    class="btn-sm btn-outline btn-error mt-4"
                                >
                                    Clear Selection
                                </x-button>
                            </div>
                        @endif
                    </div>
                </x-card>
                
                <!-- User Data Summary -->
                @if($selectedUser)
                    <x-card title="User Data Summary" subtitle="Data that will be deleted/anonymized" shadow separator>
                        <div class="space-y-4">
                            @foreach($userDataSummary as $category => $data)
                                <div>
                                    <h4 class="font-semibold mb-2">{{ $category }}</h4>
                                    <div class="bg-base-200 rounded p-3">
                                        @if(is_array($data) && count($data) > 0 && !array_key_exists(0, $data))
                                            @foreach($data as $key => $value)
                                                <div class="flex justify-between text-sm">
                                                    <span>{{ $key }}:</span>
                                                    <span class="font-medium">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-sm text-base-content/70">
                                                {{ is_array($data) ? implode(', ', $data) : $data }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>
            
            <!-- Control Key and Deletion section if selectedUser exists -->
            @if($selectedUser)
                <x-card title="2. Confirm Deletion" subtitle="Enter control key to confirm GDPR deletion" shadow separator class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Control Key -->
                        <div>
                            <x-alert title="Control Key" description="Copy this key and enter it below to confirm deletion" class="alert-info mb-4" />
                            <div class="bg-base-200 p-4 rounded font-mono text-sm break-all">
                                {{ $controlKey }}
                            </div>
                        </div>
                        
                        <!-- Confirmation Form -->
                        <div class="space-y-4">
                            <x-input 
                                label="Enter Control Key" 
                                placeholder="Paste the control key here..."
                                wire:model="controlKeyInput"
                                type="password"
                                icon="phosphor.key"
                                required
                            />
                        </div>
                    </div>
                    <div class="w-full mt-4">
                        <div class="space-y-4 lg:w-[50vh] lg:mx-auto">
                            <x-textarea 
                                label="Reason (Optional)" 
                                placeholder="Optional reason for deletion..."
                                wire:model="deletionReason"
                                rows="3"
                            />
                            
                            <x-button 
                                wire:click="openConfirmationModal"
                                class="btn-error w-full"
                                icon="phosphor.trash"
                                :disabled="$selectedUser && $selectedUser->admin"
                            >
                                Execute GDPR Deletion
                            </x-button>
                            
                            @if($selectedUser && $selectedUser->admin)
                                <p class="text-center text-error text-sm">Cannot delete admin users - remove admin privileges first</p>
                            @endif
                        </div>
                    </div>
                </x-card>
            @endif
        </x-tab>
        
        <x-tab name="logs-tab" label="Deletion Logs" icon="phosphor.scroll">
            <x-card title="GDPR Deletion Logs" subtitle="History of all GDPR deletions performed" shadow separator>
                @if($deletionLogs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Admin</th>
                                    <th>Data Deleted</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deletionLogs as $log)
                                    <tr>
                                        <td>{{ $log->formatted_executed_at }}</td>
                                        <td>
                                            <div>
                                                <span class="font-medium">{{ $log->user_full_name }}</span>
                                                <br>
                                                <span class="text-sm text-base-content/70">VID: {{ $log->user_vid }}</span>
                                                <br>
                                                <span class="text-xs text-base-content/50">{{ $log->user_email }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $log->admin_name }}</td>
                                        <td>
                                            <div class="text-sm">
                                                {{ $log->deleted_data_summary }}
                                            </div>
                                        </td>
                                        <td>{{ $log->reason ?: 'No reason provided' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $deletionLogs->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="phosphor.folder-dashed" class="w-16 h-16 mx-auto text-primary mb-4" />
                        <p class="text-base-content">No GDPR deletions have been performed yet.</p>
                    </div>
                @endif
            </x-card>
        </x-tab>
    </x-tabs>
    
    
    <!-- Confirmation Modal -->
    <x-modal wire:model="showConfirmationModal" title="⚠️ Confirm GDPR Deletion">
        <div class="space-y-4">
            <x-alert title="Final Warning" description="This action cannot be undone. The user's data will be permanently deleted/anonymized." class="alert-error" />
            
            <div class="bg-base-200 p-4 rounded">
                <h4 class="font-semibold mb-2">User to be deleted:</h4>
                <p><strong>{{ $selectedUser?->full_name }}</strong> - VID: <a href="https://www.ivao.aero/Member.aspx?Id={{ $selectedUser?->vid }}" target="_blank" class="font-semibold text-primary">{{ $selectedUser?->vid }}</a></p>
                <p class="text-sm">{{ $selectedUser?->email }}</p>
            </div>
            
            @if($deletionReason)
                <div class="bg-base-200 p-4 rounded">
                    <h4 class="font-semibold mb-2">Reason:</h4>
                    <p class="text-sm whitespace-pre-line">{{ $deletionReason }}</p>
                </div>
            @endif
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showConfirmationModal = false" />
            <x-button 
                label="Confirm Deletion" 
                wire:click="executeDeletion"
                class="btn-error"
                spinner
            />
        </x-slot:actions>
    </x-modal>
    
    <!-- Success Modal -->
    <x-modal wire:model="showSuccessModal" title="✅ GDPR Deletion Completed">
        <div class="space-y-4">
            <x-alert title="Success" description="The user's data has been successfully deleted and anonymized according to GDPR requirements." class="alert-success" />
            
            <div class="bg-base-200 p-4 rounded">
                <h4 class="font-semibold mb-2">Actions Performed:</h4>
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    <li>User profile anonymized (name changed to "Deleted User")</li>
                    <li>Email address anonymized</li>
                    <li>User settings removed</li>
                    <li>Active sessions cleared</li>
                    <li>Personal data fields cleared (GCA, staff positions)</li>
                    <li>Deletion logged for compliance</li>
                </ul>
            </div>
        </div>
        
        <x-slot:actions>
            <x-button label="Close" wire:click="closeSuccessModal" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>