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
    <form method="POST" action="{{ route('admin.teachers.store') }}" class="row g-3">
        @csrf
        <div class="col-md-3"><label class="form-label">Employee ID</label><input class="form-control" name="employee_id" required></div>
        <div class="col-md-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
        <div class="col-md-4"><label class="form-label">Password</label><input type="password" class="form-control" name="password" placeholder="Default: Teacher@123"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Save Teacher</button></div>
    </form>
</section>
<section class="panel">
    <table class="table align-middle">
        <thead><tr><th>Employee ID</th><th>Name</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($teachers as $teacher)
            <tr>
                <td>{{ $teacher->employee_id }}</td>
                <td><span class="record-name">{{ $teacher->last_name }}, {{ $teacher->first_name }}</span></td>
                <td><span class="badge text-bg-{{ $teacher->status === 'active' ? 'success' : 'secondary' }}">{{ $teacher->status }}</span></td>
                <td class="text-end">
                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Delete this teacher?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center empty-state py-4">No teachers yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
