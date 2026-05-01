@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1>Audit Trail</h1>
        <p class="text-muted mb-0">Review attendance edits, account changes, curriculum updates, and other tracked actions.</p>
    </div>
</div>

<section class="panel">
    <div class="section-title">
        <h2>System Activity</h2>
        <span class="chip-light">{{ $logs->total() }} logs</span>
    </div>
    <form method="GET" action="{{ route('admin.audit.index') }}" class="search-bar">
        <input class="form-control" name="q" value="{{ $search }}" placeholder="Live search action, user, target, or description" data-live-search data-live-search-target="#auditLogs tbody tr">
        <button class="btn btn-outline-primary">Search</button>
        @if($search !== '')
            <a class="btn btn-outline-primary" href="{{ route('admin.audit.index') }}">Clear</a>
        @endif
    </form>
    <div class="table-responsive">
        <table class="table align-middle" id="auditLogs">
            <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Description</th><th>Target</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td>
                        <span class="record-name">{{ $log->user?->name ?? 'System' }}</span>
                        <span class="meta-line">{{ $log->user?->employee_id ?? 'N/A' }}</span>
                    </td>
                    <td><span class="badge text-bg-secondary">{{ str_replace('_', ' ', $log->action) }}</span></td>
                    <td>{{ $log->description }}</td>
                    <td><span class="meta-line">{{ class_basename($log->auditable_type ?? '') }} {{ $log->auditable_id }}</span></td>
                    <td>{{ $log->ip_address ?: 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center empty-state py-4">No audit logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</section>
@endsection
