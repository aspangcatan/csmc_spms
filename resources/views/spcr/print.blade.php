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

            $coreAvg = (float) ($coreEntries->avg('average_rating') ?: 0);
            $supportAvg = (float) ($supportEntries->avg('average_rating') ?: 0);
            $strategicAvg = (float) ($strategicEntries->avg('average_rating') ?: 0);

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
                    <td class="doc-info" style="width: 17.5%;">HOPSS-HRMS-FM-02</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Revision No.:</td>
                    <td class="doc-info">Rev. 0</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Effectivity:</td>
                    <td class="doc-info">Feb 2026</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td colspan="15" class="commitment-text no-border-top">
                        I, <span class="underline bold">{{ strtoupper($spcr->user->name ?? 'N/A') }}</span>,
                        <span class="underline bold">{{ $spcr->user->designation_name ?? '' }}</span> of the
                        <span class="underline bold">{{ $spcr->user->division_name ?? '' }}</span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period
                        <span class="underline bold">{{ $periodFrom }} to {{ $periodTo }}</span>.
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td colspan="8" class="no-border-top">
                        Name of Section Chief: <span style="margin-left: 25px;"><strong>{{ strtoupper($spcr->user->name ?? 'N/A') }}</strong></span>
                    </td>
                    <td colspan="7" class="no-border-top">
                        Date: <span style="margin-left: 20px;"><strong>{{ $spcrDateDisplay }}</strong></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="no-border-bottom"><strong>Approved By:</strong></td>
                    <td colspan="7">Date: <span style="margin-left: 20px;"><strong>{{ $doneDateDisplay }}</strong></span></td>
                </tr>
                <tr>
                    <td colspan="8" class="center no-border-top"><strong>{{ strtoupper($spcr->supervisor->name ?? 'N/A') }}</strong></td>
                    <td colspan="7" rowspan="2"></td>
                </tr>
                <tr>
                    <td colspan="8" class="center"><strong>Name of Supervisor</strong></td>
                </tr>
            </table>

            <table>
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
                        <td rowspan="2">Strategic Goals and Objectives</td>
                        <td colspan="3">Success Indicator (Targets + Measure)</td>
                        <td rowspan="2">Individual Accountable</td>
                        <td colspan="4">Actual Accomplishment</td>
                        <td rowspan="2">Accomplishment Rate (Actual / Target x 100%)</td>
                        <td colspan="4">RATING</td>
                        <td rowspan="2">Remarks/ Justification of Unmet Targets</td>
                    </tr>
                    <tr class="column-header">
                        <td colspan="3"></td>
                        <td colspan="4"></td>
                        <td>Q (1)</td>
                        <td>E (2)</td>
                        <td>T (3)</td>
                        <td>A (4)</td>
                    </tr>
                </thead>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Core Functions</i></td>
                    </tr>
                    @forelse($coreEntries as $entry)
                        <tr>
                            <td>{{ $entry->output }}</td>
                            <td colspan="3">{{ $entry->success_indicator }}</td>
                            <td class="center">{{ $entry->accountability }}</td>
                            <td colspan="4">{{ $entry->actual_accomplishment }}</td>
                            <td class="center">{{ $entry->accomplishment_rate }}</td>
                            <td class="center">{{ $entry->quantity_rating ?: '' }}</td>
                            <td class="center">{{ $entry->efficiency_rating ?: '' }}</td>
                            <td class="center">{{ $entry->timeliness_rating ?: '' }}</td>
                            <td class="center">{{ $entry->average_rating ? number_format((float) $entry->average_rating, 2) : '' }}</td>
                            <td>{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No core function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="10" class="text-right">Average Rating (Core Functions)</td>
                        <td colspan="4" class="center">{{ number_format($coreAvg, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Support Functions</i></td>
                    </tr>
                    @forelse($supportEntries as $entry)
                        <tr>
                            <td>{{ $entry->output }}</td>
                            <td colspan="3">{{ $entry->success_indicator }}</td>
                            <td class="center">{{ $entry->accountability }}</td>
                            <td colspan="4">{{ $entry->actual_accomplishment }}</td>
                            <td class="center">{{ $entry->accomplishment_rate }}</td>
                            <td class="center">{{ $entry->quantity_rating ?: '' }}</td>
                            <td class="center">{{ $entry->efficiency_rating ?: '' }}</td>
                            <td class="center">{{ $entry->timeliness_rating ?: '' }}</td>
                            <td class="center">{{ $entry->average_rating ? number_format((float) $entry->average_rating, 2) : '' }}</td>
                            <td>{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No support function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="10" class="text-right">Average Rating (Support Functions)</td>
                        <td colspan="4" class="center">{{ number_format($supportAvg, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

                <tbody>
                    <tr class="section-header">
                        <td colspan="15"><i>Strategic Functions</i></td>
                    </tr>
                    @forelse($strategicEntries as $entry)
                        <tr>
                            <td>{{ $entry->output }}</td>
                            <td colspan="3">{{ $entry->success_indicator }}</td>
                            <td class="center">{{ $entry->accountability }}</td>
                            <td colspan="4">{{ $entry->actual_accomplishment }}</td>
                            <td class="center">{{ $entry->accomplishment_rate }}</td>
                            <td class="center">{{ $entry->quantity_rating ?: '' }}</td>
                            <td class="center">{{ $entry->efficiency_rating ?: '' }}</td>
                            <td class="center">{{ $entry->timeliness_rating ?: '' }}</td>
                            <td class="center">{{ $entry->average_rating ? number_format((float) $entry->average_rating, 2) : '' }}</td>
                            <td>{{ $entry->remarks }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="center">No strategic function records</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="10" class="text-right">Average Rating (Strategic Functions)</td>
                        <td colspan="4" class="center">{{ number_format($strategicAvg, 2) }}</td>
                        <td></td>
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
                    <td colspan="2">{{ number_format($coreAvg, 2) }}</td>
                    <td colspan="4">{{ number_format($coreFinal, 2) }}</td>
                    <td colspan="2" rowspan="3" style="vertical-align: middle;" class="bold">{{ number_format($finalAverage, 2) }}</td>
                    <td colspan="2" rowspan="3" style="vertical-align: middle;" class="bold">{{ $finalAdjective }}</td>
                    <td colspan="2" rowspan="3"></td>
                </tr>
                <tr class="center">
                    <td class="bold" style="text-align: left;">Support Functions</td>
                    <td colspan="2">{{ rtrim(rtrim(number_format($supportWeight, 2), '0'), '.') }}</td>
                    <td colspan="2">{{ number_format($supportAvg, 2) }}</td>
                    <td colspan="4">{{ number_format($supportFinal, 2) }}</td>
                </tr>
                <tr class="center">
                    <td class="bold" style="text-align: left;">Strategic Functions</td>
                    <td colspan="2">{{ rtrim(rtrim(number_format($strategicWeight, 2), '0'), '.') }}</td>
                    <td colspan="2">{{ number_format($strategicAvg, 2) }}</td>
                    <td colspan="4">{{ number_format($strategicFinal, 2) }}</td>
                </tr>

                <tr class="center bold">
                    <td>Discussed With:</td>
                    <td colspan="2">Assessed by:</td>
                    <td colspan="2">Date</td>
                    <td colspan="9">Final Rating by:</td>
                    <td colspan="2">Date</td>
                </tr>
                <tr class="center">
                    <td style="line-height: 1.2;">
                        I certify that I discussed my assessment of the performance with the employee.
                    </td>
                    <td colspan="2" rowspan="2" style="vertical-align: bottom; padding-bottom: 10px;">
                        <strong><u>{{ strtoupper($spcr->supervisor->name ?? '') }}</u></strong><br>
                        <span class="small-text">Supervisor</span>
                    </td>
                    <td colspan="2" rowspan="2"></td>
                    <td colspan="9" rowspan="2" style="vertical-align: bottom; padding-bottom: 10px;">
                        <strong><u>{{ strtoupper($spcr->highestSupervisor->name ?? ($spcr->pmt->name ?? '')) }}</u></strong><br>
                        <span class="small-text">Next Higher Supervisor</span>
                    </td>
                    <td colspan="2" rowspan="2"></td>
                </tr>
                <tr class="center">
                    <td style="padding-top: 0;">
                        <strong><u>{{ strtoupper($spcr->user->name ?? '') }}</u></strong><br>
                        <span class="small-text">Employee</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="15" class="small-text">
                        <strong>Legend:</strong> 1 - Quality 2 - Efficiency 3 - Timeliness 4 - Average; *In the event that there is no strategic output, the percentage distribution is as follows: Core output - 80% and Support output - 20%.
                    </td>
                </tr>
            </table>

            <div class="pmt-stamp">
                <img src="{{ asset('img/img_pmt.png') }}" alt="PMT Stamp">
            </div>
        </div>
    </div>
</body>
</html>
