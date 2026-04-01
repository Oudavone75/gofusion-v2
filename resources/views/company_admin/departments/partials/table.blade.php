<tbody id ="userTableBody">
    @forelse($users as $user)
        <tr>
            <td>{{ $user->fullname }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->job_title ?? 'Not Available' }}</td>
            <td>{{ $user->company?->name ?? 'Not Available' }}</td>
            <td>{{ $user->company?->mode->name ?? 'Not Available' }}</td>
            <td>{{ $user->registeration_date ?? 'Not Available' }}</td>
            {{-- <td>
                <div class="dropdown">
                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                        role="button" data-toggle="dropdown">
                        <i class="dw dw-more"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                        <a class="dropdown-item" href="#" data-id="{{ $user->id }}"
                            data-name="{{ $user->title }}"
                            data-url="{{ route('company_admin.departments.department-users.delete', $user->id) }}"
                            onClick="deleteRecord(this)">
                            <i class="icon-copy fa fa-trash" aria-hidden="true"></i> Delete</a>
                    </div>
                </div>
            </td> --}}
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
