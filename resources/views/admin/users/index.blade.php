@extends('layouts.app')

@section('title', 'Manage Users — Admin')

@section('content')
<div class="container-wide">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <h2 style="margin:0">Manage Users</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Files</th>
                <th>2FA</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td><strong>{{ $user->username }}</strong></td>
                <td>
                    <span style="
                        padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;
                        background:{{ $user->role === 'admin' ? '#fef3c7' : '#eff6ff' }};
                        color:{{ $user->role === 'admin' ? '#92400e' : '#1d4ed8' }};
                    ">{{ strtoupper($user->role) }}</span>
                </td>
                <td>{{ $user->files_count }}</td>
                <td>
                    @if($user->totp_enabled)
                        <span style="color:#16a34a;font-size:12px;font-weight:600">✓ Active</span>
                    @elseif($user->totp_secret)
                        <span style="color:#d97706;font-size:12px">Pending</span>
                    @else
                        <span style="color:#9ca3af;font-size:12px">—</span>
                    @endif
                </td>
                <td style="font-size:12px;color:#9ca3af">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center">
                        <a href="{{ route('admin.users.2fa', $user) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:12px">2FA</a>
                        <a href="{{ route('admin.users.password', $user) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:12px">Password</a>
                        @if($user->id !== session('auth_user_id'))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Delete user {{ $user->username }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding:5px 10px;font-size:12px">Delete</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
