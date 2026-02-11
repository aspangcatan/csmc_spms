<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IPCR Form - Print</title>
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
            font-size: 9px;
        }

        .header-row {
            background-color: #fff;
        }

        .header-cell {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
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

        .form-label {
            font-size: 8px;
            text-align: left;
        }

        .commitment-text {
            font-size: 9px;
            padding: 8px 4px;
            line-height: 1.4;
            text-align: justify;
        }

        .employee-info {
            text-align: right;
            padding: 5px;
        }

        .approval-section {
            padding: 5px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .italic {
            font-style: italic;
        }

        .underline {
            text-decoration: underline;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
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

        .signature-space {
            padding-top: 25px;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background-color: #45a049;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(200, 200, 200, 0.3);
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
        }

        .pmt-stamp {
            position: absolute;
            bottom: 40px;
            right: 10px;
            z-index: 1000;
        }

        .pmt-stamp img {
            width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print IPCR</button>

    <div class="print-container">
        @php
            // Helper function to calculate average
            $calculateAverage = function($functions) {
                $sum = 0;
                $count = 0;
                foreach ($functions as $f) {
                    $avg = $f->average_rating;
                    if ($avg === null) {
                        $ratings = array_filter([$f->quantity_rating, $f->efficiency_rating, $f->timeliness_rating], fn($v) => $v !== null && $v > 0);
                        $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : null;
                    }
                    if ($avg !== null) {
                        $sum += $avg;
                        $count++;
                    }
                }
                return $count > 0 ? $sum / $count : 0;
            };

            // Helper to group functions by Output
            $groupByOutput = function($functions) {
                $grouped = [];
                foreach ($functions as $f) {
                    $key = $f->output; 
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [];
                    }
                    $grouped[$key][] = $f;
                }
                return $grouped;
            };

            $coreAvg = $calculateAverage($ipcr->coreFunctions);
            $supportAvg = $calculateAverage($ipcr->supportFunctions);
            $strategicAvg = $calculateAverage($ipcr->strategicFunctions);

            $weightedCore = $coreAvg * 0.5;
            $weightedSupport = $supportAvg * 0.1;
            $weightedStrategic = $strategicAvg * 0.4;
            $finalScore = $weightedCore + $weightedSupport + $weightedStrategic;

            $groupedCore = $groupByOutput($ipcr->coreFunctions);
            $groupedSupport = $groupByOutput($ipcr->supportFunctions);
            $groupedStrategic = $groupByOutput($ipcr->strategicFunctions);

        @endphp

        <div class="content-wrapper">
            <!-- Watermark -->
            <div class="watermark">Page 1</div>

            <!-- Header Section -->
            <table>
                <tr class="header-row">
                    <td rowspan="3" class="form-label no-border-right no-border-bottom" style="width: 15%; vertical-align: top;">
                        DOH-SPMS Form 4
                    </td>
                    <td rowspan="3" class="logo-cell no-border-left no-border-bottom" style="width: 50%;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <img src="{{ asset('img/img_csmc.jpg') }}" alt="CSMC Logo">
                            <span class="title-text" style="text-align: center;">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)</span>
                            <img src="{{ asset('img/img_doh.png') }}" alt="DOH Logo">
                        </div>
                    </td>
                    <td class="doc-info" style="width: 17.5%;">Document Code:</td>
                    <td class="doc-info" style="width: 17.5%;">HOPSS-HRMS-FM-01</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Revision No.:</td>
                    <td class="doc-info">Rev. 1</td>
                </tr>
                <tr class="header-row">
                    <td class="doc-info">Effectivity:</td>
                    <td class="doc-info">05 Dec 2022</td>
                </tr>
            </table>

            <!-- Commitment Statement -->
            <table>
                <tr>
                    <td colspan="8" class="commitment-text no-border-top">
                        I, <span class="underline bold">{{ $ipcr->user->name ?? 'N/A' }}</span>, 
                        <span class="underline bold">{{ $ipcr->user->designation_name ?? '' }}</span> of the 
                        <span class="underline bold">{{ $ipcr->user->section_name ?? '' }}</span> of the Cebu South Medical Center, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period 
                        <span class="underline bold">{{ \Carbon\Carbon::parse($ipcr->period_from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($ipcr->period_to)->format('M d, Y') }}</span>.
                    </td>
                </tr>
            </table>

            <!-- Employee Info and Approval -->
            <table>
                <tr>
                    <td colspan="5" class="employee-info no-border-top">
                        Name of Employee: <span style="margin-left: 50px;"><strong>{{ $ipcr->user->name ?? 'N/A' }}</strong></span>
                    </td>
                    <td colspan="3" class="center no-border-top">
                        Date: <span style="margin-left: 20px;"><strong>{{ $ipcr->ipcr_date ? \Carbon\Carbon::parse($ipcr->ipcr_date)->format('m/d/Y') : 'Date Created' }}</strong></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="approval-section no-border-bottom">
                        <strong>Approved By:</strong>
                    </td>
                    <td colspan="3">
                        Date: <span style="margin-left: 20px;"><strong>{{ $ipcr->date_done ? \Carbon\Carbon::parse($ipcr->date_done)->format('m/d/Y') : 'Date Created' }}</strong></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="center no-border-top">
                        <strong>{{ $ipcr->supervisor->name ?? 'N/A' }}</strong>
                    </td>
                    <td colspan="3" rowspan="2"></td>
                </tr>
                <tr>
                    <td colspan="5" class="center">
                        <strong>Name of Supervisor</strong>
                    </td>
                </tr>
            </table>

            <!-- Main Content Table -->
            <table>
                <!-- Column Headers -->
                <thead>
                    <tr class="column-header">
                        <td rowspan="2" style="width: 12%; vertical-align: middle;">Output</td>
                        <td rowspan="2" style="width: 28%; vertical-align: middle;">Success Indicator (Targets + Measure)</td>
                        <td rowspan="2" style="width: 12%; vertical-align: middle;">Actual Accomplishment</td>
                        <td colspan="4" style="width: 20%;">RATING</td>
                        <td rowspan="2" style="width: 13%; vertical-align: middle;">Remarks/ Justification of Unmet Targets</td>
                    </tr>
                    <tr class="column-header">
                        <td style="width: 5%;">Q (1)</td>
                        <td style="width: 5%;">E (2)</td>
                        <td style="width: 5%;">T (3)</td>
                        <td style="width: 5%;">A (4)</td>
                    </tr>
                </thead>

                <!-- Core Functions -->
                <tbody class="section-functions">
                    <tr class="section-header">
                        <td colspan="8"><i>Core Functions</i></td>
                    </tr>
                    @foreach($groupedCore as $output => $functions)
                        @foreach($functions as $index => $func)
                        <tr style="page-break-inside: avoid;">
                            @if($index === 0)
                                <td rowspan="{{ count($functions) }}" class="center" style="vertical-align: middle;">{{ $output }}</td>
                            @endif
                            <td style="text-align: left; vertical-align: middle;">{{ $func->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->quantity_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->efficiency_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->timeliness_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">
                                @php
                                    $ratings = array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]);
                                    $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                                @endphp
                                {{ $func->average_rating ?? ($avg > 0 ? number_format($avg, 2) : '') }}
                            </td>
                            <td class="center" style="vertical-align: middle;">{{ $func->remarks }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    <tr class="average-row">
                        <td colspan="6" class="text-right">Average Rating (Core Functions)</td>
                        <td class="center">{{ number_format($coreAvg, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

                <!-- Support Functions -->
                <tbody class="section-functions">
                    <tr class="section-header">
                        <td colspan="8"><i>Support Functions</i></td>
                    </tr>
                    @foreach($groupedSupport as $output => $functions)
                        @foreach($functions as $index => $func)
                        <tr style="page-break-inside: avoid;">
                            @if($index === 0)
                                <td rowspan="{{ count($functions) }}" class="center" style="vertical-align: middle;">{{ $output }}</td>
                            @endif
                            <td style="text-align: left; vertical-align: middle;">{{ $func->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->quantity_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->efficiency_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->timeliness_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">
                                @php
                                    $ratings = array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]);
                                    $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                                @endphp
                                {{ $func->average_rating ?? ($avg > 0 ? number_format($avg, 2) : '') }}
                            </td>
                            <td class="center" style="vertical-align: middle;">{{ $func->remarks }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    <tr class="average-row">
                        <td colspan="6" class="text-right">Average Rating (Support Functions)</td>
                        <td class="center">{{ number_format($supportAvg, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

                <!-- Strategic Functions -->
                <tbody class="section-functions">
                    <tr class="section-header">
                        <td colspan="8"><i>Strategic Functions</i></td>
                    </tr>
                    @foreach($groupedStrategic as $output => $functions)
                        @foreach($functions as $index => $func)
                        <tr style="page-break-inside: avoid;">
                            @if($index === 0)
                                <td rowspan="{{ count($functions) }}" class="center" style="vertical-align: middle;">{{ $output }}</td>
                            @endif
                            <td style="text-align: left; vertical-align: middle;">{{ $func->success_indicator }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->actual_accomplishment }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->quantity_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->efficiency_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">{{ $func->timeliness_rating ?? '' }}</td>
                            <td class="center" style="vertical-align: middle;">
                                @php
                                    $ratings = array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]);
                                    $avg = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                                @endphp
                                {{ $func->average_rating ?? ($avg > 0 ? number_format($avg, 2) : '') }}
                            </td>
                            <td class="center" style="vertical-align: middle;">{{ $func->remarks }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    <tr class="average-row">
                        <td colspan="6" class="text-right">Average Rating (Strategic Functions)</td>
                        <td class="center">{{ number_format($strategicAvg, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>

                <!-- Rating Summary Section -->
                <tr class="rating-section">
                    <td colspan="8" class="bold">RATING</td>
                </tr>
                <tr class="column-header">
                    <td>Function</td>
                    <td>Percentage Distribution*</td>
                    <td>Average Rating per Function</td>
                    <td colspan="2">Final Rating per Function<br>(Average Rating x Percentage Distribution)</td>
                    <td>Final Average Rating</td>
                    <td>Adjectival Rating</td>
                    <td>Remarks</td>
                </tr>
                <tr class="center">
                    <td class="text-left bold">Core Functions</td>
                    <td>50%</td>
                    <td>{{ number_format($coreAvg, 2) }}</td>
                    <td colspan="2">{{ number_format($weightedCore, 2) }}</td>
                    <td rowspan="3" style="vertical-align: middle;" class="bold">{{ number_format($finalScore, 2) }}</td>
                    <td rowspan="3" style="vertical-align: middle;" class="bold">{{ $ipcr->final_rating_adjective ?? '' }}</td>
                    <td rowspan="3"></td>
                </tr>
                <tr class="center">
                    <td class="text-left bold">Support Functions</td>
                    <td>10%</td>
                    <td>{{ number_format($supportAvg, 2) }}</td>
                    <td colspan="2">{{ number_format($weightedSupport, 2) }}</td>
                </tr>
                <tr class="center">
                    <td class="text-left bold">Strategic Functions</td>
                    <td>40%</td>
                    <td>{{ number_format($strategicAvg, 2) }}</td>
                    <td colspan="2">{{ number_format($weightedStrategic, 2) }}</td>
                </tr>

                <!-- Comments Section -->
                <tr>
                    <td colspan="8" style="min-height: 40px; vertical-align: top; padding: 5px;">
                        <span class="bold">Comments and Recommendations for Development Purposes:</span> {{ $ipcr->comments }}
                    </td>
                </tr>

                <!-- Signature Section -->
                <tr class="center bold">
                    <td>Discussed With:</td>
                    <td>Assessed by:</td>
                    <td>Date</td>
                    <td colspan="4">Final Rating by:</td>
                    <td>Date</td>
                </tr>
                <tr class="center">
                    <td class="signature-space">
                        I certify that I discussed my assessment of the performance with the employee
                    </td>
                    <td rowspan="2" style="vertical-align: bottom; padding-bottom: 10px;">
                        <strong><u>{{ $ipcr->supervisor->name ?? '' }}</u></strong><br>
                        <span class="small-text">Supervisor</span>
                    </td>
                    <td rowspan="2"></td>
                    <td colspan="4" rowspan="2" style="vertical-align: bottom; padding-bottom: 10px;">
                        <strong><u>AGUSTIN D. AGOS, JR., MD, FPSGS, FPCS, DODT, PhD OD, RODC</u></strong><br>
                        <span class="small-text">Next Higher Supervisor</span>
                    </td>
                    <td rowspan="2"></td>
                </tr>
                <tr class="center">
                    <td style="padding-top: 0;">
                        <strong><u>{{ $ipcr->user->name ?? '' }}</u></strong><br>
                        <span class="small-text">Employee</span>
                    </td>
                </tr>

                <!-- Legend -->
                <tr>
                    <td colspan="8" class="small-text italic">
                        <strong>Legend:</strong> 1- Quality 2 -Efficiency 3 - Timeliness 4 - Average; *In the event that there is no strategic output, the percentage distribution is as follows: Core output- 50% and Support output-20%
                    </td>
                </tr>
            </table>

            <!-- PMT Stamp -->
            <div class="pmt-stamp">
                <img src="{{ asset('img/img_pmt.jpg') }}" alt="PMT Stamp">
            </div>
        </div>
    </div>

    <script>
        // Auto-focus for printing
        window.onload = function() {
            // Optional: Auto-print on load (uncomment if desired)
            // window.print();
        };
    </script>
</body>
</html>
