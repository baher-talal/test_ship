<div class="row g-3">
    <div class="col-12"><h6 class="text-primary border-bottom pb-2">{{ __('Basic information') }}</h6></div>
    @include('partials.input', ['name' => 'name', 'label' => __('Name'), 'required' => true, 'value' => $user->name])
    @include('partials.input', ['name' => 'email', 'label' => __('Email'), 'type' => 'email', 'required' => true, 'value' => $user->email])
    @include('partials.input', ['name' => 'phone', 'label' => __('Phone'), 'value' => $user->phone])
    @include('partials.select', ['name' => 'work_type', 'label' => __('Workplace'), 'required' => true, 'options' => \App\Models\User::WORK_TYPES, 'value' => $user->work_type])
    @include('partials.input', ['name' => 'password', 'label' => $user->exists ? __('New password (leave empty to keep it)') : __('Password'), 'type' => 'password', 'required' => !$user->exists])
    @include('partials.input', ['name' => 'password_confirmation', 'label' => __('Confirm password'), 'type' => 'password'])
    @include('partials.switch', ['name' => 'is_active', 'label' => __('Account is active'), 'value' => $user->is_active])

    <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">{{ __('Branches') }} <small class="text-muted fw-normal">({{ __('Optional for remote or head office staff') }})</small></h6></div>
    @include('partials.select', ['name' => 'branch_id', 'label' => __('Primary branch'), 'options' => $branches->pluck('name', 'id')->all(), 'value' => $user->branch_id, 'placeholder' => __('No primary branch')])
    <div class="col-md-6">
        <label class="form-label">{{ __('Additional branches (for staff moving between branches)') }}</label>
        <div class="border rounded p-2" style="max-height:140px;overflow:auto">
            @forelse($branches as $b)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="branches[]" value="{{ $b->id }}" id="br_{{ $b->id }}"
                           {{ in_array($b->id, old('branches', $selectedBranches)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="br_{{ $b->id }}">{{ $b->name }}</label>
                </div>
            @empty
                <span class="text-muted small">{{ __('No branches') }}</span>
            @endforelse
        </div>
        @error('branches')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12"><div class="form-text mt-0">{{ __('Shipments and customers are shown according to the branches selected here, unless the user is granted the permission') }} "{{ __('View data of all branches') }}".</div></div>

    <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">{{ __('Roles (by job hierarchy)') }} <small class="text-muted fw-normal">{{ __('More than one role can be selected') }}</small></h6></div>
    <div class="col-12">
        <div class="row g-2">
            @foreach($roles as $role)
                <div class="col-md-6 col-xl-4">
                    <label class="border rounded p-2 d-flex gap-2 h-100 role-card" for="role_{{ $role->id }}" style="cursor:pointer">
                        <input class="form-check-input mt-1 role-check" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                               data-grants="{{ json_encode($roleGrants[$role->id] ?? []) }}"
                               {{ in_array($role->id, old('roles', $selectedRoles)) ? 'checked' : '' }}>
                        <span>
                            <span class="fw-semibold">{{ __($role->name) }}</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Level') }} {{ $role->level }}</span>
                            @if($role->description)<span class="d-block small text-muted">{{ __($role->description) }}</span>@endif
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
        @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 mt-4">
        <h6 class="text-primary border-bottom pb-2">{{ __('Additional permissions') }} <small class="text-muted fw-normal">({{ __('Green permissions granted by the selected roles are checked automatically') }})</small></h6>
    </div>
    <div class="col-12">
        @include('partials.permission-picker', ['permissionGroups' => $permissionGroups, 'selected' => $selectedPermissions, 'grantable' => $grantable])
        @error('permissions')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var perms = document.querySelectorAll('.perm-check');
        perms.forEach(function (p) {
            p.addEventListener('change', function () { if (!p.disabled) p.dataset.own = p.checked ? '1' : '0'; });
        });
        function refresh() {
            var granted = {};
            document.querySelectorAll('.role-check:checked').forEach(function (c) {
                JSON.parse(c.dataset.grants || '[]').forEach(function (k) { granted[k] = true; });
            });
            perms.forEach(function (p) {
                var label = p.nextElementSibling;
                if (granted[p.dataset.key]) {
                    p.checked = true; p.disabled = true; label.classList.add('text-success', 'fw-semibold');
                } else {
                    label.classList.remove('text-success', 'fw-semibold');
                    if (p.dataset.locked === '1') { p.disabled = true; p.checked = false; return; }
                    p.disabled = false; p.checked = p.dataset.own === '1';
                }
            });
        }
        document.querySelectorAll('.role-check').forEach(function (c) { c.addEventListener('change', refresh); });
        refresh();
    })();
</script>
@endpush
