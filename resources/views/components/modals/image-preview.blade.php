{{-- Fullscreen Image Preview --}}
<div id="fullImageModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] flex items-center justify-center p-6" onclick="closeModal('fullImageModal')">
    <button class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors text-2xl">
        <i class="fas fa-times"></i>
    </button>
    <img id="fullImageSrc" class="max-w-full max-h-full rounded-2xl shadow-2xl border border-white/10" onclick="event.stopPropagation()">
</div>
