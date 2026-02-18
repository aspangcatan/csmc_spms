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
            background: white;
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
            font-size: 8px;
        }

        .header-row {
            background-color: #fff;
        }

        .logo-cell {
            text-align: center;
            vertical-align: middle;
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
            font-size: 7px;
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

        .section-header {
            background-color: #f3f4f6;
            font-weight: bold;
            font-style: italic;
            padding: 4px 5px;
            text-transform: uppercase;
            font-size: 9px;
        }

        .column-header {
            background-color: #f9fafb;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
            padding: 4px 2px;
        }

        .average-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #f06a38;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(240, 106, 56, 0.2);
            z-index: 1000;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .signature-space {
            padding-top: 30px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print SPCR</button>

    <div class="print-container">
        @php
            $coreAvg = $spcr->entries->where('category', 'core')->avg('average_rating') ?: 0;
            $supportAvg = $spcr->entries->where('category', 'support')->avg('average_rating') ?: 0;
            $strategicAvg = $spcr->entries->where('category', 'strategic')->avg('average_rating') ?: 0;
            
            $weightedCore = $coreAvg * ($spcr->core_dist / 100);
            $weightedSupport = $supportAvg * ($spcr->support_dist / 100);
            $weightedStrategic = $strategicAvg * ($spcr->strategic_dist / 100);
            $finalScore = $weightedCore + $weightedSupport + $weightedStrategic;
        @endphp

        <!-- Header -->
        <table>
            <tr class="header-row">
                <td rowspan="3" style="width: 15%;">DOH-SPMS Form 4 (SPCR)</td>
                <td rowspan="3" class="logo-cell" style="width: 50%;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <img src="{{ asset('img/img_csmc.jpg') }}" alt="Logo">
                        <span class="title-text">STRATEGIC PERFORMANCE COMMITMENT AND REVIEW (SPCR)</span>
                        <img src="{{ asset('img/img_doh.png') }}" alt="Logo">
                    </div>
                </td>
                <td class="doc-info" style="width: 17.5%;">Document Code:</td>
                <td class="doc-info">CSMC-SPCR-FM-01</td>
            </tr>
            <tr>
                <td class="doc-info">Revision No.:</td>
                <td class="doc-info">Rev. 0</td>
            </tr>
            <tr>
                <td class="doc-info">Effectivity:</td>
                <td class="doc-info">Feb 2026</td>
            </tr>
        </table>

        <!-- Commitment -->
        <table>
            <tr>
                <td colspan="4" class="commitment-text">
                    I, <span class="underline bold">{{ $spcr->user->name }}</span>, 
                    <span class="underline bold">{{ $spcr->user->designation_name }}</span> of the 
                    <span class="underline bold">{{ $spcr->user->division_name }}</span>, commit to deliver and agree to be rated on the attainment of the following strategic targets for the period 
                    <span class="underline bold">{{ $spcr->year }}</span> - <span class="underline bold">{{ $spcr->semester == 1 ? '1st' : '2nd' }} Semester</span>.
                </td>
            </tr>
        </table>

        <!-- Main Table -->
        <table>
            <thead>
                <tr class="column-header">
                    <td rowspan="2" style="width: 15%;">Strategic Goals & Objectives</td>
                    <td rowspan="2" style="width: 15%;">Success Indicator</td>
                    <td rowspan="2" style="width: 10%;">Accountable</td>
                    <td rowspan="2" style="width: 15%;">Actual Accomplishment</td>
                    <td rowspan="2" style="width: 8%;">Rate</td>
                    <td colspan="4">Rating</td>
                    <td rowspan="2" style="width: 10%;">Remarks</td>
                </tr>
                <tr class="column-header">
                    <td style="width: 3%;">Q</td>
                    <td style="width: 3%;">E</td>
                    <td style="width: 3%;">T</td>
                    <td style="width: 4%;">A</td>
                </tr>
            </thead>

            @foreach(['core' => 'Core Functions', 'support' => 'Support Functions', 'strategic' => 'Strategic Functions'] as $cat => $label)
                <tbody class="section-functions">
                    <tr class="section-header">
                        <td colspan="10">{{ $label }}</td>
                    </tr>
                    @php $entries = $spcr->entries->where('category', $cat); @endphp
                    @forelse($entries as $e)
                    <tr>
                        <td>{{ $e->output }}</td>
                        <td>{{ $e->success_indicator }}</td>
                        <td class="center">{{ $e->accountability }}</td>
                        <td>{{ $e->actual_accomplishment }}</td>
                        <td class="center">{{ $e->accomplishment_rate }}</td>
                        <td class="center">{{ $e->quantity_rating }}</td>
                        <td class="center">{{ $e->efficiency_rating }}</td>
                        <td class="center">{{ $e->timeliness_rating }}</td>
                        <td class="center bold" style="background-color: #f9fafb;">{{ number_format($e->average_rating, 2) }}</td>
                        <td>{{ $e->remarks }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="center italic">No data recorded</td></tr>
                    @endforelse
                    <tr class="average-row">
                        <td colspan="8" style="text-align: right; padding-right: 10px;">Category Weighted Score ({{ $cat == 'core' ? $spcr->core_dist : ($cat == 'support' ? $spcr->support_dist : $spcr->strategic_dist) }}%)</td>
                        <td class="center">{{ number_format($entries->avg('average_rating') ?: 0, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            @endforeach

            <!-- Summary -->
            <tr class="section-header" style="background-color: #111827; color: white;">
                <td colspan="8" style="text-align: right; font-size: 10px; padding: 6px;">FINAL AVERAGE RATING</td>
                <td class="center" style="font-size: 10px; padding: 6px;">{{ number_format($finalScore, 2) }}</td>
                <td class="center" style="font-size: 10px; padding: 6px;">{{ $spcr->final_rating_adjective }}</td>
            </tr>
        </table>

        <!-- Signatures -->
        <table style="margin-top: 20px;">
            <tr>
                <td style="width: 33%;" class="center">
                    <p class="bold">Discussed With:</p>
                    <div class="signature-space"></div>
                    <p class="bold underline">{{ $spcr->user->name }}</p>
                    <p>Division Head / Employee</p>
                </td>
                <td style="width: 34%;" class="center">
                    <p class="bold">Assessed By:</p>
                    <div class="signature-space"></div>
                    <p class="bold underline">{{ $spcr->supervisor->name ?? '---' }}</p>
                    <p>Supervisor / Reviewer</p>
                </td>
                <td style="width: 33%;" class="center">
                    <p class="bold">Final Rating By:</p>
                    <div class="signature-space"></div>
                    <p class="bold underline">{{ $spcr->highestSupervisor->name ?? 'Next Higher Supervisor' }}</p>
                    <p>Next Higher Supervisor</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
