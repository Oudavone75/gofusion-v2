<tbody id="userTableBody">
    @forelse($carbonFootPrintAssessments as $assessment)
        <tr>
            <td>{{ $assessment->user->full_name }}</td>
            <td>{{ $assessment->user->email }}</td>
            <td>{{ \Carbon\Carbon::parse($assessment->created_at)->format('d F Y') }}</td>
            <td>{{ $assessment->water_value . ' ' . $assessment->water_unit }}</td>
            <td>{{ $assessment->carbon_value . ' ' . $assessment->carbon_unit }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted">No data available.</td>
        </tr>
    @endforelse
</tbody>
