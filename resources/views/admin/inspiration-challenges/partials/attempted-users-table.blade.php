<tbody id="userTableBody">
    @forelse($challengesPoints as $challengesPoint)
        <tr>
            <td>{{ $challengesPoint->user->fullname }}</td>
            <td>{{ $challengesPoint->user->email }}</td>
            <td>{{ $challengesPoint->challengeStep->company_id !== null ? $challengesPoint->user->company?->name : 'Not Available' }}
            </td>
            <td>{{ $challengesPoint->challengeStep->department_id !== null ? $challengesPoint->user->department?->name : 'Not Available' }}
            </td>
            <td>{{ $challengesPoint->points }}</td>
        @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
