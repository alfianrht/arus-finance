<div
    id="imagePreviewModal"
    class="pointer-events-none fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 p-4 opacity-0 transition duration-200"
    aria-hidden="true"
>
    <button
        type="button"
        data-image-preview-close
        class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/95 text-zinc-950 shadow-sm"
        aria-label="Tutup preview"
    >
        <span class="material-symbols-rounded text-[20px]" aria-hidden="true">close</span>
    </button>

    <img
        id="imagePreviewModalImage"
        src=""
        alt="Preview gambar"
        class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl"
    >
</div>
