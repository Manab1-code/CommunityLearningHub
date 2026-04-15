<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') – Community Learning Hub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-56 bg-slate-800 text-white flex-shrink-0 flex flex-col min-h-screen">
            <div class="p-4 border-b border-slate-700">
                <a href="{{ route('admin.dashboard') }}" class="font-bold text-lg">🛡️ Admin</a>
                <p class="text-xs text-slate-400 mt-1">Community Learning Hub</p>
            </div>
            <nav class="p-2 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Dashboard</a>
                <a href="{{ route('admin.users') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Users</a>
                <a href="{{ route('admin.session-requests') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.session-requests') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Session Requests</a>
                <a href="{{ route('admin.learning-materials') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.learning-materials') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Learning Materials</a>
                <a href="{{ route('admin.challenges') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.challenges*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Challenges</a>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <a href="{{ url('/home') }}" class="block text-sm text-slate-400 hover:text-white">← Back to site</a>
                <a href="{{ url('/auth/logout') }}" class="block mt-2 text-sm text-slate-400 hover:text-white">Sign out</a>
            </div>
        </aside>
        <main class="flex-1 overflow-auto">
            <header class="bg-white border-b border-slate-200 px-6 py-4">
                <h1 class="text-xl font-semibold text-slate-800">@yield('heading', 'Admin')</h1>
                <p class="text-sm text-slate-500 mt-0.5">@yield('subheading', '')</p>
            </header>
            <div class="p-6">
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
