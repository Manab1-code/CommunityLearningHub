@extends('layouts.app-with-nav')

@section('content')
<div class="flex flex-col w-full max-w-6xl mx-auto border border-slate-200 rounded-lg overflow-hidden mt-4 bg-white" style="min-height: calc(100vh - 8rem);">
    <div class="flex flex-1 min-h-0 overflow-hidden">
        <aside class="w-72 border-r border-slate-200 p-4 flex-shrink-0 overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4">Conversations</h2>

            <p class="text-xs text-slate-500 mb-2">Session chats (tutor & learner can message each other)</p>
            <div class="space-y-1 mb-4">
                @foreach($acceptedSessionsForChat ?? [] as $item)
                    @php
                        $other = $item['otherUser'];
                        $sr = $item['session_request'];
                        $isLearner = $sr->learner_id === auth()->id();
                        $hasUnread = $item['hasUnread'] ?? false;
                    @endphp
                    <a href="{{ route('messages.with', $other->id) }}" class="flex items-center p-2 hover:bg-slate-100 rounded-lg border border-slate-100 transition">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-medium text-sm">{{ strtoupper(substr($other->name, 0, 1)) }}</div>
                        <div class="ml-2 flex-1 min-w-0">
                            <span class="{{ $hasUnread ? 'font-bold' : 'font-medium' }} text-slate-900 block truncate">{{ $other->name }}</span>
                            <span class="text-xs text-slate-500">{{ $isLearner ? 'Tutor' : 'Learner' }} · {{ $sr->skill_name }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <p class="text-xs text-slate-500 mb-2">Direct messages</p>
            <div class="space-y-1">
                @forelse($dmConversations ?? [] as $dm)
                    @php $hasUnread = $dm['hasUnread'] ?? false; @endphp
                    <a href="{{ route('messages', ['conv' => $dm['id']]) }}" class="flex items-center p-2 rounded-lg border transition {{ (isset($selectedConv) && $selectedConv->id == $dm['id']) ? 'bg-emerald-50 border-emerald-200' : 'border-slate-100 hover:bg-slate-50' }}">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-medium text-sm">{{ $dm['otherUser'] ? strtoupper(substr($dm['otherUser']['name'], 0, 1)) : '?' }}</div>
                        <div class="ml-2 flex-1 min-w-0">
                            <span class="{{ $hasUnread ? 'font-bold' : 'font-medium' }} text-slate-900 block truncate">{{ $dm['otherUser']['name'] ?? 'Unknown' }}</span>
                            @if(!empty($dm['lastMessage']))
                                <span class="text-xs {{ $hasUnread ? 'text-slate-700 font-medium' : 'text-slate-500' }} truncate block">{{ $dm['lastMessage']['body'] }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500 py-2">No conversations yet. After a session is accepted, both tutor and learner can message each other here—or start a chat from someone's profile.</p>
                @endforelse
            </div>
        </aside>

        <main class="flex flex-col flex-1 min-h-0 min-w-0 bg-slate-50">
            @if(isset($selectedConv) && $selectedConv)
                @php
                    $other = $selectedConv->participants->first()?->user;
                    $isDmChat = $selectedConv->type === 'dm';
                @endphp
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-white flex-shrink-0">
                    <div class="flex items-center min-w-0">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-medium flex-shrink-0">{{ $other ? strtoupper(substr($other->name, 0, 1)) : '?' }}</div>
                        <h2 class="text-lg font-semibold ml-3 text-slate-900 truncate">{{ $other ? $other->name : 'Chat' }}</h2>
                    </div>
                    @if($isDmChat && $other)
                        <button type="button" id="dmVideoCallBtn" class="flex-shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white shadow-md transition" title="Video call" aria-label="Start video call">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    @endif
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto p-4 space-y-3" id="messages-area">
                    @forelse($selectedMessages ?? [] as $m)
                        <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : '' }}">
                            <div class="max-w-[75%] {{ $m->sender_id === auth()->id() ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200' }} rounded-lg px-3 py-2 shadow-sm">
                                <p class="text-xs {{ $m->sender_id === auth()->id() ? 'text-emerald-100' : 'text-slate-500' }}">{{ $m->sender->name }}</p>
                                <p class="text-sm">{{ $m->body }}</p>
                                <p class="text-xs mt-1 {{ $m->sender_id === auth()->id() ? 'text-emerald-200' : 'text-slate-400' }}">{{ $m->created_at->timezone($userTimezone ?? config('app.timezone'))->format('M j, H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm text-center py-8">No messages yet. Say hello!</p>
                    @endforelse
                </div>

                <div class="flex-shrink-0 bg-white border-t border-slate-200 p-4">
                    <form action="{{ route('messages.send') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="conv_id" value="{{ $selectedConv->id }}">
                        <input type="text" name="body" required maxlength="2000" placeholder="Type your message..." class="flex-1 min-w-0 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium whitespace-nowrap">Send</button>
                    </form>
                    @if(!empty($userTimezone) && $userTimezone !== 'UTC')
                        <p class="text-xs text-slate-400 mt-2">Times shown in your timezone ({{ $userTimezone }})</p>
                    @endif
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-slate-500 p-8">
                    <div class="text-center">
                        <p class="text-4xl mb-2">💬</p>
                        <p>Select a conversation or <a href="{{ url('/session-requests') }}" class="text-emerald-600 hover:underline">view session requests</a> to chat with a tutor after they accept.</p>
                    </div>
                </div>
            @endif
        </main>
    </div>
</div>

@if(isset($selectedConv) && $selectedConv->type === 'dm' && $selectedConv->participants->first()?->user)
<div id="dmVideoOverlay" class="fixed inset-0 z-[200] bg-black hidden flex flex-col" role="dialog" aria-modal="true" aria-labelledby="dm-call-status">
    <div class="relative flex-1 flex items-center justify-center min-h-0 min-h-[40vh]">
        <video id="dmRemoteVideo" class="absolute inset-0 z-0 max-h-full max-w-full w-full h-full object-contain bg-black pointer-events-none" autoplay playsinline></video>
        <p id="dmRemotePlaceholder" class="absolute inset-0 z-[1] flex items-center justify-center text-white/50 text-sm px-6 text-center pointer-events-none">Waiting for the other person…</p>
        <div class="absolute bottom-28 right-4 z-30 w-40 sm:w-48 h-28 sm:h-32 rounded-2xl overflow-hidden border-2 border-white/90 shadow-2xl ring-1 ring-black/20 bg-slate-900">
            <video id="dmLocalVideo" class="absolute inset-0 w-full h-full object-cover bg-slate-800" muted playsinline></video>
        </div>
    </div>
    <div class="flex-shrink-0 flex flex-col items-center gap-2 pb-6 pt-2 bg-gradient-to-t from-black via-black/90 to-transparent">
        <p id="dm-call-status" class="text-xs text-white/70 px-4 text-center">Connecting…</p>
        <div class="flex items-center justify-center gap-6 sm:gap-10 pb-8 pt-2">
            <button type="button" id="dmMuteBtn" class="flex flex-col items-center gap-1 text-white/90">
                <span class="w-14 h-14 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center border border-white/20">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                </span>
                <span id="dmMuteLabel" class="text-[11px]">Mute</span>
            </button>
            <button type="button" id="dmCameraBtn" class="flex flex-col items-center gap-1 text-white/90">
                <span class="w-14 h-14 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center border border-white/20">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <span id="dmCameraLabel" class="text-[11px]">Camera</span>
            </button>
            <button type="button" id="dmEndCallBtn" class="flex flex-col items-center gap-1 text-white">
                <span class="w-14 h-14 rounded-full bg-rose-600 hover:bg-rose-700 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </span>
                <span class="text-[11px]">End</span>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const CONV_ID = {{ (int) $selectedConv->id }};
    const REMOTE_USER_ID = {{ (int) $selectedConv->participants->first()->user->id }};
    const csrfToken = '{{ csrf_token() }}';
    const callPrefix = @json(url('/conversations/'.$selectedConv->id.'/call'));

    const overlay = document.getElementById('dmVideoOverlay');
    const localVideo = document.getElementById('dmLocalVideo');
    const remoteVideo = document.getElementById('dmRemoteVideo');
    const remotePlaceholder = document.getElementById('dmRemotePlaceholder');
    const callStatusEl = document.getElementById('dm-call-status');
    const startBtn = document.getElementById('dmVideoCallBtn');
    const muteBtn = document.getElementById('dmMuteBtn');
    const cameraBtn = document.getElementById('dmCameraBtn');
    const endBtn = document.getElementById('dmEndCallBtn');
    const muteLabel = document.getElementById('dmMuteLabel');
    const cameraLabel = document.getElementById('dmCameraLabel');

    let localStream = null;
    let remoteStream = null;
    let peerConnection = null;
    let pollingHandle = null;
    let lastSignalId = 0;
    let isMuted = false;
    let isCameraOff = false;

    const rtcServers = {
        iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
    };

    function setStatus(text) {
        if (callStatusEl) callStatusEl.textContent = text;
    }

    function showOverlay() {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (remotePlaceholder) remotePlaceholder.classList.remove('hidden');
    }

    async function playVideoEl(videoEl) {
        if (!videoEl) return;
        try {
            await videoEl.play();
        } catch (e) {
            await new Promise(function (r) { requestAnimationFrame(r); });
            try {
                await videoEl.play();
            } catch (e2) {}
        }
    }

    function updateRemotePlaceholder() {
        if (!remotePlaceholder || !remoteVideo) return;
        var src = remoteVideo.srcObject;
        var hasVideo = !!(src && src.getVideoTracks && src.getVideoTracks().some(function (t) {
            return t.readyState === 'live' && t.enabled;
        }));
        if (hasVideo) {
            remotePlaceholder.classList.add('hidden');
        } else {
            remotePlaceholder.classList.remove('hidden');
        }
    }

    function hideOverlay() {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    async function callApi(path, method, body) {
        const options = {
            method,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (method !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        if (body) options.body = JSON.stringify(body);
        const response = await fetch(path, options);
        if (!response.ok) throw new Error('Request failed');
        return response.json();
    }

    async function ensureLocalMedia() {
        if (localStream) {
            await playVideoEl(localVideo);
            return localStream;
        }
        var constraints = {
            audio: true,
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        try {
            localStream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (e) {
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
        }
        localVideo.muted = true;
        localVideo.setAttribute('muted', '');
        localVideo.setAttribute('playsinline', '');
        localVideo.setAttribute('webkit-playsinline', '');
        localVideo.playsInline = true;
        localVideo.srcObject = localStream;
        localVideo.onloadedmetadata = function () {
            playVideoEl(localVideo);
        };
        await playVideoEl(localVideo);
        requestAnimationFrame(function () {
            playVideoEl(localVideo);
        });
        return localStream;
    }

    function ensurePeerConnection() {
        if (peerConnection) return peerConnection;
        peerConnection = new RTCPeerConnection(rtcServers);

        if (localStream) {
            localStream.getTracks().forEach(function (track) {
                peerConnection.addTrack(track, localStream);
            });
        }

        peerConnection.ontrack = function (event) {
            var stream = event.streams && event.streams[0];
            if (stream) {
                remoteStream = stream;
                remoteVideo.srcObject = stream;
                remoteVideo.playsInline = true;
                remoteVideo.setAttribute('playsinline', '');
                playVideoEl(remoteVideo).then(function () {
                    updateRemotePlaceholder();
                });
                updateRemotePlaceholder();
            }
        };

        peerConnection.onicecandidate = async (event) => {
            if (!event.candidate) return;
            await sendSignal('ice', event.candidate, REMOTE_USER_ID);
        };

        peerConnection.onconnectionstatechange = () => {
            if (peerConnection.connectionState === 'connected') setStatus('Connected');
            else if (peerConnection.connectionState === 'disconnected' || peerConnection.connectionState === 'failed') {
                setStatus('Connection lost');
            }
        };

        return peerConnection;
    }

    async function sendSignal(type, payload, targetUserId) {
        const data = { type, payload };
        if (targetUserId) data.target_user_id = targetUserId;
        await callApi(callPrefix + '/signal', 'POST', data);
    }

    async function processSignal(signal) {
        if (!signal || !signal.type) return;

        if (signal.type === 'ready') {
            setStatus('Contact is online');
            return;
        }
        if (signal.type === 'offer') {
            showOverlay();
            await ensureLocalMedia();
            setTimeout(function () { playVideoEl(localVideo); }, 50);
            setTimeout(function () { playVideoEl(localVideo); }, 300);
            const pc = ensurePeerConnection();
            await pc.setRemoteDescription(new RTCSessionDescription(signal.payload));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await sendSignal('answer', answer, signal.sender_id);
            setStatus('Connected');
            return;
        }

        const pc = ensurePeerConnection();
        if (signal.type === 'answer') {
            await pc.setRemoteDescription(new RTCSessionDescription(signal.payload));
            setStatus('Connected');
            return;
        }
        if (signal.type === 'ice') {
            try {
                await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
            } catch (e) {}
            return;
        }
        if (signal.type === 'hangup') {
            cleanupCall(false);
            setStatus('Call ended');
            hideOverlay();
        }
    }

    async function pollSignals() {
        try {
            const data = await callApi(callPrefix + '/signal/poll?after_id=' + lastSignalId, 'GET');
            const signals = Array.isArray(data.signals) ? data.signals : [];
            for (const signal of signals) {
                lastSignalId = Math.max(lastSignalId, Number(signal.id) || 0);
                await processSignal(signal);
            }
        } catch (e) {}
    }

    async function startCallFlow() {
        showOverlay();
        setStatus('Starting…');
        await callApi(callPrefix + '/start', 'POST');
        await ensureLocalMedia();
        setTimeout(function () { playVideoEl(localVideo); }, 50);
        setTimeout(function () { playVideoEl(localVideo); }, 300);
        const pc = ensurePeerConnection();
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await sendSignal('offer', offer, REMOTE_USER_ID);
        setStatus('Calling…');
    }

    function cleanupCall(notifyBackend) {
        if (peerConnection) {
            peerConnection.ontrack = null;
            peerConnection.onicecandidate = null;
            peerConnection.close();
            peerConnection = null;
        }
        if (remoteStream) {
            remoteStream.getTracks().forEach(function (t) { t.stop(); });
            remoteStream = null;
        }
        if (remoteVideo) {
            remoteVideo.srcObject = null;
        }
        updateRemotePlaceholder();
        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
            localStream = null;
        }
        if (localVideo) localVideo.srcObject = null;
        isMuted = false;
        isCameraOff = false;
        if (muteLabel) muteLabel.textContent = 'Mute';
        if (cameraLabel) cameraLabel.textContent = 'Camera';
        if (notifyBackend) {
            callApi(callPrefix + '/end', 'POST').catch(function () {});
        }
    }

    startBtn?.addEventListener('click', async function () {
        try {
            await startCallFlow();
        } catch (e) {
            setStatus('Could not start camera or call');
            hideOverlay();
        }
    });

    muteBtn?.addEventListener('click', async function () {
        try {
            await ensureLocalMedia();
            isMuted = !isMuted;
            localStream.getAudioTracks().forEach(function (t) { t.enabled = !isMuted; });
            if (muteLabel) muteLabel.textContent = isMuted ? 'Unmute' : 'Mute';
        } catch (e) {}
    });

    cameraBtn?.addEventListener('click', async function () {
        try {
            await ensureLocalMedia();
            isCameraOff = !isCameraOff;
            localStream.getVideoTracks().forEach(function (t) { t.enabled = !isCameraOff; });
            if (cameraLabel) cameraLabel.textContent = isCameraOff ? 'Camera on' : 'Camera';
        } catch (e) {}
    });

    endBtn?.addEventListener('click', function () {
        cleanupCall(true);
        setStatus('Call ended');
        hideOverlay();
    });

    window.addEventListener('beforeunload', function () {
        cleanupCall(true);
    });

    pollingHandle = window.setInterval(pollSignals, 1500);
    pollSignals();
    sendSignal('ready', { conversation_id: CONV_ID }, REMOTE_USER_ID).catch(function () {});
})();
</script>
@endif
@endsection
