<?php

namespace Tests\Feature;

use App\Models\Spcr;
use App\Models\User;
use App\Services\PmtApprovalService;
use App\Services\SpcrService;
use App\Support\Period;
use Tests\TestCase;

/**
 * Walks a real SPCR of section head #1 (IMISS) through the whole workflow, from draft to PMT Approved,
 * signing in as each approver in turn. Runs against the configured databases and KEEPS the
 * records so they can be reviewed in the app afterwards (SPCR page, print view with PMT signatures).
 *
 * A period that already has an SPCR for #1 is skipped. Use SIM_YEAR to simulate another year:
 *   SIM_YEAR=2027 php artisan test --filter=SpcrPmtSimulationTest
 */
class SpcrPmtSimulationTest extends TestCase
{
    use DrawsPmtSignatures;

    const SECTION_HEAD_ID = 1;

    public function periodProvider(): array
    {
        return [
            '1st Semester' => [1],
            '2nd Semester' => [2],
            '3 Month Period' => [3],
        ];
    }

    /** @dataProvider periodProvider */
    public function test_spcr_goes_from_draft_to_pmt_approved(int $period)
    {
        $year = (int) (getenv('SIM_YEAR') ?: date('Y'));
        $owner = User::findOrFail(self::SECTION_HEAD_ID);
        $this->assertTrue($owner->headsAnySection(), "User #{$owner->id} must head a section to own an SPCR.");
        $label = "{$year} " . Period::label($period);

        $existing = Spcr::where('userid', $owner->id)->where('year', $year)->where('semester', $period)->first();
        if ($existing) {
            $this->markTestSkipped("SPCR #{$existing->id} already exists for {$label}. Set SIM_YEAR to simulate another year.");
        }

        $signatories = app(SpcrService::class)->getAutomatedSignatories($owner);
        $supervisor = User::findOrFail($signatories['supervisor_id']);
        $divisionHead = User::findOrFail($signatories['division_head_id']);
        $pmtMembers = User::whereIn('id', app(PmtApprovalService::class)->memberIds())->get();
        $this->assertNotEmpty($pmtMembers, 'No PMT members are configured.');
        // The 3 month period takes whatever range the section head picks, not a fixed quarter.
        [$periodFrom, $periodTo] = Period::isCustom($period)
            ? ["{$year}-02-15", "{$year}-05-14"]
            : Period::range($year, $period);
        $header = ['year' => $year, 'semester' => $period, 'spcr_date' => $periodFrom]
            + (Period::isCustom($period) ? ['period_from' => $periodFrom, 'period_to' => $periodTo] : []);

        // 1. The 3 month period cannot be created without its date range.
        if (Period::isCustom($period)) {
            $this->actingAs($owner)->postJson('/api/spcr', ['year' => $year, 'semester' => $period, 'status' => 'Draft Target'])
                ->assertStatus(422)->assertJsonValidationErrors(['period_from', 'period_to']);
        }

        // 2. Section head saves an unfinished draft (only one core row, no support/strategic rows yet).
        $response = $this->actingAs($owner)->postJson('/api/spcr', $header + [
            'status' => 'Draft Target',
            'core_entries' => [['output' => 'Information systems maintained', 'success_indicator' => '']],
            'support_entries' => [],
            'strategic_entries' => [],
        ])->assertCreated();
        $spcrId = $response->json('id');
        $this->assertStatus($spcrId, 'Draft Target');

        // 3. Section head completes the targets and submits them.
        $this->actingAs($owner)->putJson("/api/spcr/{$spcrId}", $header + ['status' => 'Draft Target']
            + $this->targetRows($this->rowIds($spcrId)))->assertOk();
        $this->actingAs($owner)->postJson("/api/spcr/{$spcrId}/submit")->assertOk();
        $this->assertStatus($spcrId, 'Target Submitted');

        // 4. Supervisor approves the targets.
        $this->actingAs($supervisor)->postJson("/api/spcr/{$spcrId}/approve")->assertOk();
        $this->assertStatus($spcrId, 'Target Approved');

        // 5. Section head saves accomplishments as a draft, then submits them with ratings.
        $this->actingAs($owner)->putJson("/api/spcr/{$spcrId}", $header + ['status' => 'Draft Accomplishment']
            + $this->accomplishmentRows($this->rowIds($spcrId), false))->assertOk();
        $this->assertStatus($spcrId, 'Draft Accomplishment');

        $this->actingAs($owner)->putJson("/api/spcr/{$spcrId}", $header + ['status' => 'Draft Accomplishment', 'date_done' => $periodTo]
            + $this->accomplishmentRows($this->rowIds($spcrId), true))->assertOk();
        $this->actingAs($owner)->postJson("/api/spcr/{$spcrId}/submit")->assertOk();
        $this->assertStatus($spcrId, 'Accomplishment Submitted');

        // 6. Supervisor approves the accomplishments.
        $this->actingAs($supervisor)->postJson("/api/spcr/{$spcrId}/approve")->assertOk();
        $this->assertStatus($spcrId, 'Supervisor Approved');

        // 7. Division Head approves from the Division Approvals queue.
        $this->actingAs($divisionHead)->postJson('/division-head/decide', [
            'type' => 'spcr', 'decision' => 'approve', 'ids' => [$spcrId], 'remarks' => 'Simulation: approved by Division Head.',
        ])->assertOk()->assertJson(['done' => [$spcrId], 'failed' => []]);
        $this->assertStatus($spcrId, 'Division Head Approved');

        // 8. PMT: approving without a signature is refused, then every member signs and approves.
        $this->actingAs($pmtMembers->first())->postJson('/pmt/decide', [
            'type' => 'spcr', 'decision' => 'approve', 'ids' => [$spcrId],
        ])->assertStatus(422);

        foreach ($pmtMembers->values() as $index => $member) {
            $this->assertStatus($spcrId, 'Division Head Approved');
            $this->actingAs($member)->postJson('/pmt/decide', [
                'type' => 'spcr', 'decision' => 'approve', 'ids' => [$spcrId],
                'remarks' => 'Simulation: PMT approval.', 'signature' => $this->signatureFor($member),
            ])->assertOk()->assertJson(['done' => [$spcrId], 'failed' => []]);

            if ($index < $pmtMembers->count() - 1) {
                $this->assertDatabaseHas('spcr_logs', ['spcr_id' => $spcrId, 'subject' => 'PMT Approval (' . ($index + 1) . '/' . $pmtMembers->count() . ')']);
            }
        }

        // 9. Final state, computed rating and the signed print form.
        $spcr = Spcr::findOrFail($spcrId);
        $this->assertSame('PMT Approved', $spcr->status);
        $this->assertSame([$periodFrom, $periodTo], [$spcr->period_from, $spcr->period_to]);
        // Core (4.00 + 5.00) / 2 x 50% + Support 4.00 x 10% + Strategic 5.00 x 40% = 4.65
        $this->assertEqualsWithDelta(4.65, (float) $spcr->final_rating, 0.001);
        $this->assertSame('Very Satisfactory', $spcr->final_rating_adjective);

        $print = $this->actingAs($owner)->get("/spcr/print/{$spcrId}")->assertOk();
        $print->assertSee('CSMC PMT');
        $this->assertSame($pmtMembers->count(), substr_count($print->getContent(), 'alt="Signature of '));

        fwrite(STDERR, sprintf(
            "\n  SPCR #%d (%s) -> %s, final rating %s %s. Signed by %s. Print: %s\n",
            $spcrId, $label, $spcr->status, $spcr->final_rating, $spcr->final_rating_adjective,
            $pmtMembers->pluck('name')->join(', '), url("/spcr/print/{$spcrId}")
        ));
    }

    // Saved entry ids per category, so updates edit the rows instead of replacing them.
    protected function rowIds(int $spcrId): array
    {
        $entries = Spcr::with('entries')->findOrFail($spcrId)->entries;

        return [
            'core_entries' => $entries->where('category', 'core')->pluck('id')->values()->all(),
            'support_entries' => $entries->where('category', 'support')->pluck('id')->values()->all(),
            'strategic_entries' => $entries->where('category', 'strategic')->pluck('id')->values()->all(),
        ];
    }

    protected function targets(): array
    {
        return [
            'core_entries' => [
                ['output' => 'Information systems maintained', 'success_indicator' => '100% of reported system issues resolved within 3 working days', 'accountability' => 'IMISS Section'],
                ['output' => 'Hospital network uptime', 'success_indicator' => '99% network availability every month', 'accountability' => 'IMISS Section'],
            ],
            'support_entries' => [
                ['output' => 'Section reports', 'success_indicator' => 'Monthly accomplishment report submitted on or before the 5th', 'accountability' => 'Section Head'],
            ],
            'strategic_entries' => [
                ['output' => 'Process automation', 'success_indicator' => '1 manual process digitized within the period', 'accountability' => 'IMISS Section'],
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
            'core_entries' => [
                ['All 48 reported issues resolved; 41 within 3 days', '85%', [5, 4, 3]],
                ['99.6% average network availability', '100%', [5, 5, 5]],
            ],
            'support_entries' => [
                ['All monthly reports submitted on time', '100%', [4, 4, 4]],
            ],
            'strategic_entries' => [
                ['Leave application process digitized', '100%', [5, 5, 5]],
            ],
        ];

        $rows = $this->targetRows($ids);
        foreach ($results as $group => $groupResults) {
            foreach ($groupResults as $i => [$accomplishment, $rate, $ratings]) {
                $rows[$group][$i]['actual_accomplishment'] = $accomplishment;
                $rows[$group][$i]['accomplishment_rate'] = $rate;
                if ($withRatings) {
                    [$q, $e, $t] = $ratings;
                    $rows[$group][$i] += ['quantity_rating' => $q, 'efficiency_rating' => $e, 'timeliness_rating' => $t];
                }
            }
        }
        return $rows;
    }

    protected function assertStatus(int $spcrId, string $expected): void
    {
        $this->assertSame($expected, Spcr::findOrFail($spcrId)->status, "SPCR #{$spcrId} should be '{$expected}'.");
    }
}
