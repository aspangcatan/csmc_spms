<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SPCR Form - Print</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: #fff;
        }

        .print-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        td, th {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9px;
        }

        .header-row {
            background-color: #fff;
        }

        .logo-cell {
            text-align: center;
            vertical-align: middle;
            position: relative;
        }

        .logo-cell img {
            height: 40px;
            width: 40px;
            vertical-align: middle;
            margin: 0 10px;
        }

        .title-text {
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
            vertical-align: middle;
        }

        .doc-info {
            font-size: 8px;
            text-align: left;
        }

        .commitment-text {
            font-size: 9px;
            padding: 8px 4px;
            line-height: 1.4;
            text-align: justify;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }

        .text-right {
            text-align: right;
        }

        .section-header {
            background-color: #fff;
            font-weight: bold;
            font-style: italic;
            padding: 3px 5px;
        }

        .column-header {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
            padding: 3px 2px;
        }

        .column-header td {
            text-align: center;
            vertical-align: middle;
        }

        .rating-section {
            background-color: #ddd9c4;
        }

        .average-row {
            background-color: #ddd9c4;
            font-weight: bold;
        }

        .small-text {
            font-size: 7px;
        }

        .medium-text {
            font-size: 9px;
        }

        .no-border {
            border: none !important;
        }

        .no-border-top {
            border-top: none !important;
        }

        .no-border-bottom {
            border-bottom: none !important;
        }

        .no-border-left {
            border-left: none !important;
        }

        .no-border-right {
            border-right: none !important;
        }

        .annex {
            text-align: right;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background-color: #45a049;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(200, 200, 200, 0.25);
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        .annex {
            text-align: right;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .pmt-stamp {
            position: absolute;
            bottom: 0;
            right: 5px;
            z-index: 1000;
        }

        .pmt-stamp img {
            width: 180px;
            height: auto;
        }
        #spcr-entries tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Lay the page out at A4-landscape printable width while measuring page breaks. */
        html.spcr-paginating body {
            width: 1026px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print SPCR</button>

    <div class="print-container">
        @php
            $year = (int) $spcr->year;

            $periodFrom = $spcr->period_from
                ? \Carbon\Carbon::parse($spcr->period_from)->format('F Y')
                : ($spcr->semester == 1 ? "January {$year}" : "July {$year}");
            $periodTo = $spcr->period_to
                ? \Carbon\Carbon::parse($spcr->period_to)->format('F Y')
                : ($spcr->semester == 1 ? "June {$year}" : "December {$year}");

            $spcrDateDisplay = $spcr->spcr_date ? \Carbon\Carbon::parse($spcr->spcr_date)->format('F j, Y') : '';
            $doneDateDisplay = $spcr->spcr_date ? \Carbon\Carbon::parse($spcr->spcr_date)->format('m/d/Y') : '';

            $coreEntries = $spcr->entries->where('category', 'core')->values();
            $supportEntries = $spcr->entries->where('category', 'support')->values();
            $strategicEntries = $spcr->entries->where('category', 'strategic')->values();

            // Ratings are decimal columns returned as strings ("0.00" is truthy), so treat
            // anything <= 0 as "not rated": blank on the form and excluded from averages.
            $fmtRating = fn($value) => (float) $value > 0 ? number_format((float) $value, 2) : '';
            $ratedAvg = fn($entries) => (float) ($entries->filter(fn($e) => (float) $e->average_rating > 0)->avg('average_rating') ?: 0);

            // Merge consecutive rows that share the same Strategic Goal: the first row of a
            // group gets the rowspan, the rest get 0 (cell omitted).
            $outputSpans = function ($entries) {
                $spans = [];
                $start = null;
                foreach ($entries as $i => $entry) {
                    $key = trim((string) $entry->output);
                    if ($start !== null && $key !== '' && $key === trim((string) $entries[$start]->output)) {
                        $spans[$start]++;
                        $spans[$i] = 0;
                    } else {
                        $start = $i;
                        $spans[$i] = 1;
                    }
                }
                return $spans;
            };
            $spans = [
                'core' => $outputSpans($coreEntries),
                'support' => $outputSpans($supportEntries),
                'strategic' => $outputSpans($strategicEntries),
            ];

            $coreAvg = $ratedAvg($coreEntries);
            $supportAvg = $ratedAvg($supportEntries);
            $strategicAvg = $ratedAvg($strategicEntries);

            $coreWeight = ((float) ($spcr->core_dist ?: 0)) / 100;
            $supportWeight = ((float) ($spcr->support_dist ?: 0)) / 100;
            $strategicWeight = ((float) ($spcr->strategic_dist ?: 0)) / 100;

            $coreFinal = $coreAvg * $coreWeight;
            $supportFinal = $supportAvg * $supportWeight;
            $strategicFinal = $strategicAvg * $strategicWeight;

            $finalAverage = $coreFinal + $supportFinal + $strategicFinal;
            $finalAdjective = $spcr->final_rating_adjective ?: 'N/A';
        @endphp

        <div class="content-wrapper">
            <div class="watermark">SPCR</div>

            <div class="annex">Annex E</div>

            <table>
                <tr class="header-row">
                    <td rowspan="3" class="no-border-right no-border-bottom" style="width: 15%; vertical-align: top;">
                        DOH - SPMS Form 3
                    </td>
                    <td rowspan="3" class="logo-cell no-border-left no-border-bottom" style="width: 50%;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <img src="{{ asset('img/img_csmc.jpg') }}" alt="CSMC Logo">
                            <span class="title-text">SECTION PERFORMANCE COMMITMENT AND REVIEW (SPCR)</span>
                            <img src="{{ asset('img/img_doh.png') }}" alt="DOH Logo">
                        </div>
                    </td>
                    <td class="doc-info" style="width: 17.5%;">Document Code:</td>
                    <td class="doc-info" style="width: 17.5%;">HOPSS-HRMS-FM-26</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Revision No.:</td>
                    <td class="doc-info">Rev. 0</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Effectivity:</td>
                    <td class="doc-info">29 July 2025</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td colspan="15" class="commitment-text no-border-top">
                        I, <strong><span class="underline">
                          {{ strtoupper(trim(
    ($spcr->user->fname ?? '') . ' ' .
    (!empty($spcr->user->mname) ? substr($spcr->user->mname, 0, 1) . '. ' : '') .
    ($spcr->user->lname ?? '') .
    (!empty($spcr->user->suffix) ? ' ' . $spcr->user->suffix : '')
)) }}    
                        </span></strong>,
                        <span class="underline bold">{{ $spcr->user->designation_name ?? 'Position' }}</span>,
                        of the <span class="underline bold">{{  $spcr->user->division_name }} - {{  $spcr->user->section_name }}[{{ $spcr->user->section_acronym ?? '' }}]</span>, of the Cebu South Medical Center, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period
                        <span class="underline bold">{{ $periodFrom }} to {{ $periodTo }}</span>.
                    </td>
                </tr>
            </table>

            <table>
                <tr style="height: 36px;">
                    <td colspan="6" class="text-right" style="vertical-align: middle; border-top: none; border-right: none;">
                        <strong>Name of Section Chief:</strong>
                    </td>
                    <td colspan="7" class="center" style="vertical-align: middle; border-top: none; border-left: none; border-right: none;">
                        <strong><u>
                          {{ strtoupper(trim(
    ($spcr->user->fname ?? '') . ' ' .
    (!empty($spcr->user->mname) ? substr($spcr->user->mname, 0, 1) . '. ' : '') .
    ($spcr->user->lname ?? '') .
    (!empty($spcr->user->suffix) ? ' ' . $spcr->user->suffix : '')
)) }}     
                        </u></strong>
                    </td>
                    <td colspan="2" class="text-start" style="vertical-align: middle; border-top: none; border-left: none;">
                        <strong>Date:</strong> <span style="margin-left: 6px;"><strong>{{ $spcrDateDisplay }}</strong></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="13" class="no-border-bottom"><strong>Approved By:</strong></td>
                    <td colspan="2"><strong>Date:</strong> </td>
                </tr>
                <tr>
                    <td colspan="13" class="center no-border-top"><strong><u>
                          {{ strtoupper(trim(
    ($spcr->supervisor->fname ?? '') . ' ' .
    (!empty($spcr->supervisor->mname) ? substr($spcr->supervisor->mname, 0, 1) . '. ' : '') .
    ($spcr->supervisor->lname ?? '') .
    (!empty($spcr->supervisor->suffix) ? ' ' . $spcr->supervisor->suffix : '')
)) }}        
                    </u></strong></td>
                    <td colspan="2" rowspan="2" class="center bold" style="vertical-align: middle">{{ $spcrDateDisplay }}</td>
                </tr>
                <tr>
                    <td colspan="13" class="center"><strong>Name of Supervisor</strong></td>
                </tr>
            </table>

            <table id="spcr-entries">
                <colgroup>
                    <col style="width: 13%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 7%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 6%">
                    <col style="width: 3.5%">
                    <col style="width: 3.5%">
                    <col style="width: 3.5%">
                    <col style="width: 7.5%">
                </colgroup>
                <thead>
                    <tr class="column-header">
                        <td>Strategic Goals and Objectives</td>
                        <td colspan="3">Success Indicator (Targets + Measure)</td>
                        <td>Individual Accountable</td>
                        <td colspan="3">Actual Accomplishment</td>
                        <td>Accomplishment Rate (Actual Accomplishment / Target x 100%)</td>
                        <td>Q (1)</td>
                        <td>E (2)</td>
                        <td>T (3)</td>
                        <td>A (4)</td>
                        <td colspan="2">Remarks/ Justification of Unmet Targets (use separate sheet if needed)</td>
                    </tr>
                </thead>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Core Functions</i></td>
                    </tr>
                    @forelse($coreEntries as $entry)
                        <tr>
                            @if($spans[$entry->category][$loop->index] > 0)
                                <td rowspan="{{ $spans[$entry->category][$loop->index] }}" class="goal-cell center" style="vertical-align: middle;">{{ $entry->output }}</td>
                            @endif
                            <td colspan="3" style="text-align: left; vertical-align: middle;">{{ $entry->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accountability }}</td>
                            <td colspan="3" class="center" style="vertical-align: middle;">{{ $entry->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accomplishment_rate }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->quantity_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->efficiency_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->timeliness_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->average_rating) }}</td>
                            <td colspan="2" class="center" style="vertical-align: middle;">{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No core function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="11" class="text-right">Average Rating (Core Functions)</td>
                        <td></td>
                        <td class="center">{{ $fmtRating($coreAvg) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Support Functions</i></td>
                    </tr>
                    @forelse($supportEntries as $entry)
                        <tr>
                            @if($spans[$entry->category][$loop->index] > 0)
                                <td rowspan="{{ $spans[$entry->category][$loop->index] }}" class="goal-cell center" style="vertical-align: middle;">{{ $entry->output }}</td>
                            @endif
                            <td colspan="3" style="text-align: left; vertical-align: middle;">{{ $entry->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accountability }}</td>
                            <td colspan="3" class="center" style="vertical-align: middle;">{{ $entry->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accomplishment_rate }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->quantity_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->efficiency_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->timeliness_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->average_rating) }}</td>
                            <td colspan="2" class="center" style="vertical-align: middle;">{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No support function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="11" class="text-right">Average Rating (Support Functions)</td>
                        <td></td>
                        <td class="center">{{ $fmtRating($supportAvg) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Strategic Functions</i></td>
                    </tr>
                    @forelse($strategicEntries as $entry)
                        <tr>
                            @if($spans[$entry->category][$loop->index] > 0)
                                <td rowspan="{{ $spans[$entry->category][$loop->index] }}" class="goal-cell center" style="vertical-align: middle;">{{ $entry->output }}</td>
                            @endif
                            <td colspan="3" style="text-align: left; vertical-align: middle;">{{ $entry->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accountability }}</td>
                            <td colspan="3" class="center" style="vertical-align: middle;">{{ $entry->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $entry->accomplishment_rate }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->quantity_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->efficiency_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->timeliness_rating) }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $fmtRating($entry->average_rating) }}</td>
                            <td colspan="2" class="center" style="vertical-align: middle;">{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No strategic function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="11" class="text-right">Average Rating (Strategic Functions)</td>
                        <td></td>
                        <td class="center">{{ $fmtRating($strategicAvg) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>

                <tr class="rating-section">
                    <td colspan="15" class="bold">RATING</td>
                </tr>
                <tr class="column-header">
                    <td>Function</td>
                    <td colspan="2">Percentage Distribution*</td>
                    <td colspan="2">Average Rating per Function</td>
                    <td colspan="4">Final Rating per Function (Average Rating x Percentage Distribution)</td>
                    <td colspan="2">Final Average Rating</td>
                    <td colspan="2">Adjectival Rating</td>
                    <td colspan="2">Remarks</td>
                </tr>
                <tr class="center">
                    <td class="bold" style="text-align: left;">Core Functions</td>
                    <td colspan="2">{{ rtrim(rtrim(number_format($coreWeight, 2), '0'), '.') }}</td>
                    <td colspan="2">{{ $fmtRating($coreAvg) }}</td>
                    <td colspan="4">{{ $fmtRating($coreFinal) }}</td>
                    <td colspan="2" rowspan="3" style="vertical-align: middle;" class="bold">{{ $fmtRating($finalAverage) }}</td>
                    <td colspan="2" rowspan="3" style="vertical-align: middle;" class="bold">{{ $finalAdjective }}</td>
                    <td colspan="2" rowspan="3"></td>
                </tr>
                <tr class="center">
                    <td class="bold" style="text-align: left;">Support Functions</td>
                    <td colspan="2">{{ rtrim(rtrim(number_format($supportWeight, 2), '0'), '.') }}</td>
                    <td colspan="2">{{ $fmtRating($supportAvg) }}</td>
                    <td colspan="4">{{ $fmtRating($supportFinal) }}</td>
                </tr>
                <tr class="center">
                    <td class="bold" style="text-align: left;">Strategic Functions</td>
                    <td colspan="2">{{ rtrim(rtrim(number_format($strategicWeight, 2), '0'), '.') }}</td>
                    <td colspan="2">{{ $fmtRating($strategicAvg) }}</td>
                    <td colspan="4">{{ $fmtRating($strategicFinal) }}</td>
                </tr>
                {{-- #TODO --}}
                <tr class="center bold">
                    <td colspan="3">Discussed With:</td>
                    <td colspan="3">Assessed by:</td>
                    <td colspan="3">Date</td>
                    <td colspan="4">Final Rating by:</td>
                    <td colspan="2">Date</td>
                </tr>
                <tr class="center">
                    <td colspan="3" rowspan="2" style="height: 68px; vertical-align: bottom; padding-bottom: 8px;">
                        <strong><u>{{ strtoupper($spcr->user->name ?? '') }}</u></strong>
                    </td>
                    <td colspan="3" class="medium-text center" style="height: 20px;">
                        I certify that I discussed my assessment of the performance with the employee
                    </td>
                    <td colspan="3" rowspan="2"></td>
                    <td colspan="4" rowspan="2" style="vertical-align: bottom; padding-bottom: 8px;">
                        <strong><u>{{ strtoupper($spcr->highestSupervisor->name ?? ($spcr->pmt->name ?? '')) }}</u></strong>
                    </td>
                    <td colspan="2" rowspan="2"></td>
                </tr>
                <tr>
                    <td colspan="3" style="vertical-align: bottom; padding-bottom: 8px;" class="center">
                        <strong><u>{{ strtoupper($spcr->supervisor->name ?? '') }}</u></strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="bold center">Employee</td>
                    <td colspan="3" class="bold center">Supervisor</td>
                    <td colspan="3"></td>
                    <td colspan="4" class="bold center">Next Higher Supervisor</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="15" class="small-text" style="font-style: italic;">
                        Legend: 1 - Quality 2 - Efficiency 3 - Timeliness 4 - Average; *In the event that there is no strategic output, the percentage distribution is as follows: Core output - 80% and Support output - 20%.
                    </td>
                </tr>
                {{-- END OF TODO --}}
            </table>

            <div class="pmt-stamp">
                <img src="{{ asset('img/img_pmt.png') }}" alt="PMT Stamp">
            </div>
        </div>
    </div>
    <script>
        // A merged "Strategic Goals" cell that crosses a page break only shows its text on the
        // first page. Before printing, simulate the page breaks and split any such cell so the
        // goal is repeated at the top of the next page. The original markup is restored after.
        (function () {
            // A4 landscape (297 x 210mm) minus the 0.5in @page margins, in CSS px.
            var PAGE_HEIGHT = 210 / 25.4 * 96 - 96;
            var SAFETY = 4;
            var table = document.getElementById('spcr-entries');
            if (!table) return;
            var originalHtml = null;

            // Simulate Chrome's pagination of the entries table: returns the rows that start a
            // new page, and the first one that falls inside a merged goal cell (if any).
            function simulate() {
                var thead = table.tHead;
                var theadHeight = thead ? thead.getBoundingClientRect().height : 0;
                var rows = [];
                Array.prototype.forEach.call(table.tBodies, function (tbody) {
                    Array.prototype.push.apply(rows, tbody.rows);
                });
                var result = { pageStarts: [], midGroup: null };
                if (!rows.length) return result;

                var cursor = rows[0].getBoundingClientRect().top + window.scrollY;
                var group = null;
                for (var i = 0; i < rows.length; i++) {
                    var goal = rows[i].querySelector('td.goal-cell');
                    if (goal) group = { cell: goal, start: i, startRow: rows[i], span: goal.rowSpan };

                    var height = rows[i].getBoundingClientRect().height;
                    var forced = rows[i].style.breakBefore === 'page';
                    if (i > 0 && (forced || cursor + height > PAGE_HEIGHT - SAFETY)) {
                        result.pageStarts.push(rows[i]);
                        if (!result.midGroup && group && i > group.start && i < group.start + group.span) {
                            result.midGroup = { row: rows[i], index: i, group: group };
                        }
                        cursor = theadHeight;
                    }
                    cursor += height;
                }
                return result;
            }

            function paginate() {
                if (originalHtml !== null) return;
                originalHtml = table.innerHTML;
                document.documentElement.classList.add('spcr-paginating');
                for (var guard = 0; guard < 500; guard++) {
                    var before = simulate();
                    var found = before.midGroup;
                    if (!found) break;

                    var group = found.group;
                    var headRows = found.index - group.start;
                    var clone = group.cell.cloneNode(true);
                    clone.rowSpan = group.span - headRows;
                    group.cell.rowSpan = headRows;
                    found.row.insertBefore(clone, found.row.firstChild);

                    // Giving the first part its own copy of the goal text can make it too tall
                    // to stay on this page. If so, undo the split and start the group on a new page.
                    var after = simulate();
                    var pushed = after.pageStarts.indexOf(group.startRow) !== -1
                        && before.pageStarts.indexOf(group.startRow) === -1;
                    if (pushed) {
                        found.row.removeChild(clone);
                        group.cell.rowSpan = group.span;
                        group.startRow.style.breakBefore = 'page';
                    }
                }
            }

            function restore() {
                if (originalHtml === null) return;
                table.innerHTML = originalHtml;
                originalHtml = null;
                document.documentElement.classList.remove('spcr-paginating');
            }

            window.addEventListener('beforeprint', paginate);
            window.addEventListener('afterprint', restore);
        })();
    </script>
</body>
</html>
