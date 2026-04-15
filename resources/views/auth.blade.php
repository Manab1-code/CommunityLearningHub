@extends('layouts.app')

@section('body')
<div class="min-h-screen flex items-center justify-center bg-[#FAF7F2] px-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8">
        <div class="flex bg-[#F3F1EC] rounded-full p-1 mb-8">
            <a href="{{ url('/auth/signin') }}" class="flex-1 py-2 rounded-full text-sm font-semibold text-center transition {{ request()->is('auth/signin') ? 'bg-emerald-500 text-white' : 'text-slate-600' }}">Sign In</a>
            <a href="{{ url('/auth/signup') }}" class="flex-1 py-2 rounded-full text-sm font-semibold text-center transition {{ request()->is('auth/signup') ? 'bg-emerald-500 text-white' : 'text-slate-600' }}">Sign Up</a>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900">
                @if(request()->is('auth/signin')) Welcome Back! @else Create Account @endif
            </h1>
            <p class="text-slate-500 text-sm mt-2">
                @if(request()->is('auth/signin')) Sign in to continue your learning journey @else Join us and start learning today @endif
            </p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                <p class="text-red-600 text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @if(request()->is('auth/signup'))
            <form method="POST" action="{{ url('/auth/register') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700">Full Name</label>
                    <input name="name" value="{{ old('name') }}" placeholder="Enter your full name" class="w-full mt-1 px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Email Address</label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" class="w-full mt-1 px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <div class="relative mt-1">
                        <input name="password" type="password" placeholder="Enter your password" class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none password-toggle-input" required>
                        <button type="button" class="absolute inset-y-0 right-3 text-slate-500 hover:text-slate-700 focus:outline-none password-toggle-btn" aria-label="Show password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3C5.455 3 1.73 6.11.458 10c1.272 3.89 4.997 7 9.542 7s8.27-3.11 9.542-7C18.27 6.11 14.545 3 10 3Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-closed-icon hidden" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3.28 2.22a.75.75 0 1 0-1.06 1.06l2.051 2.051A10.255 10.255 0 0 0 .458 10c1.272 3.89 4.997 7 9.542 7 1.973 0 3.813-.59 5.352-1.601l1.368 1.37a.75.75 0 1 0 1.06-1.061L3.28 2.22ZM10 14a4 4 0 0 1-3.997-3.857l5.854 5.854A3.98 3.98 0 0 1 10 14Zm0-8a4 4 0 0 1 3.997 3.857l1.636 1.636A9.066 9.066 0 0 0 19.1 10C17.92 6.943 14.255 4 10 4c-1.239 0-2.437.25-3.54.712l1.69 1.69A3.98 3.98 0 0 1 10 6Z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                    <div class="relative mt-1">
                        <input name="password_confirmation" type="password" placeholder="Confirm your password" class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none password-toggle-input" required>
                        <button type="button" class="absolute inset-y-0 right-3 text-slate-500 hover:text-slate-700 focus:outline-none password-toggle-btn" aria-label="Show password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3C5.455 3 1.73 6.11.458 10c1.272 3.89 4.997 7 9.542 7s8.27-3.11 9.542-7C18.27 6.11 14.545 3 10 3Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-closed-icon hidden" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3.28 2.22a.75.75 0 1 0-1.06 1.06l2.051 2.051A10.255 10.255 0 0 0 .458 10c1.272 3.89 4.997 7 9.542 7 1.973 0 3.813-.59 5.352-1.601l1.368 1.37a.75.75 0 1 0 1.06-1.061L3.28 2.22ZM10 14a4 4 0 0 1-3.997-3.857l5.854 5.854A3.98 3.98 0 0 1 10 14Zm0-8a4 4 0 0 1 3.997 3.857l1.636 1.636A9.066 9.066 0 0 0 19.1 10C17.92 6.943 14.255 4 10 4c-1.239 0-2.437.25-3.54.712l1.69 1.69A3.98 3.98 0 0 1 10 6Z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full py-3 rounded-full font-semibold text-white bg-gradient-to-r from-emerald-500 to-orange-400 shadow-lg hover:opacity-90">Create Account →</button>
            </form>
        @else
            <form method="POST" action="{{ url('/auth/login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700">Email Address</label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" class="w-full mt-1 px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <div class="relative mt-1">
                        <input name="password" type="password" placeholder="Enter your password" class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none password-toggle-input" required>
                        <button type="button" class="absolute inset-y-0 right-3 text-slate-500 hover:text-slate-700 focus:outline-none password-toggle-btn" aria-label="Show password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3C5.455 3 1.73 6.11.458 10c1.272 3.89 4.997 7 9.542 7s8.27-3.11 9.542-7C18.27 6.11 14.545 3 10 3Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-closed-icon hidden" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3.28 2.22a.75.75 0 1 0-1.06 1.06l2.051 2.051A10.255 10.255 0 0 0 .458 10c1.272 3.89 4.997 7 9.542 7 1.973 0 3.813-.59 5.352-1.601l1.368 1.37a.75.75 0 1 0 1.06-1.061L3.28 2.22ZM10 14a4 4 0 0 1-3.997-3.857l5.854 5.854A3.98 3.98 0 0 1 10 14Zm0-8a4 4 0 0 1 3.997 3.857l1.636 1.636A9.066 9.066 0 0 0 19.1 10C17.92 6.943 14.255 4 10 4c-1.239 0-2.437.25-3.54.712l1.69 1.69A3.98 3.98 0 0 1 10 6Z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="text-right">
                    <a href="{{ url('/forgot-password') }}" class="text-sm text-emerald-600 hover:underline">Forgot Password?</a>
                </div>
                <button type="submit" class="w-full py-3 rounded-full font-semibold text-white bg-gradient-to-r from-emerald-500 to-orange-400 shadow-lg hover:opacity-90">Sign In →</button>
            </form>
        @endif
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.password-toggle-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var wrapper = this.closest('.relative');
                var input = wrapper ? wrapper.querySelector('.password-toggle-input') : null;
                if (!input) return;

                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';

                var openIcon = this.querySelector('.eye-open-icon');
                var closedIcon = this.querySelector('.eye-closed-icon');

                if (openIcon && closedIcon) {
                    openIcon.classList.toggle('hidden', !isHidden);
                    closedIcon.classList.toggle('hidden', isHidden);
                }

                this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });
    });
</script>
@endsection
