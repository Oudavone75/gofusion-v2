<tbody id="userTableBody">
    @forelse($users as $user)
        <tr>
            <td>{{ $user->fullname }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->quiz_attempts[0]?->quiz?->campaignSeason?->title ?? 'Not Available' }}
            </td>
            @if ($type == 'campaign')
                <td>{{ $user->company?->name ?? 'Not Available' }}</td>
            @endif
            @if ($type == 'campaign')
                <td>{{ $user->department?->name ?? 'Not Available' }}</td>
            @endif
            <td class="text-center">{{ $user->quiz_attempts[0]?->points }}</td>
            <td>
                {{ \Carbon\Carbon::parse($user->registeration_date)->format('d M Y') }}</td>
            <td>
                {{ \Carbon\Carbon::parse($user->quiz_attempts[0]?->created_At)->format('d M Y') }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ $type == 'campaign' ? '8' : '6' }}" class="text-center text-muted py-4">
                No data available.
            </td>
        </tr>
    @endforelse
</tbody>
