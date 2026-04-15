<nav class="fixed top-0 inset-x-0 z-50 w-screen bg-[#F6FBF8] border-b border-gray-200">
    <div class="flex items-center justify-between px-8 h-16">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-green-400 flex items-center justify-center">
                <span class="text-white font-bold text-lg">CL</span>
            </a>
            <a href="{{ url('/') }}" class="text-xl font-semibold text-gray-900">CommunityLearningHub</a>
        </div>
        <div class="flex items-center gap-5">
            <a href="{{ url('/auth/signin') }}" class="text-[15px] font-medium text-gray-700 hover:text-gray-900">Sign In</a>
            <a href="{{ url('/auth/signup') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-emerald-400 to-green-500 text-white text-[15px] hover:opacity-80 font-semibold shadow-sm">Get Started</a>
        </div>
    </div>
</nav>
