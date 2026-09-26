<?php
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

use App\Models\Admin;
use App\Models\User;
use App\Enums\AdminPermission;
use App\Services\AdminService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    /* Search */
    public string $search = '';

    /*
     * Edit modal. The administrator edited and the permissions ticked are
     * written by the server only (the checkboxes call togglePermission()):
     * locked, a forged update gets a 419 (CLAUDE.md, "Conventions & gotchas")
     */
    public bool $editModal = false;
    #[Locked]
    public ?Admin $editingAdmin = null;
    #[Locked]
    public array $selectedPermissions = [];

    /* Delete confirmation (the administrator is set by the server only) */
    public bool $deleteModal = false;
    #[Locked]
    public ?Admin $deletingAdmin = null;

    /* Refresh tracking */
    public string $refreshKey = '';

    /* Permissions expansion tracking */
    public array $expandedPermissions = []; // Format: ['admin_id' => ['category1', 'category2']]

    /* Table headers */
    public array $headers = [
        ['key' => 'vid', 'label' => 'VID', 'class' => 'font-bold max-w-20'],
        ['key' => 'name', 'label' => 'Administrator', 'class' => 'text-left', 'sortable' => false],
        ['key' => 'permissions_count', 'label' => 'Permissions', 'class' => 'text-left max-w-52', 'sortable' => false],
    ];
    public array $sortBy = ['column' => 'vid', 'direction' => 'asc'];
    
    /* Table data */
    public function getAdminsProperty()
    {
        return Admin::query()
                        ->with('user')
                        ->when($this->search, function(Builder $q) {
                            return $q->whereHas('user', function(Builder $userQuery) {
                                $userQuery->where('first_name', 'like', "%{$this->search}%")
                                         ->orWhere('last_name', 'like', "%{$this->search}%")
                                         ->orWhere('vid', 'like', "%{$this->search}%");
                            });
                        })
                        ->orderBy(...array_values($this->sortBy))
                        ->get();
    }

    /* Data search */    
    public function mount($search = '')
    {
        $this->search = $search;
    }

    #[On('admin-added')]
    public function refreshTable(): void
    {
        $this->mount();
    }

    /* Toggle permission category expansion */
    public function togglePermissionCategory(int $adminId, string $category)
    {
        if (!isset($this->expandedPermissions[$adminId])) {
            $this->expandedPermissions[$adminId] = [];
        }

        $index = array_search($category, $this->expandedPermissions[$adminId]);
        
        if ($index !== false) {
            // Remove category from expanded list
            unset($this->expandedPermissions[$adminId][$index]);
            $this->expandedPermissions[$adminId] = array_values($this->expandedPermissions[$adminId]);
        } else {
            // Add category to expanded list
            $this->expandedPermissions[$adminId][] = $category;
        }
    }

    /* Check if permission category is expanded */
    public function isCategoryExpanded(int $adminId, string $category): bool
    {
        return isset($this->expandedPermissions[$adminId]) && 
               in_array($category, $this->expandedPermissions[$adminId]);
    }

    /* Get admin permission categories */
    public function getAdminPermissionCategories(Admin $admin): array
    {
        $permissions = $admin->permissions ?? [];
        $categories = [];
        
        foreach ($permissions as $permissionValue) {
            $permission = AdminPermission::tryFrom($permissionValue);
            if ($permission) {
                $category = $permission->category();
                if (!isset($categories[$category])) {
                    $categories[$category] = [];
                }
                $categories[$category][] = $permission;
            }
        }
        
        return $categories;
    }

    /*
     * The administrator acting: read again from the session on every
     * request, never kept in the component's state, which a snapshot
     * replayed by another account would carry
     */
    private function actor(): ?Admin
    {
        return Admin::where('vid', Auth::user()->vid)->first();
    }

    /* Permission values the acting administrator may grant or withdraw (AdminService::grantable()) */
    #[Computed]
    public function grantable(): array
    {
        return app(AdminService::class)->grantable($this->actor());
    }

    /* Security helper method using existing infrastructure */
    private function checkPermissions(): bool
    {
        return $this->actor()?->canString('admins_edit_permissions') ?? false;
    }

    /* Edit admin permissions */
    public function editAdmin(int $adminId, AdminService $service)
    {
        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to modify administrators');
            return;
        }

        // Kept only once every check passed: the component never holds an administrator it refused
        $admin = Admin::with('user')->find($adminId);
        if (!$admin) {
            $this->error('Administrator not found');
            return;
        }

        $actor = $this->actor();

        if ($admin->is($actor)) {
            $this->error('You cannot modify your own permissions');
            return;
        }

        // The table offers no edit button on an administrator beyond the acting one's rights
        throw_unless($service->canActOnAdmin($actor, $admin), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);

        // Load current permissions
        $this->editingAdmin = $admin;
        $this->selectedPermissions = $admin->permissions ?? [];
        $this->editModal = true;
    }

    /* Update admin permissions */
    public function savePermissions(AdminService $service)
    {
        if (!$this->editingAdmin) {
            return;
        }

        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to modify administrators');
            return;
        }

        // Read again: the administrator may have gained rights since the window opened
        $actor = $this->actor();
        $target = Admin::with('user')->findOrFail($this->editingAdmin->id);

        // The window offers neither the acting administrator's own record, nor an
        // administrator beyond their rights, nor a permission they do not hold
        throw_if($target->is($actor), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);
        throw_unless(
            $service->canActOnAdmin($actor, $target) && $service->withinRights($actor, $this->selectedPermissions),
            AccessDeniedHttpException::class,
            AdminService::BEYOND_RIGHTS
        );

        // Update permissions (AdminService checks the ceiling again)
        try {
            $service->updatePermissions($target, $this->selectedPermissions, $actor);
        } catch (\Exception $e) {
            $this->error(e($e->getMessage()));
            return;
        }

        // MaryUI renders toast titles as HTML (x-html): escape user-controlled values
        $this->success("Permissions updated for " . e($target->user->full_name));
        $this->closeEditModal();
    }

    /* Close edit modal */
    public function closeEditModal()
    {
        $this->editModal = false;
        $this->editingAdmin = null;
        $this->selectedPermissions = [];
    }

    /* Delete admin confirmation */
    public function confirmDelete(int $adminId, AdminService $service)
    {
        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to remove administrators');
            return;
        }

        // Kept only once every check passed: the component never holds an administrator it refused
        $admin = Admin::with('user')->find($adminId);
        if (!$admin) {
            $this->error('Administrator not found');
            return;
        }

        $actor = $this->actor();

        if ($admin->is($actor)) {
            $this->error('You cannot remove yourself');
            return;
        }

        // The table offers no delete button on an administrator beyond the acting one's rights
        throw_unless($service->canActOnAdmin($actor, $admin), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);

        $this->deletingAdmin = $admin;
        $this->deleteModal = true;
    }

    /* Delete admin */
    public function deleteAdmin(AdminService $service)
    {
        if (!$this->deletingAdmin) {
            return;
        }

        if (!$this->checkPermissions()) {
            $this->error('Insufficient permissions to remove administrators');
            return;
        }

        // Read again: the administrator may have gained rights since the window opened
        $actor = $this->actor();
        $target = Admin::with('user')->findOrFail($this->deletingAdmin->id);

        // The table offers neither the acting administrator's own record nor an administrator beyond their rights
        throw_if($target->is($actor), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);
        throw_unless($service->canActOnAdmin($actor, $target), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);

        $adminName = $target->user->full_name;

        // AdminService checks the ceiling again
        try {
            $service->removeAdmin($target, $actor);
        } catch (\Exception $e) {
            $this->error(e($e->getMessage()));
            return;
        }

        // MaryUI renders toast titles as HTML (x-html): escape user-controlled values
        $this->success("Administrator " . e($adminName) . " removed successfully");
        $this->closeDeleteModal();
    }

    /* Close delete modal */
    public function closeDeleteModal()
    {
        $this->deleteModal = false;
        $this->deletingAdmin = null;
    }

    /* Get all permission categories for modal */
    public function getPermissionCategories(): array
    {
        $categories = [];
        
        foreach (AdminPermission::cases() as $permission) {
            $category = $permission->category();
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = $permission;
        }
        
        return $categories;
    }

    /* Check if permission is selected */
    public function isPermissionSelected(string $permissionValue): bool
    {
        return in_array($permissionValue, $this->selectedPermissions);
    }

    /* Toggle individual permission */
    public function togglePermission(string $permissionValue)
    {
        // The window disables the permissions the acting administrator does not hold
        throw_unless(in_array($permissionValue, $this->grantable, true), AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);

        if ($this->isPermissionSelected($permissionValue)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionValue]);
        } else {
            $this->selectedPermissions[] = $permissionValue;
        }
        
        $this->selectedPermissions = array_values($this->selectedPermissions);
    }

    /* Check if super admin is selected */
    public function isSuperAdminSelected(): bool
    {
        return $this->isPermissionSelected('*');
    }

    /* Permission values of a category the acting administrator may grant or withdraw */
    public function getGrantableInCategory(string $category): array
    {
        $categoryPermissionValues = array_map(fn($p) => $p->value, $this->getPermissionCategories()[$category] ?? []);

        return array_values(array_intersect($categoryPermissionValues, $this->grantable));
    }

    /* Check if category is fully selected, as far as the acting administrator may tick it */
    public function isCategoryFullySelected(string $category): bool
    {
        $grantable = $this->getGrantableInCategory($category);

        return count($grantable) > 0 && array_diff($grantable, $this->selectedPermissions) === [];
    }

    /* Check if category is partially selected */
    public function isCategoryPartiallySelected(string $category): bool
    {
        $categoryPermissions = $this->getPermissionCategories()[$category] ?? [];
        $selectedCount = 0;
        
        foreach ($categoryPermissions as $permission) {
            if ($this->isPermissionSelected($permission->value)) {
                $selectedCount++;
            }
        }
        
        return $selectedCount > 0 && $selectedCount < count($categoryPermissions);
    }

    /* Toggle all permissions in a category: those the acting administrator holds, and only those */
    public function toggleCategoryPermissions(string $category)
    {
        $categoryPermissionValues = $this->getGrantableInCategory($category);

        // The window disables a category without any permission the acting administrator holds
        throw_if($categoryPermissionValues === [], AccessDeniedHttpException::class, AdminService::BEYOND_RIGHTS);

        // New keys for the individual checkboxes, so that they show the new selection
        $this->refreshKey = uniqid();

        if ($this->isCategoryFullySelected($category)) {
            // Remove them all when unchecking
            $this->selectedPermissions = array_diff($this->selectedPermissions, $categoryPermissionValues);
        } else {
            // Add the missing ones
            foreach ($categoryPermissionValues as $permValue) {
                if (!in_array($permValue, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $permValue;
                }
            }
        }

        $this->selectedPermissions = array_values($this->selectedPermissions);
    }

    /* Get selected granular permission count for a category */
    public function getSelectedGranularCount(string $category): int
    {
        $categoryPermissions = $this->getPermissionCategories()[$category] ?? [];
        $granularPermissions = array_filter($categoryPermissions, fn($p) => str_contains($p->value, '_'));
        
        return array_sum(array_map(
            fn($p) => $this->isPermissionSelected($p->value) ? 1 : 0, 
            $granularPermissions
        ));
    }

    /* Get total granular permission count for a category */
    public function getTotalGranularCount(string $category): int
    {
        $categoryPermissions = $this->getPermissionCategories()[$category] ?? [];
        $granularPermissions = array_filter($categoryPermissions, fn($p) => str_contains($p->value, '_'));
        
        return count($granularPermissions);
    }

    /* Get modal grid layout classes based on number of categories */
    public function getModalGridLayoutClasses(): string
    {
        return 'grid grid-cols-1 md:grid-cols-2 gap-6';
    }

    /* Get modal card classes for optimal layout */
    public function getModalCardClasses(string $category, int $index): string
    {
        $categoryCount = count($this->getPermissionCategories());
        $baseClasses = 'card bg-base-100 border ' . AdminPermission::categoryColorProp($category, 'border-l') . ' border-l-4';        
        return $baseClasses;
    }

    /* Volt with() method */
    public function with(AdminService $service): array
    {
        $admins = $this->admins;
        $actor = $this->actor();

        return [
            'admins' => $admins,
            'search' => $this->search,
            'permissionCategories' => $this->getPermissionCategories(),
            'refreshKey' => $this->refreshKey,
            // The acting administrator's own record: shown, never acted on
            'actorId' => $actor?->id,
            // Administrators holding a permission the acting one lacks: shown, never acted on (AdminService::canActOnAdmin())
            'beyond' => $admins->reject(fn (Admin $admin) => $service->canActOnAdmin($actor, $admin))->pluck('id')->all(),
        ];
    }
}; 
?>

<div>
    {{-- Admins Table --}}
    <x-table 
        :headers="$headers" 
        :rows="$admins" 
        :sortBy="$sortBy" 
        no-hover
        class="sm:overflow-x-clip mx-auto">

        @scope('cell_vid', $admin)
            <a href="https://www.ivao.aero/Member.aspx?Id={{ $admin->vid }}" target="_blank">
                <span class="badge badge-primary py-4">{{ $admin->vid }}</span>
            </a>
        @endscope

        @scope('cell_name', $admin)
            {{ $admin->user->full_name }}
        @endscope

        @scope('cell_permissions_count', $admin)
            @php
                $permissions = $admin->permissions ?? [];
                $isSuperAdmin = in_array('*', $permissions);
                $hasNoPermissions = empty($permissions);
                $categories = $this->getAdminPermissionCategories($admin);
            @endphp

            {{-- Super Admin case --}}
            @if($isSuperAdmin)
                <x-badge value="SUPER ADMIN" class="badge-accent" />
            
            {{-- No permissions case --}}
            @elseif($hasNoPermissions)
                <x-badge value="None" class="badge-neutral" />
            
            {{-- Regular permissions - show categories --}}
            @else
                <div class="space-y-1">
                    @foreach($categories as $category => $categoryPermissions)
                        <div class="flex items-center gap-2">
                            {{-- Category name with click handler --}}
                            <div 
                                class="flex items-center gap-1 cursor-pointer hover:bg-base-200 px-2 py-1 rounded transition-colors"
                                wire:click="togglePermissionCategory({{ $admin->id }}, '{{ $category }}')"
                            >
                                <x-icon 
                                    name="{{ AdminPermission::categoryIcon($category) }}" 
                                    class="w-4 h-4 {{ AdminPermission::categoryColorProp($category, 'text') }}" 
                                />
                                <span class="text-sm font-medium">{{ $category }}</span>
                                <x-icon 
                                    name="{{ $this->isCategoryExpanded($admin->id, $category) ? 'phosphor.caret-down' : 'phosphor.caret-right' }}" 
                                    class="w-3 h-3 text-base-content/60 transition-transform" 
                                />
                            </div>
                        </div>

                        {{-- Expanded permissions for this category --}}
                        @if($this->isCategoryExpanded($admin->id, $category))
                            <div class="ml-6 space-y-1 pb-2">
                                @foreach($categoryPermissions as $permission)
                                    <div class="flex items-center gap-2 text-xs text-base-content/70">
                                        <x-icon name="{{ $permission->icon() }}" class="w-3 h-3" />
                                        <span>{{ $permission->description() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endscope

        @scope('actions', $admin, $beyond, $actorId)
            @if($admin->id !== $actorId)
                {{-- An administrator holding a permission the acting one lacks: shown, never acted on --}}
                @if(in_array($admin->id, $beyond, true))
                    <x-badge value="Beyond your rights" class="badge-warning whitespace-nowrap" />
                @else
                    <div class="flex gap-2 justify-right">
                        <x-button
                            icon="phosphor.pen"
                            class="btn-outline btn-sm btn-secondary"
                            wire:click="editAdmin({{ $admin->id }})"
                        />
                        <x-button
                            icon="phosphor.trash"
                            class="btn-outline btn-error btn-sm"
                            wire:click="confirmDelete({{ $admin->id }})"
                        />
                    </div>
                @endif
            @endif
        @endscope
    </x-table>

    {{-- Edit Permissions Modal --}}
    <x-modal wire:model="editModal" title="Edit Administrator Permissions" subtitle="Set appropriate permissions" class="backdrop-blur" box-class="w-4/5 max-w-4xl max-h-9/10 mx-auto">        @if($editingAdmin)
            <div class="space-y-6">
                {{-- Admin Info --}}
                <div class="flex items-center gap-4 p-4 bg-base-100 rounded-lg border">
                    <div>
                        <h5 class="font-bold">{{ $editingAdmin->user->full_name }}</h5>
                        <p class="text-sm opacity-70">VID: {{ $editingAdmin->vid }}</p>
                    </div>
                </div>

                {{-- The permissions the acting administrator does not hold are shown, never granted --}}
                @if(count($this->grantable) < count(AdminPermission::cases()))
                    <x-alert title="Some permissions are beyond your rights" description="You can only grant or withdraw the permissions you hold yourself." icon="phosphor.info" class="alert-info" />
                @endif

                {{-- Permissions by Category --}}
                <div class="space-y-6">
                    {{-- Super Admin Permission (Special handling) --}}
                    <div class="card bg-base-100 border border-l-accent border-l-4">
                        <div class="card-body p-4">
                            <h4 class="card-title text-base flex items-center gap-2">
                                <x-icon name="phosphor.crown" class="w-5 h-5 text-accent" />
                                Super Administrator
                            </h4>
                            
                            @php $canGrantAll = in_array('*', $this->grantable, true); @endphp
                            <label @class([
                                'flex items-center gap-3 p-2 rounded',
                                'cursor-pointer hover:bg-base-200' => $canGrantAll,
                                'cursor-not-allowed opacity-50' => ! $canGrantAll,
                            ])>
                                <input 
                                    type="checkbox" 
                                    value="*"
                                    class="checkbox checkbox-sm checkbox-accent"
                                    wire:click="togglePermission('*')"
                                    @checked($this->isPermissionSelected('*'))
                                    @disabled(! $canGrantAll)
                                />
                                <div class="flex-1">
                                    <div class="font-medium text-sm">All permissions</div>
                                </div>
                                <x-icon name="phosphor.crown" class="w-4 h-4 opacity-60" />
                            </label>
                        </div>
                    </div>

                    {{-- Other Categories (Hidden if Super Admin is selected) --}}
                    @if(!$this->isSuperAdminSelected())
                        <div class="{{ $this->getModalGridLayoutClasses() }}">
                            @foreach($permissionCategories as $category => $permissions)
                                @php $index = array_search($category, array_keys($permissionCategories)); @endphp

                                @if($category === AdminPermission::ALL->category())
                                    @continue
                                @endif

                                <div class="{{ $this->getModalCardClasses($category, $index) }}">
                                    <div class="card-body p-2">
                                        @php
                                            $isFullySelected = $this->isCategoryFullySelected($category);
                                            $isPartiallySelected = $this->isCategoryPartiallySelected($category);
                                            // A category without any permission the acting administrator holds is shown, never ticked
                                            $hasGrantable = $this->getGrantableInCategory($category) !== [];
                                        @endphp

                                        {{-- Category Header (Clickable) --}}
                                        <div 
                                            @class([
                                                'card-title text-base flex items-center gap-2 p-2 rounded transition-colors',
                                                'cursor-pointer hover:bg-base-200' => $hasGrantable,
                                                'cursor-not-allowed opacity-50' => ! $hasGrantable,
                                            ])
                                            @if($hasGrantable) wire:click="toggleCategoryPermissions('{{ $category }}')" @endif
                                        >
                                            <input 
                                                type="checkbox" 
                                                class="checkbox checkbox-sm {{ AdminPermission::categoryColorProp($category, 'checkbox') }}"
                                                @checked($isFullySelected)
                                                @if($isPartiallySelected && !$isFullySelected) 
                                                    style="opacity: 0.5;" 
                                                @endif
                                                onclick="event.stopPropagation();"
                                                wire:click="toggleCategoryPermissions('{{ $category }}')"
                                                @disabled(! $hasGrantable)
                                            />
                                            <x-icon name="{{ AdminPermission::categoryIcon($category) }}" class="w-5 h-5" />
                                            <span>{{ $category }}</span>
                                            @if($isPartiallySelected && !$isFullySelected)
                                                <span class="text-xs opacity-60">({{ $this->getSelectedGranularCount($category) }}/{{ $this->getTotalGranularCount($category) }})</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Individual Permissions --}}
										<div>
											@foreach($permissions as $permission)
												@if(str_contains($permission->value, '_'))
													@php $canGrant = in_array($permission->value, $this->grantable, true); @endphp
													<label @class([
														'flex items-center gap-3 p-2 rounded ml-4',
														'cursor-pointer hover:bg-base-200' => $canGrant,
														'cursor-not-allowed opacity-50' => ! $canGrant,
													])>
														<input 
															type="checkbox" 
															value="{{ $permission->value }}"
															class="checkbox checkbox-sm {{ AdminPermission::categoryColorProp($category, 'checkbox') }}"
															wire:click="togglePermission('{{ $permission->value }}')"
															wire:key="perm-{{ $permission->value }}-{{ $this->refreshKey }}"
															@checked($this->isPermissionSelected($permission->value))
															@disabled(! $canGrant)
														/>
														<div class="flex-1">
															<div class="font-medium text-sm">{{ $permission->description() }}</div>
														</div>
														<x-icon name="{{ $permission->icon() }}" class="w-4 h-4 opacity-60" />
													</label>
												@endif
											@endforeach
										</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Message when Super Admin is selected --}}
                        <div class="text-center py-8">
                            <x-icon name="phosphor.crown" class="w-16 h-16 mx-auto text-accent opacity-50 mb-4" />
                            <p class="text-lg font-semibold text-accent">Super Administrator Selected</p>
                            <p class="text-sm opacity-70 mt-2">
                                This user has all permissions.<br>
                                Uncheck "Super Administrator" to manage individual permissions.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <div class="flex justify-between w-full">
                    <x-button label="Cancel" wire:click="closeEditModal" />
                    <x-button label="Save Permissions" class="btn-primary" wire:click="savePermissions" />
                </div>
            </x-slot:actions>
        @endif
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="deleteModal" title="Remove Administrator" class="backdrop-blur" box-class="w-4/5 max-w-4xl max-h-9/10 mx-auto">
        @if($deletingAdmin)
            <div class="space-y-4">
                <x-alert title="Are you sure?" description="You are removing admin status for this user" icon="phosphor.warning"  class="alert-warning" />

                <div class="flex justify-between items-center gap-4 p-4 bg-base-100 rounded-lg border">
                    <p class="font-semibold text-medium">{{ $deletingAdmin->user->full_name }}</p>
                    <p class="text-sm opacity-70">VID: {{ $deletingAdmin->vid }}</p>
                </div>
            </div>

            <x-slot:actions>
                <div class="flex justify-between w-full">
                    <x-button label="Cancel" wire:click="closeDeleteModal" />
                    <x-button label="Remove Administrator" class="btn-error" wire:click="deleteAdmin" />
                </div>
            </x-slot:actions>
        @endif
    </x-modal>
</div>