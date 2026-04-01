<tbody id="userTableBody">
    @forelse($challenges as $challenge)
        <tr>
            <td>{{ $challenge->title }}</td>
            <td>{{ $challenge->description }}</td>
            <td>
                @if (auth('admin')->user()->hasDirectPermission('manage inspiration challenges user requests'))
                    <!-- Accept Button -->
                    <form action="{{ route('admin.inspiration-challenges.pending.status', [$challenge->id, 'accept']) }}"
                        onsubmit="addPoints(event,this,true)" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="points" id="points-status" value="">
                        <input type="hidden" name="description" class="description"
                            value="{{ $challenge?->description }}">
                        <input type="hidden" name="guideline_text" id="guideline_text" value="">
                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                    </form>
                    <!-- Reject Button -->
                    <form
                        action="{{ route('admin.inspiration-challenges.pending.status', [$challenge->id, 'reject']) }}"
                        onsubmit="addPoints(event,this)" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                    </form>
                @else
                    <span class="badge badge-warning">Pending</span>
                @endif
            </td>
            <td>
                <div class="dropdown">
                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                        role="button" data-toggle="dropdown">
                        <i class="dw dw-more"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                        <a class="dropdown-item"
                            href="{{ route('admin.inspiration-challenges.pending.details', [$challenge->id]) }}"><i
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
