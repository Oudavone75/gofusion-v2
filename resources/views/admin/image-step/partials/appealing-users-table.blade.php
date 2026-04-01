@forelse($users as $user)
    @php
        $filteredAttempt = $user->image_attempts
            ->where('go_session_step_id', $image_detail->go_session_step_id)
            ->first();
    @endphp

    <tr>
        <td>{{ $user->fullname }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $filteredAttempt?->goSessionStep->goSession->campaignSeason->title ?? 'Not Available' }}</td>

        @if ($type == 'campaign')
            <td>{{ $user->company?->name ?? 'Not Available' }}</td>
        @endif

        @if ($type == 'campaign')
            <td>{{ $user->department?->name ?? 'Not Available' }}</td>
        @endif

        <td>{{ $filteredAttempt?->points }}</td>

        <td>
            @if (auth('admin')->user()->hasDirectPermission('manage challenges user requests'))
                <button onclick="changeAppealingStatus({{ $image_step->id }}, {{ $image_detail->points }}, 'approve')"
                    id="approve-btn-{{ $image_step->id }}"
                    data-url="{{ route('admin.images.appealing-users.change-status', $image_step->id) }}"
                    class="btn btn-success btn-sm">
                    Approve
                </button>
                <button onclick="changeAppealingStatus({{ $image_step->id }}, 0, 'reject')"
                    id="reject-btn-{{ $image_step->id }}"
                    data-url="{{ route('admin.images.appealing-users.change-status', $image_step->id) }}"
                    class="btn btn-danger btn-sm">
                    Reject
                </button>
            @else
                <span class="badge badge-warning">Pending</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center text-muted">No data available.</td>
    </tr>
@endforelse
