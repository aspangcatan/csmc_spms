@extends('layouts.app')

@section('title', 'Training')

@section('content')
@php
    // Chapters come from public/media/training/chapters.json, generated with the video.
    $manifestPath = public_path('media/training/chapters.json');
    $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
    $hasVideo = $manifest && file_exists(public_path('media/training/' . $manifest['video']));
    $clock = fn ($s) => sprintf($s >= 3600 ? '%d:%02d:%02d' : '%2$d:%3$02d', intdiv((int) $s, 3600), intdiv((int) $s % 3600, 60), (int) $s % 60);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">e-SPMS Training</h1>
            <p class="text-xs text-gray-400 font-medium">Video guide to preparing, reviewing, approving and printing IPCRs and SPCRs. Pick any chapter to jump straight to it.</p>
        </div>
        @if($hasVideo)
            <button id="resumeBtn" type="button" class="btn btn-orange items-center gap-2 shadow-sm hidden">
                <i class="fas fa-play"></i> <span>Continue where you left off</span>
            </button>
        @endif
    </div>

    @if(!$hasVideo)
        <div class="card-modern p-12 text-center">
            <i class="fas fa-film text-4xl text-gray-200 mb-4 block"></i>
            <p class="text-sm font-bold text-gray-900">The training video is not available yet.</p>
            <p class="text-xs text-gray-400 mt-1">Please contact the system administrator.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="card-modern overflow-hidden p-0 bg-black">
                    <video id="trainingVideo" class="w-full aspect-video bg-black" controls preload="metadata" playsinline>
                        <source src="{{ asset('media/training/' . $manifest['video']) }}" type="video/mp4">
                        <track kind="chapters" src="{{ asset('media/training/chapters.vtt') }}" srclang="en" label="Chapters" default>
                        Your browser does not support embedded video.
                    </video>
                </div>
                <div class="mt-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Now playing</p>
                        <p id="nowPlaying" class="text-sm font-bold text-gray-900">{{ $manifest['parts'][0]['title'] }}</p>
                    </div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                        Total {{ $clock($manifest['duration']) }} · Subtitles included
                    </p>
                </div>
            </div>

            <div class="card-modern p-0 overflow-hidden lg:max-h-[calc(100vh-12rem)] flex flex-col">
                <div class="p-4 border-b border-gray-50 bg-gray-50/30">
                    <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Chapters</h3>
                </div>
                <div id="chapterList" class="overflow-y-auto p-3 space-y-4">
                    @foreach($manifest['parts'] as $part)
                        <div>
                            <p class="px-3 mb-1 text-[10px] font-black text-orange-500 uppercase tracking-widest">{{ $part['title'] }}</p>
                            @foreach($part['chapters'] as $chapter)
                                <button type="button" class="chapter-item w-full text-left flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-orange-50 transition-colors"
                                        data-start="{{ $chapter['start'] }}" data-label="{{ $part['title'] }} · {{ $chapter['title'] }}">
                                    <i class="fas fa-play-circle text-gray-300 chapter-icon"></i>
                                    <span class="flex-1 text-xs font-bold text-gray-700">{{ $chapter['title'] }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 tabular-nums">{{ $clock($chapter['start']) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($hasVideo)
@push('scripts')
<script>
    (function () {
        const video = document.getElementById('trainingVideo');
        const items = Array.from(document.querySelectorAll('.chapter-item'));
        const resumeBtn = document.getElementById('resumeBtn');
        const storageKey = 'espms-training-position-{{ auth()->id() }}';
        let currentIndex = -1;

        const clock = (s) => {
            s = Math.floor(s);
            const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
            return (h ? h + ':' + String(m).padStart(2, '0') : m) + ':' + String(sec).padStart(2, '0');
        };
        // Saved position is a per-viewer convenience; storage may be unavailable.
        const readSaved = () => { try { return parseFloat(localStorage.getItem(storageKey)) || 0; } catch (e) { return 0; } };
        const save = () => { try { localStorage.setItem(storageKey, String(video.currentTime)); } catch (e) {} };

        function seek(seconds, play = true) {
            video.currentTime = seconds;
            if (play) video.play().catch(() => {});
        }

        function highlight() {
            const t = video.currentTime;
            let index = 0;
            items.forEach((item, i) => { if (t >= parseFloat(item.dataset.start)) index = i; });
            if (index === currentIndex) return;
            currentIndex = index;
            items.forEach((item, i) => {
                const active = i === index;
                item.classList.toggle('bg-orange-50', active);
                item.querySelector('.chapter-icon').classList.toggle('text-orange-500', active);
                item.querySelector('.chapter-icon').classList.toggle('text-gray-300', !active);
            });
            document.getElementById('nowPlaying').textContent = items[index].dataset.label;
            items[index].scrollIntoView({ block: 'nearest' });
        }

        items.forEach((item) => item.addEventListener('click', () => seek(parseFloat(item.dataset.start))));
        video.addEventListener('timeupdate', highlight);
        video.addEventListener('pause', save);
        video.addEventListener('ended', () => { try { localStorage.removeItem(storageKey); } catch (e) {} });
        setInterval(() => { if (!video.paused) save(); }, 5000);

        // ?t=<seconds> opens the video at a given point, so other pages can link to a chapter.
        const linked = parseFloat(new URLSearchParams(window.location.search).get('t'));
        const saved = readSaved();
        video.addEventListener('loadedmetadata', () => {
            if (linked > 0) {
                seek(linked, false);
            } else if (saved > 10 && saved < video.duration - 10) {
                resumeBtn.querySelector('span').textContent = 'Continue where you left off (' + clock(saved) + ')';
                resumeBtn.classList.remove('hidden');
                resumeBtn.classList.add('flex');
                resumeBtn.addEventListener('click', () => {
                    seek(saved);
                    resumeBtn.classList.add('hidden');
                });
            }
            highlight();
        });
    })();
</script>
@endpush
@endif
