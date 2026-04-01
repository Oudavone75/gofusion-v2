<tbody id="citizenTableBody">
    @forelse($citizens as $citizen)
        <tr>
            <td>{{ $citizen->full_name }}</td>
            <td>{{ $citizen->email }}</td>
            <td>{{ $citizen->city }}</td>
            <td>{{ $citizen->registeration_date }}</td>

            @if (auth('admin')->user()->hasDirectPermission('manage citizens status'))
                <td>
                    <input type="checkbox" class="switch-btn"
                        data-url="{{ route('admin.citizens.toggle-status', $citizen->id) }}"
                        {{ $citizen->status == 'active' ? 'checked' : '' }}>
                </td>
            @else
                <td>
                    <span
                        class="badge badge-{{ $citizen->status == 'active' ? 'success' : ($citizen->status == 'pending' ? 'warning' : 'danger') }} d-inline px-2 py-1">
                        {{ $citizen->status ?? '—' }}
                    </span>
                </td>
            @endif
            @if (auth('admin')->user()->hasDirectPermission('delete citizens'))
                <td>
                    <div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                            role="button" data-toggle="dropdown">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="#" data-id="{{ $citizen->id }}"
                                data-name="{{ $citizen->title }}"
                                data-url="{{ route('admin.citizens.delete', $citizen->id) }}"
                                onClick="deleteRecord(this)">
                                <i class="icon-copy fa fa-trash" aria-hidden="true"></i> Delete</a>
                        </div>
                    </div>
                </td>
            @else
                <td></td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
