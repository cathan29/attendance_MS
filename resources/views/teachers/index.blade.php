@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Teachers</h1>
        <p class="text-muted mb-0">Create teacher accounts and manage access.</p>
    </div>
</div>
<section class="panel mb-4">
    <h2>Teacher Form</h2>
    @if(session('generated_teacher'))
        <div class="credential-box">
            <strong>Generated Login</strong>
            <span>Employee ID: <b>{{ session('generated_teacher.employee_id') }}</b></span>
            <span>Temporary Password: <b>{{ session('generated_teacher.password') }}</b></span>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.teachers.store') }}" class="row g-3">
        @csrf
        <div class="col-md-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-6"><span class="meta-line">Employee ID and temporary password are generated automatically. Teacher will update email and password on first login.</span></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Save Teacher</button></div>
    </form>
</section>
<section class="panel">
    <div class="section-title">
        <h2>Teacher Accounts</h2>
        <span class="chip-light">{{ $teachers->count() }} teachers</span>
    </div>
    <form method="GET" action="{{ route('admin.teachers.index') }}" class="search-bar">
        <input class="form-control" name="q" value="{{ $search }}" placeholder="Live search employee ID, name, email, or status" data-live-search data-live-search-target="#teacherAccounts tbody tr">
        <button class="btn btn-outline-primary">Search</button>
        @if($search !== '')
            <a class="btn btn-outline-primary" href="{{ route('admin.teachers.index') }}">Clear</a>
        @endif
    </form>
    <div class="section-title password-section-title">
        <h2>Password Section</h2>
        <span class="meta-line">Reset gives a new temporary password and requires first-login update again.</span>
    </div>
    <table class="table align-middle" id="teacherAccounts">
        <thead><tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Status</th><th>Password</th><th></th></tr></thead>
        <tbody>
        @forelse($teachers as $teacher)
            <tr>
                <td>{{ $teacher->employee_id }}</td>
                <td><span class="record-name">{{ $teacher->last_name }}, {{ $teacher->first_name }}</span></td>
                <td>{{ $teacher->email ?: 'Not set' }}</td>
                <td><span class="badge text-bg-{{ $teacher->status === 'active' ? 'success' : 'secondary' }}">{{ $teacher->status }}</span></td>
                <td>
                    <form method="POST" action="{{ route('admin.teachers.reset-password', $teacher) }}" onsubmit="return confirm('Reset password for this teacher?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">Reset Password</button>
                    </form>
                </td>
                <td class="text-end">
                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Delete this teacher?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center empty-state py-4">No teachers yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
