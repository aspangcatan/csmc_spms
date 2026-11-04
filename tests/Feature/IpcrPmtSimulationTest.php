<?php

namespace Tests\Feature;

use App\Models\Ipcr;
use App\Models\User;
use App\Services\IpcrService;
use App\Services\PmtApprovalService;
use App\Support\Period;
use Tests\TestCase;

/**
 * Walks a real IPCR of employee #1116 through the whole workflow, from draft to PMT Approved,
 * signing in as each approver in turn. Runs against the configured databases and KEEPS the
 * records so they can be reviewed in the app afterwards (IPCR page, print view with PMT signatures).
 *
 * A period that already has an IPCR for #1116 is skipped. Use SIM_YEAR to simulate another year:
 *   SIM_YEAR=2027 php artisan test --filter=IpcrPmtSimulationTest
 */
class IpcrPmtSimulationTest extends TestCase
{
    const EMPLOYEE_ID = 1116;

    public function periodProvider(): array
    {
        return [
            '1st Semester' => [1],
            '2nd Semester' => [2],
            '1st Quarter (Jan-Mar)' => [3],
        ];
    }

    /** @dataProvider periodProvider */
    public function test_ipcr_goes_from_draft_to_pmt_approved(int $period)
    {
        $year = (int) (getenv('SIM_YEAR') ?: date('Y'));
        $employee = User::findOrFail(self::EMPLOYEE_ID);
        $label = "{$year} " . Period::label($period);

        $existing = Ipcr::where('userid', $employee->id)->where('year', $year)->where('semester', $period)->first();
        if ($existing) {
            $this->markTestSkipped("IPCR #{$existing->id} already exists for {$label}. Set SIM_YEAR to simulate another year.");
        }

        $signatories = app(IpcrService::class)->getAutomatedSignatories($employee);
        $supervisor = User::findOrFail($signatories['supervisor_id']);
        $divisionHead = User::findOrFail($signatories['division_head']);
        $pmtMembers = User::whereIn('id', app(PmtApprovalService::class)->memberIds())->get();
        $this->assertNotEmpty($pmtMembers, 'No PMT members are configured.');
        [$periodFrom, $periodTo] = Period::range($year, $period);

        // 1. Employee saves an unfinished draft (only one core row, no support/strategic rows yet).
        $response = $this->actingAs($employee)->postJson('/api/ipcr', [
            'draft' => true,
            'ipcr' => $this->header($employee, $year, $period, $periodFrom, $periodTo, 'Draft Target'),
            'core_functions' => [['output' => 'Information systems maintained', 'success_indicator' => '']],
            'support_functions' => [],
            'strategic_functions' => [],
        ])->assertCreated();
        $ipcrId = $response->json('id');
        $this->assertStatus($ipcrId, 'Draft Target');

        // 2. Employee completes the targets and submits them.
        $this->actingAs($employee)->putJson("/api/ipcr/{$ipcrId}", [
            'ipcr' => $this->header($employee, $year, $period, $periodFrom, $periodTo, 'Draft Target'),
        ] + $this->targetRows($this->rowIds($ipcrId)))->assertOk();
        $this->actingAs($employee)->postJson("/api/ipcr/{$ipcrId}/submit")->assertOk();
        $this->assertStatus($ipcrId, 'Target Submitted');

        // 3. Supervisor approves the targets.
        $this->actingAs($supervisor)->postJson("/api/ipcr/{$ipcrId}/approve")->assertOk();
        $this->assertStatus($ipcrId, 'Target Approved');

        // 4. Employee saves accomplishments as a draft, then submits them with ratings.
        $this->actingAs($employee)->putJson("/api/ipcr/{$ipcrId}", [
            'draft' => true,
            'ipcr' => $this->header($employee, $year, $period, $periodFrom, $periodTo, 'Draft Accomplishment'),
        ] + $this->accomplishmentRows($this->rowIds($ipcrId), false))->assertOk();
        $this->assertStatus($ipcrId, 'Draft Accomplishment');

        $this->actingAs($employee)->putJson("/api/ipcr/{$ipcrId}", [
            'ipcr' => $this->header($employee, $year, $period, $periodFrom, $periodTo, 'Draft Accomplishment') + ['date_done' => $periodTo],
        ] + $this->accomplishmentRows($this->rowIds($ipcrId), true))->assertOk();
        $this->actingAs($employee)->postJson("/api/ipcr/{$ipcrId}/submit")->assertOk();
        $this->assertStatus($ipcrId, 'Accomplishment Submitted');

        // 5. Supervisor approves the accomplishments with feedback.
        $this->actingAs($supervisor)->postJson("/api/ipcr/{$ipcrId}/approve", [
            'comments' => "Simulation: targets met for {$label}. Keep up the good work.",
        ])->assertOk();
        $this->assertStatus($ipcrId, 'Supervisor Approved');

        // 6. Division Head approves from the Division Approvals queue.
        $this->actingAs($divisionHead)->postJson('/division-head/decide', [
            'type' => 'ipcr', 'decision' => 'approve', 'ids' => [$ipcrId], 'remarks' => 'Simulation: approved by Division Head.',
        ])->assertOk()->assertJson(['done' => [$ipcrId], 'failed' => []]);
        $this->assertStatus($ipcrId, 'Division Head Approved');

        // 7. PMT: approving without a signature is refused, then every member signs and approves.
        $this->actingAs($pmtMembers->first())->postJson('/pmt/decide', [
            'type' => 'ipcr', 'decision' => 'approve', 'ids' => [$ipcrId],
        ])->assertStatus(422);

        foreach ($pmtMembers->values() as $index => $member) {
            $this->assertStatus($ipcrId, 'Division Head Approved');
            $this->actingAs($member)->postJson('/pmt/decide', [
                'type' => 'ipcr', 'decision' => 'approve', 'ids' => [$ipcrId],
                'remarks' => 'Simulation: PMT approval.', 'signature' => $this->signatureFor($member),
            ])->assertOk()->assertJson(['done' => [$ipcrId], 'failed' => []]);

            if ($index < $pmtMembers->count() - 1) {
                $this->assertDatabaseHas('ipcr_logs', ['ipcr_id' => $ipcrId, 'subject' => 'PMT Approval (' . ($index + 1) . '/' . $pmtMembers->count() . ')']);
            }
        }

        // 8. Final state, computed rating and the signed print form.
        $ipcr = Ipcr::findOrFail($ipcrId);
        $this->assertSame('PMT Approved', $ipcr->status);
        $this->assertSame([$periodFrom, $periodTo], [$ipcr->period_from, $ipcr->period_to]);
        // Core (4.00 + 5.00) / 2 x 50% + Support 4.00 x 10% + Strategic 5.00 x 40% = 4.65
        $this->assertEqualsWithDelta(4.65, (float) $ipcr->final_rating, 0.001);
        $this->assertSame('Very Satisfactory', $ipcr->final_rating_adjective);

        $print = $this->actingAs($employee)->get("/ipcr/print/{$ipcrId}")->assertOk();
        $print->assertSee('CSMC PMT');
        $this->assertSame($pmtMembers->count(), substr_count($print->getContent(), 'alt="Signature of '));

        fwrite(STDERR, sprintf(
            "\n  IPCR #%d (%s) -> %s, final rating %s %s. Signed by %s. Print: %s\n",
            $ipcrId, $label, $ipcr->status, $ipcr->final_rating, $ipcr->final_rating_adjective,
            $pmtMembers->pluck('name')->join(', '), url("/ipcr/print/{$ipcrId}")
        ));
    }

    protected function header(User $employee, int $year, int $period, string $from, string $to, string $status): array
    {
        return [
            'userid' => $employee->id,
            'year' => $year,
            'semester' => $period,
            'period_from' => $from,
            'period_to' => $to,
            'ipcr_date' => $from,
            'status' => $status,
        ];
    }

    // Saved row ids per function group, so updates edit the rows instead of replacing them.
    protected function rowIds(int $ipcrId): array
    {
        $ipcr = Ipcr::with(['coreFunctions', 'supportFunctions', 'strategicFunctions'])->findOrFail($ipcrId);

        return [
            'core_functions' => $ipcr->coreFunctions->pluck('id')->all(),
            'support_functions' => $ipcr->supportFunctions->pluck('id')->all(),
            'strategic_functions' => $ipcr->strategicFunctions->pluck('id')->all(),
        ];
    }

    protected function targets(): array
    {
        return [
            'core_functions' => [
                ['output' => 'Information systems maintained', 'success_indicator' => '100% of reported system issues resolved within 3 working days'],
                ['output' => 'Hospital network uptime', 'success_indicator' => '99% network availability every month'],
            ],
            'support_functions' => [
                ['output' => 'Section reports', 'success_indicator' => 'Monthly accomplishment report submitted on or before the 5th'],
            ],
            'strategic_functions' => [
                ['output' => 'Process automation', 'success_indicator' => '1 manual process digitized within the period'],
            ],
        ];
    }

    protected function targetRows(array $ids): array
    {
        $rows = [];
        foreach ($this->targets() as $group => $targets) {
            foreach ($targets as $i => $target) {
                $rows[$group][] = ['id' => $ids[$group][$i] ?? null] + $target;
            }
        }
        return $rows;
    }

    // Ratings are chosen so the row averages are exact: core 4.00 and 5.00, support 4.00, strategic 5.00.
    protected function accomplishmentRows(array $ids, bool $withRatings): array
    {
        $results = [
            'core_functions' => [
                ['All 48 reported issues resolved; 41 within 3 days', [5, 4, 3]],
                ['99.6% average network availability', [5, 5, 5]],
            ],
            'support_functions' => [
                ['All monthly reports submitted on time', [4, 4, 4]],
            ],
            'strategic_functions' => [
                ['Leave application process digitized', [5, 5, 5]],
            ],
        ];

        $rows = $this->targetRows($ids);
        foreach ($results as $group => $groupResults) {
            foreach ($groupResults as $i => [$accomplishment, $ratings]) {
                $rows[$group][$i]['actual_accomplishment'] = $accomplishment;
                if ($withRatings) {
                    [$q, $e, $t] = $ratings;
                    $rows[$group][$i] += ['quantity_rating' => $q, 'efficiency_rating' => $e, 'timeliness_rating' => $t];
                }
            }
        }
        return $rows;
    }

    protected function assertStatus(int $ipcrId, string $expected): void
    {
        $this->assertSame($expected, Ipcr::findOrFail($ipcrId)->status, "IPCR #{$ipcrId} should be '{$expected}'.");
    }

    // A hand-drawn-looking stroke, unique per member, as the PNG data URL the signature pad sends.
    protected function signatureFor(User $member): string
    {
        $img = imagecreatetruecolor(300, 100);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $ink = imagecolorallocate($img, 17, 24, 39);
        imagesetthickness($img, 3);

        mt_srand($member->id);
        [$prevX, $prevY] = [15, 55];
        for ($x = 21; $x <= 285; $x += 6) {
            $y = 55 + (int) (25 * sin($x / (10 + $member->id % 7))) + mt_rand(-6, 6);
            imageline($img, $prevX, $prevY, $x, $y, $ink);
            [$prevX, $prevY] = [$x, $y];
        }
        imageline($img, 30, 88, 270, 84, $ink);

        ob_start();
        imagepng($img);
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }
}
