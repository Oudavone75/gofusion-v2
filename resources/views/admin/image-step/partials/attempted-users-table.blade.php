<tbody id="userTableBody">
    @forelse($users as $user)
        @php
            $filteredAttempt = $user->image_attempts
                ->where('go_session_step_id', $image_detail->go_session_step_id)
                ->first();
        @endphp
        <tr>
            <td>{{ $user->fullname }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $filteredAttempt?->goSessionStep->goSession->campaignSeason->title ?? 'Not Available' }}
            </td>
            @if ($type == 'campaign')
                <td>{{ $user->company?->name ?? 'Not Available' }}</td>
            @endif
            @if ($type == 'campaign')
                <td>{{ $user->department?->name ?? 'Not Available' }}</td>
            @endif
            <td>{{ $filteredAttempt?->points }}</td>
            <td>
                @if (isset($filteredAttempt) && $filteredAttempt?->status == 'completed')
                    <span class="badge badge-success">Completed</span>
                @elseif(isset($filteredAttempt) && $filteredAttempt?->status == 'appealing')
                    <span class="badge badge-warning">Appealing</span>
                @else
                    <span class="badge badge-secondary">N/A</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($user->registeration_date)->format('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($filteredAttempt?->created_At)->format('d M Y') }}</td>
            <td>
                <div class="dropdown">
                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                        role="button" data-toggle="dropdown">
                        <i class="dw dw-more"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                        <a class="dropdown-item"
                            href="{{ route('admin.images-step.attempted-user.details', [$user->id, $filteredAttempt->go_session_step_id, activeCampaignSeasonFilter()]) }}"><i
                                class="dw dw-eye"></i> View</a>
                    </div>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
