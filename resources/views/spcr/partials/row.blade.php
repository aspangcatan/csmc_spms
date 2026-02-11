<tr class="group hover:bg-orange-50/30 transition-colors border-b border-gray-100">
    <input type="hidden" class="row-id" value="">
    {{-- 1. Strategic Goals & Objectives --}}
    <td class="border-e border-gray-100 p-2 align-middle !text-center">
        <textarea class="w-full border-0 bg-transparent text-[11px] p-0 resize-none focus:ring-0 placeholder-gray-300 font-medium !text-center output-field" style="text-align: center !important;" rows="5" placeholder="Describe goal..."></textarea>
    </td>

    {{-- 2. Success Indicator --}}
    <td class="border-e border-gray-100 p-2 align-top !text-center">
        <textarea class="w-full border-0 bg-transparent text-[11px] p-0 resize-none focus:ring-0 placeholder-gray-300 font-medium !text-center indicator-field" style="text-align: start !important;" rows="5" placeholder="Success indicators..."></textarea>
    </td>

    {{-- 3. Individual Accountable --}}
    <td class="border-e border-gray-100 p-2 align-middle !text-center">
        <textarea class="w-full border-0 bg-transparent text-[11px] p-0 resize-none focus:ring-0 placeholder-gray-300 font-bold text-gray-700 !text-center accountability-field" style="text-align: center !important;" rows="5" placeholder="e.g. John Doe"></textarea>
    </td>

    {{-- 4. Actual Accomplishment --}}
    <td class="border-e border-gray-100 p-2 align-middle !text-center">
        <textarea class="w-full border-0 bg-transparent text-[11px] p-0 resize-none focus:ring-0 placeholder-gray-300 font-medium !text-center accomplishment-field" style="text-align: center !important;" rows="5" placeholder="Describe actual results..."></textarea>
    </td>

    {{-- 5. Accomplishment Rate --}}
    <td class="border-e border-gray-100 p-2 align-middle !text-center">
        <input type="text" class="w-full border-0 bg-transparent text-[11px] font-black p-0 focus:ring-0 text-emerald-600 !text-center acc-rate-field" style="text-align: center !important;" placeholder="100%">
    </td>

    {{-- 6-9. Rating --}}
    <td class="border-e border-gray-100 p-2 !text-center w-10 align-middle">
        <input type="text" class="w-full !text-center border-0 bg-transparent text-[11px] font-bold p-0 focus:ring-0 q-rating" style="text-align: center !important;" placeholder="0" />
    </td>
    <td class="border-e border-gray-100 p-2 !text-center w-10 align-middle">
        <input type="text" class="w-full !text-center border-0 bg-transparent text-[11px] font-bold p-0 focus:ring-0 e-rating" style="text-align: center !important;" placeholder="0" />
    </td>
    <td class="border-e border-gray-100 p-2 !text-center w-10 align-middle">
        <input type="text" class="w-full !text-center border-0 bg-transparent text-[11px] font-bold p-0 focus:ring-0 t-rating" style="text-align: center !important;" placeholder="0" />
    </td>
    <td class="border-e border-gray-100 p-2 !text-center w-12 align-middle">
        <input type="text" class="w-full !text-center border-0 bg-orange-50/50 rounded-lg text-[11px] font-black p-1 focus:ring-0 text-orange-600 a-rating" style="text-align: center !important;" placeholder="0.0" readonly />
    </td>

    {{-- 10. Remarks --}}
    <td class="border-e border-gray-100 p-2 align-middle !text-center">
        <textarea class="w-full border-0 bg-transparent text-[11px] p-0 resize-none focus:ring-0 placeholder-gray-300 !text-center remarks-field" style="text-align: center !important;" rows="5" placeholder="Remarks..."></textarea>
    </td>

    {{-- 11. Action --}}
    <td class="p-2 text-center align-middle">
        <button type="button" class="w-7 h-7 rounded-full flex items-center justify-center mx-auto text-red-400 hover:text-white hover:bg-red-500 transition-all shadow-sm border border-red-50 delete-row-btn" onclick="removeRow(this)">
            <i class="fas fa-trash-alt text-[10px]"></i>
        </button>
    </td>
</tr>
