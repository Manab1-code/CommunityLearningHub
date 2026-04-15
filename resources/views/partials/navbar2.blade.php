<nav class="w-screen fixed top-0 left-0 bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-xl px-6 py-5 flex items-center justify-between border-b-4 border-emerald-700 z-50">
    <a href="{{ url('/home') }}" class="text-2xl font-bold text-white hover:opacity-90 transition">🎓 CommunityLearning Hub</a>
    <div class="flex gap-8 font-semibold text-white">
        <a href="{{ url('/explore') }}" class="{{ request()->is('explore') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">Explore</a>
        <a href="{{ url('/learn') }}" class="{{ request()->is('learn') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">My Learning</a>
        <a href="{{ url('/teaching') }}" class="{{ request()->is('teaching') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">Teach</a>
        <a href="{{ url('/communitygroups') }}" class="{{ request()->is('communitygroups') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">Community</a>
        <a href="{{ url('/learning-materials') }}" class="{{ request()->is('learning-materials*') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">Resources</a>
        <a href="{{ route('skillwallet') }}" class="{{ request()->is('skillwallet') ? 'border-b-2 border-white pb-1' : 'hover:text-emerald-100 transition' }}">Skill Wallet</a>
    </div>
    @php
        $navIconClass = 'relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white ring-1 ring-white/25 bg-white/10 shadow-sm backdrop-blur-[2px] transition hover:bg-white/[0.2] hover:ring-white/40 active:scale-[0.96] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-600';
        $navBadgeClass = 'absolute -right-0.5 -top-0.5 flex h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-bold tabular-nums leading-none text-emerald-950 shadow ring-2 ring-emerald-600';
    @endphp
    <div class="flex items-center gap-2 sm:gap-3">
        <a href="{{ url('/session-requests') }}" class="{{ $navIconClass }}" title="Session requests" aria-label="Session requests">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            @if(($pendingRequestsCount ?? 0) > 0)
                <span class="{{ $navBadgeClass }}">{{ $pendingRequestsCount }}</span>
            @endif
        </a>
        <a href="{{ url('/messages') }}" class="{{ $navIconClass }}" title="Messages" aria-label="Messages">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.043 4.043 0 01-.554 1.704 21.243 21.243 0 01-1.89 2.3c-.695.616-1.028 1.586-.962 2.55.055.98.49 1.892 1.223 2.538a.75.75 0 00.752.184 21.122 21.122 0 001.928-.946 12.001 12.001 0 007.596-3.092c1.22-1.104 2.2-2.43 2.882-3.894" />
            </svg>
            @if(($unreadMessagesCount ?? 0) > 0)
                <span class="{{ $navBadgeClass }}">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
            @endif
        </a>
        <a href="{{ url('/notifications') }}" class="{{ $navIconClass }}" title="Notifications" aria-label="Notifications">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if(($unreadNotificationsCount ?? 0) > 0)
                <span class="{{ $navBadgeClass }}">{{ $unreadNotificationsCount }}</span>
            @endif
        </a>
        <a href="{{ url('/profile') }}" class="block">
            @if(!empty($navProfilePhotoUrl))
                <img src="{{ $navProfilePhotoUrl }}" alt="Profile" class="w-10 h-10 rounded-full hover:opacity-90 border-2 border-white transition object-cover">
            @else
                <div class="w-10 h-10 rounded-full border-2 border-white bg-emerald-100 text-emerald-700 font-semibold flex items-center justify-center">
                    {{ $navProfileInitial ?? 'U' }}
                </div>
            @endif
        </a>
    </div>
</nav>
