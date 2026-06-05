<div id="format-download-modal" class="fixed inset-0 z-[1200] hidden items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm">
    <div class="modal-fade-in w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-xl font-black text-gray-950">Choose Format</h2>
                <p class="text-sm text-gray-500 mt-1">Select the format you want to download.</p>
            </div>
            <button type="button" data-close-format-modal class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition hover:border-gray-300 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            <button type="button" data-format="pdf" class="format-option-btn flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white p-4 text-left transition hover:border-blue-600 hover:bg-blue-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <div>
                        <div class="font-bold text-gray-950">PDF Document</div>
                        <div class="text-xs text-gray-500">Best for printing & sharing</div>
                    </div>
                </div>
            </button>
            <button type="button" data-format="docx" class="format-option-btn flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white p-4 text-left transition hover:border-blue-600 hover:bg-blue-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <div>
                        <div class="font-bold text-gray-950">Word Document (.docx)</div>
                        <div class="text-xs text-gray-500">Best for editing</div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    const formatModal = document.getElementById('format-download-modal');
    if (!formatModal) return;

    let onFormatSelectedCallback = null;

    window.openFormatDownloadModal = (callback) => {
        onFormatSelectedCallback = callback;
        formatModal.classList.remove('hidden');
        formatModal.classList.add('flex');
    };

    const closeFormatModal = () => {
        formatModal.classList.add('hidden');
        formatModal.classList.remove('flex');
    };

    document.addEventListener('click', (event) => {
        if (event.target === formatModal || event.target.closest('[data-close-format-modal]')) {
            event.preventDefault();
            closeFormatModal();
        }

        const optionBtn = event.target.closest('.format-option-btn');
        if (optionBtn) {
            event.preventDefault();
            const format = optionBtn.dataset.format;
            if (onFormatSelectedCallback) onFormatSelectedCallback(format);
            closeFormatModal();
        }
    });
})();
</script>
