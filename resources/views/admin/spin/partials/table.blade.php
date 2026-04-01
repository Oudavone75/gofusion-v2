<tbody id="userTableBody">
    @forelse($users as $user)
        <tr>
            <td>{{ $user->fullname }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->spinwheel_attempts[0]?->goSessionStep->goSession->campaignSeason->title ?? 'Not Available' }}
            </td>
            @if ($type == 'campaign')
                <td>{{ $user->company?->name ?? 'Not Available' }}</td>
            @endif
            @if ($type == 'campaign')
                <td>{{ $user->department?->name ?? 'Not Available' }}</td>
            @endif
            <td>{{ $user->spinwheel_attempts[0]?->points }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $user->spinwheel_attempts[0]?->bonus_type)) }}</td>
            <td>{{ $user->spinwheel_attempts[0]?->bonus_value }}</td>
            <td>{{ $user->spinwheel_attempts[0]?->created_at->format('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($user->registeration_date)->format('d M Y') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
