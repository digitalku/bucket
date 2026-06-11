@extends('layouts.app')

@section('title', 'Add User — Admin')

@section('content')
<div class="container" style="max-width:480px">
    <div style="margin-bottom:16px">
        <a href="{{ route('admin.users.index') }}" style="color:#6b7280;font-size:14px">← Back</a>
    </div>

    <h2>Add New User</h2>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}"
                   placeholder="letters, numbers, - and _" autofocus>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px">
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Create User</button>
    </form>
</div>
@endsection
