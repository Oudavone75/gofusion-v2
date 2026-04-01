<tbody id="employeeTableBody">
    @forelse($employees as $employee)
        <tr>
            <td>{{ $employee->full_name ?? 'Not Available' }}</td>
            <td>{{ $employee->email ?? 'Not Available' }}</td>
            <td>{{ $employee->city ?? 'Not Available' }}</td>
            <td>{{ $employee->registeration_date ?? 'Not Available' }}</td>
            <td>
                <input type="checkbox" class="switch-btn"
                    data-url="{{ route('company_admin.employees.toggle-status', $employee->id) }}"
                    {{ $employee->status == 'active' ? 'checked' : '' }}>
            </td>
            {{-- <td>
                <div class="dropdown">
                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                        role="button" data-toggle="dropdown">
                        <i class="dw dw-more"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                        <a class="dropdown-item" href="#" data-id="{{ $employee->id }}"
                            data-name="{{ $employee->title }}"
                            data-url="{{ route('company_admin.employees.delete', $employee->id) }}"
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
