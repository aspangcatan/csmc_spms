{{--
    Year / month / division / section / name filters shared by the PMT queue and dashboard.
    Params: $action (form URL), plus $year, $month, $division, $section, $search, $divisions, $sections from the controller.
--}}
<form method="GET" action="{{ $action }}" id="pmtFilterForm"
      class="bg-white px-4 py-3 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-end gap-4 mb-6">
    {{-- Keep the PMT Approvals tab when filtering --}}
    @if(!empty($show) && $show !== 'pending')
        <input type="hidden" name="show" value="{{ $show }}">
    @endif
    <div>
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Fiscal Year</p>
        <select name="year" id="pmtYearFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer" onchange="this.form.submit()">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="h-8 w-px bg-gray-100"></div>
    <div>
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1" title="Month the record last changed status">Month</p>
        <select name="month" id="pmtMonthFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer" onchange="this.form.submit()">
            <option value="">All</option>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
            @endfor
        </select>
    </div>
    <div class="h-8 w-px bg-gray-100"></div>
    <div class="min-w-[160px]">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Division</p>
        {{-- Changing the division clears the section, since sections belong to a division --}}
        <select name="division" id="pmtDivisionFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer w-full"
                onchange="this.form.elements.section.value = ''; this.form.submit()">
            <option value="">All</option>
            @foreach($divisions as $item)
                <option value="{{ $item->id }}" {{ (string) $division === (string) $item->id ? 'selected' : '' }}>{{ $item->description }}</option>
            @endforeach
        </select>
    </div>
    <div class="h-8 w-px bg-gray-100"></div>
    <div class="min-w-[160px]">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Section</p>
        <select name="section" id="pmtSectionFilter" class="bg-transparent border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-pointer w-full" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($sections as $item)
                <option value="{{ $item->id }}" {{ (string) $section === (string) $item->id ? 'selected' : '' }}>{{ $item->description }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-grow min-w-[220px] relative">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search employee name..."
               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-4 focus:ring-orange-50 focus:border-orange-500 outline-none transition-all">
    </div>
    <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-black transition-all">
        Apply
    </button>
    @if($month || $division || $section || $search !== '')
        <a href="{{ $action }}?{{ http_build_query(array_filter(['year' => $year, 'show' => ($show ?? 'pending') !== 'pending' ? $show : null])) }}" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-500 font-bold text-[10px] uppercase tracking-widest hover:bg-gray-50 transition-all">
            Clear
        </a>
    @endif
</form>
