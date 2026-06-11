<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Digitalku Bucket')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 15px;
            color: #1f2937;
        }

        .container {
            max-width: 760px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 28px 24px;
        }

        .container-wide {
            max-width: 1100px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 28px 24px;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #1e293b;
            border-radius: 10px;
            margin-bottom: 20px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        nav .brand {
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
        }

        nav .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        nav a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
        }

        nav a:hover { color: #fff; }

        nav .badge {
            background: #3b82f6;
            color: #fff;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        nav .badge.admin { background: #f59e0b; }

        h1, h2, h3 { margin-top: 0; }

        .btn {
            display: inline-block;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger  { background: #dc2626; color: #fff; }
        .btn-danger:hover  { background: #b91c1c; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
        }

        .form-group input:focus {
            border-color: #2563eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fafb; }
    </style>
    @stack('styles')
</head>
<body>

@if(session('auth_user_id'))
<nav>
    <a href="{{ route('upload') }}" class="brand">Digitalku Bucket</a>
    <div class="nav-links">
        <a href="{{ route('upload') }}">Upload</a>
        <a href="{{ route('gallery') }}">Gallery</a>
        <a href="{{ route('profile.password') }}">Password</a>
        @if(session('auth_role') === 'admin')
            <a href="{{ route('admin.index') }}">Admin</a>
            <span class="badge admin">Admin</span>
        @else
            <span class="badge">{{ session('auth_username') }}</span>
        @endif
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;padding:0;">Logout</button>
        </form>
    </div>
</nav>
@endif

@yield('content')

<footer style="text-align:center;padding:28px 16px 18px;font-size:12px;color:#9ca3af">
    <a href="https://www.digitalku.com" target="_blank" rel="noopener" style="color:#9ca3af;text-decoration:none">Copyright Digitalku</a>
</footer>

@stack('scripts')
</body>
</html>
