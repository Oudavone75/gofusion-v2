<div class="row mt-4">
    <div class="col-12">
        <h5 class="mb-3">Roles</h5>

        <!-- Role Tabs -->
        <div class="role-tabs mb-3">
            @foreach ($roles as $index => $role)
                <button type="button" class="btn role-tab {{ $index === 0 ? 'active' : '' }}"
                    data-role="{{ $role->name }}" onclick="switchRole('{{ $role->name }}', event)">
                    {{ $role->name }}
                </button>
            @endforeach
        </div>

        <input type="hidden" name="role" id="selected-role" value="{{ $roles->first()->name ?? '' }}">

        <!-- Permissions Section -->
        <h5 class="mb-3 mt-4">Permissions</h5>

        @foreach ($roles as $role)
            <div class="permissions-container {{ $loop->first ? 'active' : '' }}"
                id="permissions-{{ Str::slug($role->name) }}">
                <div class="row">
                    @php
                        // Get all permission IDs for this role
                        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

                        // Group permissions by module based on sidebar structure
                        $permissionGroups = [
                            'Citizens Management' => [],
                            'Company Management' => [],
                            'Department Management' => [],
                            'Campaign Management' => [],
                            'Session Management' => [],
                            'Import Management' => [],
                            'Step Management' => [],
                            'Quiz Management' => [],
                            'Challenge Management' => [],
                            'SpinWheel Management' => [],
                            'Survey & Feedback' => [],
                            'Inspiration Challenges' => [],
                            // 'News Category' => [],
                            // 'News Feed' => [],
                            'Rewards Management' => [],
                            'Contact Requests' => [],
                            'Gallery Management' => [],
                            'Notification Management' => [],
                            'User Management' => [],
                            'Post Management' => [],
                            'Carbon Assessment Management' => [],
                        ];

                        // Use all permissions instead of just role permissions
                        foreach ($permissions as $permission) {
                            $name = $permission->name;

                            if (str_contains($name, 'citizen')) {
                                $permissionGroups['Citizens Management'][] = $permission;
                            } elseif (str_contains($name, 'companies')) {
                                $permissionGroups['Company Management'][] = $permission;
                            } elseif (str_contains($name, 'department')) {
                                $permissionGroups['Department Management'][] = $permission;
                            } elseif (str_contains($name, 'campaign')) {
                                $permissionGroups['Campaign Management'][] = $permission;
                            } elseif (str_contains($name, 'session')) {
                                $permissionGroups['Session Management'][] = $permission;
                            } elseif (str_contains($name, 'import')) {
                                $permissionGroups['Import Management'][] = $permission;
                            } elseif (str_contains($name, 'steps')) {
                                $permissionGroups['Step Management'][] = $permission;
                            } elseif (str_contains($name, 'quiz')) {
                                $permissionGroups['Quiz Management'][] = $permission;
                            } elseif (str_contains($name, 'challenge') && !str_contains($name, 'inspiration')) {
                                $permissionGroups['Challenge Management'][] = $permission;
                            } elseif (str_contains($name, 'spinwheel')) {
                                $permissionGroups['SpinWheel Management'][] = $permission;
                            } elseif (str_contains($name, 'survey') || str_contains($name, 'feedback')) {
                                $permissionGroups['Survey & Feedback'][] = $permission;
                            } elseif (str_contains($name, 'inspiration')) {
                                $permissionGroups['Inspiration Challenges'][] = $permission;
                            }
                            // elseif (str_contains($name, 'news') && str_contains($name, 'categor')) {
                            //     $permissionGroups['News Category'][] = $permission;
                            // } elseif (str_contains($name, 'news') && str_contains($name, 'feed')) {
                            //     $permissionGroups['News Feed'][] = $permission;
                            // }
                            elseif (str_contains($name, 'reward')) {
                                $permissionGroups['Rewards Management'][] = $permission;
                            } elseif (str_contains($name, 'contact')) {
                                $permissionGroups['Contact Requests'][] = $permission;
                            } elseif (str_contains($name, 'gallery')) {
                                $permissionGroups['Gallery Management'][] = $permission;
                            } elseif (str_contains($name, 'notification')) {
                                $permissionGroups['Notification Management'][] = $permission;
                            } elseif (str_contains($name, 'user')) {
                                $permissionGroups['User Management'][] = $permission;
                            } elseif (str_contains($name, 'posts') || str_contains($name, 'reported users')) {
                                $permissionGroups['Post Management'][] = $permission;
                            } elseif (str_contains($name, 'carbon assessment')) {
                                $permissionGroups['Carbon Assessment'][] = $permission;
                            }
                        }

                        // Remove empty groups
                        $permissionGroups = array_filter($permissionGroups, function ($group) {
                            return !empty($group);
                        });
                    @endphp

                    @foreach ($permissionGroups as $category => $categoryPermissions)
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                            <div class="permission-group">
                                <button type="button"
                                    class="btn btn-light btn-block text-left permission-dropdown d-flex justify-content-between align-items-center"
                                    onclick="togglePermissionDropdown(this)">
                                    <span>{{ $category }}</span>
                                    <i class="icon-copy dw dw-down-arrow-1"></i>
                                </button>
                                <div class="permission-list" style="display: none;">
                                    @foreach ($categoryPermissions as $permission)
                                        @php
                                            $isChecked = in_array($permission->id, $rolePermissionIds);
                                        @endphp
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input permission-checkbox"
                                                id="permission-{{ Str::slug($role->name) }}-{{ $permission->id }}"
                                                name="permissions[{{ Str::slug($role->name) }}][]"
                                                value="{{ $permission->id }}"
                                                data-role="{{ $role->name }}" {{ $isChecked ? 'checked' : '' }}
                                                {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="permission-{{ Str::slug($role->name) }}-{{ $permission->id }}">
                                                {{ ucfirst($permission->name) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
