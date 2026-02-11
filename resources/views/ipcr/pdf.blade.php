<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IPCR Form</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15px;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 11px;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        .header-table td {
            border: none;
        }

        .section-title {
            font-weight: bold;
        }

        .no-border {
            border: none !important;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bg-average {
            background-color: rgb(221, 217, 196);
        }

    </style>
</head>
<body>

@php
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

    $coreAvg = $calculateAverage($ipcr->coreFunctions);
    $supportAvg = $calculateAverage($ipcr->supportFunctions);
    $strategicAvg = $calculateAverage($ipcr->strategicFunctions);

    $weightedCore = $coreAvg * 0.5;
    $weightedSupport = $supportAvg * 0.1;
    $weightedStrategic = $strategicAvg * 0.4;
    $finalScore = $weightedCore + $weightedSupport + $weightedStrategic;
@endphp

<table style="width: 100%; border-collapse: collapse">
    <tr>
        <td rowspan="3" style="border-right: none;border-bottom: none">
            <small>DOH-SPMS Form 4</small>
        </td>
        <td style="width: 50%;border-left: none;border-bottom: none" class="center" rowspan="3">
            <img style="margin-bottom: -15px" src="{{ public_path('img/img_csmc.jpg') }}" alt="Left Logo" height="45"
                 width="45">
            <span class="bold">INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)</span>
            <img style="margin-bottom: -20px" src="{{ public_path('img/img_doh.png') }}" alt="Right Logo" height="45"
                 width="45">
        </td>
        <td style=" border:1px solid #000;">Document Code:</td>
        <td style="border:1px solid #000;">HOPSS-HRMS-FM-01</td>
    </tr>
    <tr>
        <td style="border:1px solid #000;">Revision No.:</td>
        <td style="border:1px solid #000;">Rev. 1</td>
    </tr>
    <tr>
        <td style="border:1px solid #000;">Effectivity:</td>
        <td style="border:1px solid #000;">05 Dec 2022</td>
    </tr>
</table>
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td colspan="8" class="small" style="border-top:none;border-bottom:none;padding-top:15px;">
            I, <u>{{ $ipcr->user->name ?? 'N/A' }}</u>,
            {{ $ipcr->user->designation ?? '' }} of the {{ $ipcr->user->section->name ?? '' }} of the Cebu South Medical Center, commit to deliver and agree to be rated on the
            attainment of the following targets in accordance with the indicated measures for the period {{ \Carbon\Carbon::parse($ipcr->period_from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($ipcr->period_to)->format('M d, Y') }}.
        </td>
    </tr>
    <tr>
        <td colspan="1" style="border-top:none;border-right: none;"></td>
        <td colspan="4" style="border-top:none;border-right: none;border-left: none;" class="text-right">
            <div style="padding-top: 10px;">
                Name of Employee: <span style="margin-left: 15px"><strong>{{ $ipcr->user->name ?? 'N/A' }}</strong></span>
            </div>
        </td>
        <td colspan="3" style="border-top:none;border-left: none">
            <div style="padding-top: 10px;" class="center">
                Date: <span style="margin-left: 15px"><strong>{{ \Carbon\Carbon::parse($ipcr->ipcr_date)->format('m/d/Y') }}</strong></span>
            </div>
        </td>
    </tr>
</table>
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td colspan="2" style="border-bottom: none;">
            <strong>Approved By:</strong>
        </td>
        <td style="border-left: none;width: 22%">
            Date: <span style="margin-left: 15px"><strong>{{ $ipcr->date_done ? \Carbon\Carbon::parse($ipcr->date_done)->format('m/d/Y') : '' }}</strong></span>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border-top: none;" class="center">
            <strong>{{ $ipcr->supervisor->name ?? 'N/A' }}</strong>
        </td>
        <td rowspan="2"></td>
    </tr>
    <tr>
        <td colspan="2" class="center">
            <strong>Name of Supervisor</strong>
        </td>
    </tr>
</table>
<table>
    <!-- Header Row -->
    <tr class="center bold">
        <td style="width:18%;">Output</td>
        <td style="width:18%;">Success Indicator (Targets + Measure)</td>
        <td style="width:18%;">Actual Accomplishment</td>
        <td style="width:5%;">Q(1)</td>
        <td style="width:5%;">E(2)</td>
        <td style="width:5%;">T(3)</td>
        <td style="width:5%;">A(4)</td>
        <td style="width:13%;">Remarks / Justification of Unmet Targets</td>
    </tr>

    <!-- Core Functions -->
    <tr class="section-title">
        <td colspan="8">Core Functions</td>
    </tr>
    @foreach($ipcr->coreFunctions as $func)
        <tr>
            <td>{{ $func->output }}</td>
            <td>{{ $func->success_indicator }}</td>
            <td>{{ $func->actual_accomplishment }}</td>
            <td class="center">{{ $func->quantity_rating ?? '' }}</td>
            <td class="center">{{ $func->efficiency_rating ?? '' }}</td>
            <td class="center">{{ $func->timeliness_rating ?? '' }}</td>
            <td class="center">{{ $func->average_rating ?? number_format(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]) ? array_sum(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) / count(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) : 0, 2) }}</td>
            <td>{{ $func->remarks }}</td>
        </tr>
    @endforeach
    <tr class="bg-average">
        <td colspan="6" class="bold text-right">Average Rating (Core Functions)</td>
        <td class="center bold">{{ number_format($coreAvg, 2) }}</td>
        <td></td>
    </tr>
    <!-- Support Functions -->
    <tr class="section-title">
        <td colspan="8">Support Functions</td>
    </tr>
    @foreach($ipcr->supportFunctions as $func)
        <tr>
            <td>{{ $func->output }}</td>
            <td>{{ $func->success_indicator }}</td>
            <td>{{ $func->actual_accomplishment }}</td>
            <td class="center">{{ $func->quantity_rating ?? '' }}</td>
            <td class="center">{{ $func->efficiency_rating ?? '' }}</td>
            <td class="center">{{ $func->timeliness_rating ?? '' }}</td>
            <td class="center">{{ $func->average_rating ?? number_format(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]) ? array_sum(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) / count(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) : 0, 2) }}</td>
            <td>{{ $func->remarks }}</td>
        </tr>
    @endforeach
    <tr class="bg-average">
        <td colspan="6" class="bold text-right">Average Rating (Support Functions)</td>
        <td class="center bold">{{ number_format($supportAvg, 2) }}</td>
        <td></td>
    </tr>

    <!-- Strategic Functions -->
    <tr class="section-title">
        <td colspan="8">Strategic Functions</td>
    </tr>
    @foreach($ipcr->strategicFunctions as $func)
        <tr>
            <td>{{ $func->output }}</td>
            <td>{{ $func->success_indicator }}</td>
            <td>{{ $func->actual_accomplishment }}</td>
            <td class="center">{{ $func->quantity_rating ?? '' }}</td>
            <td class="center">{{ $func->efficiency_rating ?? '' }}</td>
            <td class="center">{{ $func->timeliness_rating ?? '' }}</td>
            <td class="center">{{ $func->average_rating ?? number_format(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating]) ? array_sum(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) / count(array_filter([$func->quantity_rating, $func->efficiency_rating, $func->timeliness_rating])) : 0, 2) }}</td>
            <td>{{ $func->remarks }}</td>
        </tr>
    @endforeach
    <tr class="bg-average">
        <td colspan="6" class="bold text-right">Average Rating (Strategic Functions)</td>
        <td class="center bold">{{ number_format($strategicAvg, 2) }}</td>
        <td></td>
    </tr>

    <!-- Rating Summary -->
    <tr class="section-title bg-average">
        <td colspan="8">RATING</td>
    </tr>
    <tr class="center bold">
        <td>Function</td>
        <td>Percentage Distribution</td>
        <td>Average Rating per Function</td>
        <td>Final Rating per Function<br>(Average × Percentage)</td>
        <td>Final Average Rating</td>
        <td colspan="2">Adjectival Rating</td>
        <td>Remarks</td>
    </tr>
    <tr class="center">
        <td class="text-left bold">Core Functions</td>
        <td>50%</td>
        <td>{{ number_format($coreAvg, 2) }}</td>
        <td>{{ number_format($weightedCore, 2) }}</td>
        <td rowspan="3" style="vertical-align: middle" class="bold">{{ number_format($finalScore, 2) }}</td>
        <td colspan="2" rowspan="3" style="vertical-align: middle" class="bold">{{ $ipcr->final_rating_adjective }}</td>
        <td rowspan="3"></td>
    </tr>
    <tr class="center">
        <td class="text-left bold">Support Functions</td>
        <td>10%</td>
        <td>{{ number_format($supportAvg, 2) }}</td>
        <td>{{ number_format($weightedSupport, 2) }}</td>
    </tr>
    <tr class="center">
        <td class="text-left bold">Strategic Functions</td>
        <td>40%</td>
        <td>{{ number_format($strategicAvg, 2) }}</td>
        <td>{{ number_format($weightedStrategic, 2) }}</td>
    </tr>

    <!-- Comments -->
    <tr>
        <td colspan="8" class="bold">Comments and Recommendations for Development Purposes:</td>
    </tr>
    <tr style="height: 40px">
        <td colspan="8">{{ $ipcr->comments }}</td>
    </tr>
    <!-- Signatures -->
    <tr class="center bold">
        <td>Discussed With:</td>
        <td>Assessed By:</td>
        <td>Date:</td>
        <td colspan="4">Final Rating By:</td>
        <td>Date:</td>
    </tr>
    <tr class="center">
        <td style="padding-top: 30px">
            <u>{{ $ipcr->user->name ?? '' }}</u><br>
            Employee
        </td>
        <td style="padding-top: 30px">
            <u>{{ $ipcr->supervisor->name ?? '' }}</u><br>
            Supervisor
        </td>
        <td></td>
        <td colspan="4" style="padding-top: 30px">
            <u>AGUSTIN D. AGOS, JR., MD, FPSGS, FPCS, DODT, PhD OD, RODC</u><br>
            Next Higher Supervisor
        </td>
        <td></td>
    </tr>

    <tr>
        <td colspan="8" class="small">
            <i><b>Legend:</b> 1- Quality 2 -Efficiency 3 - Timeliness 4 - Average; *In the event that there is no
                strategic output, the percentage distribution is as follows: Core output- 80% and Support output-20%</i>
        </td>
    </tr>
</table>

</body>
</html>
