{{--
    "Select all / Delete selected" toolbar for the IPCR/SPCR editor tables; the per-row delete buttons stay.
    Rows opt in with a .row-select checkbox. Removed rows are only deleted for good when the document is saved.
    Params: $modalId (the editor modal's id).
--}}
<div class="row-bulk-actions flex items-center justify-between px-4 py-2 mb-3 bg-gray-50 rounded-xl border border-gray-100">
    <label class="flex items-center gap-2 text-[10px] font-black text-gray-500 uppercase tracking-widest cursor-pointer mb-0">
        <input type="checkbox" class="row-select-all w-4 h-4 rounded border-gray-300 text-red-500 cursor-pointer focus:ring-red-400">
        Select all rows
    </label>
    <button type="button" disabled
            class="row-delete-selected px-4 h-8 rounded-xl bg-white border border-red-200 text-red-500 text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all shadow-sm flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
        <i class="fas fa-trash-alt"></i> Delete Selected (<span class="row-selected-count">0</span>)
    </button>
</div>

@push('scripts')
<script>
    (function () {
        const modal = $('#{{ $modalId }}');

        function refreshRowSelection() {
            const boxes = modal.find('.row-select:enabled');
            const checked = boxes.filter(':checked').length;
            modal.find('.row-selected-count').text(checked);
            modal.find('.row-delete-selected').prop('disabled', checked === 0);
            modal.find('.row-select-all')
                .prop('checked', boxes.length > 0 && checked === boxes.length)
                .prop('indeterminate', checked > 0 && checked < boxes.length);
        }

        modal.on('change', '.row-select', refreshRowSelection);

        modal.on('change', '.row-select-all', function () {
            modal.find('.row-select:enabled').prop('checked', this.checked);
            refreshRowSelection();
        });

        modal.on('click', '.row-delete-selected', function () {
            const rows = modal.find('.row-select:checked').closest('tr');
            if (!rows.length) return;
            confirmAction(
                'Delete Selected Rows?',
                `Remove ${rows.length} selected row(s)? The change is saved with the document.`,
                'DELETE',
                () => {
                    rows.remove();
                    refreshRowSelection();
                }
            );
        });

        // Rows are rebuilt whenever a document opens, so each session starts with nothing selected.
        modal.on('hidden.bs.modal', function () {
            modal.find('.row-select, .row-select-all').prop('checked', false);
            refreshRowSelection();
        });
    })();
</script>
@endpush
