@extends('layouts.app')

@section('body')
<div class="min-h-screen flex flex-col">
    @include('partials.navbar')

    <section class="relative min-h-[600px] flex flex-col items-center justify-center text-center px-6 py-24 mt-16">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-100/80 via-white/70 to-amber-100/70"></div>
        <div class="relative z-10 max-w-4xl">
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-100 text-emerald-600 text-sm font-medium mb-8">✨ Join 10,000+ learners worldwide</div>
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-gray-900">
                Learn Together, <span class="bg-gradient-to-r from-emerald-400 to-yellow-400 bg-clip-text text-transparent">Grow Together</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-gray-600 leading-relaxed">
                Exchange skills with your community. Whether you're teaching or learning, our platform connects passionate people to share knowledge and grow together.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-5">
                <a href="{{ url('/auth/signup') }}" class="px-8 py-3 rounded-full bg-gradient-to-r from-emerald-400 to-yellow-400 text-white font-semibold shadow-md hover:opacity-90 transition">Start Learning Free →</a>
                <a href="#" class="px-8 py-3 rounded-full border-2 border-emerald-400 text-emerald-600 font-semibold hover:bg-emerald-50 transition">Watch Demo</a>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-[#078987] mb-2">10K+</div>
                    <div class="text-sm text-slate-500">Active Learners</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-[#078987] mb-2">500+</div>
                    <div class="text-sm text-slate-500">Skills Shared</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-[#078987] mb-2">25K+</div>
                    <div class="text-sm text-slate-500">Sessions Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-[#078987] mb-2">98%</div>
                    <div class="text-sm text-slate-500">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 md:py-32 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">
                    Everything You Need to <span class="bg-gradient-to-r from-[#2FB7A3] to-[#6CBF8E] bg-clip-text text-transparent">Learn & Teach</span>
                </h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Our platform provides all the tools you need for successful skill exchange</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition">
                    <div class="w-14 h-14 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-5 text-2xl">👥</div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Skill Exchange</h3>
                    <p class="text-slate-500 leading-relaxed">Trade your expertise with others. Teach what you know, learn what you need.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition">
                    <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center mb-5 text-2xl">💬</div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Community Chat</h3>
                    <p class="text-slate-500 leading-relaxed">Connect in real-time with learners and mentors in topic-based chat rooms.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition">
                    <div class="w-14 h-14 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-5 text-2xl">🏆</div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Achievement Badges</h3>
                    <p class="text-slate-500 leading-relaxed">Earn recognition for your progress and contributions to the community.</p>
                </div>
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition">
                    <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center mb-5 text-2xl">👛</div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">Skill Wallet</h3>
                    <p class="text-slate-500 leading-relaxed">Earn points when you teach, then redeem them to add new learning skills and book sessions.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="pt-20 pb-10 md:pt-32 md:pb-16 px-6">
        <div class="container mx-auto">
            <div class="relative overflow-hidden rounded-3xl p-10 md:p-16 bg-gradient-to-r from-teal-500 via-emerald-500 to-amber-400">
                <div class="relative z-10 max-w-2xl">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Ready to Start Your Learning Journey?</h2>
                    <p class="text-lg text-white/90 mb-8">Join thousands of learners exchanging skills every day. It's free to get started – no credit card required.</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ url('/auth/signup') }}" class="inline-flex items-center justify-center gap-2 h-14 px-8 text-base font-semibold rounded-full bg-[#F9F4E7] text-slate-900 hover:shadow-lg transition">Create Free Account →</a>
                        <a href="{{ url('/auth/signin') }}" class="inline-flex items-center justify-center h-14 px-8 text-base font-semibold rounded-full bg-white/10 text-white hover:bg-white/20 transition">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')
</div>
@endsection
