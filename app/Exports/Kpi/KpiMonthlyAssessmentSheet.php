<?php

namespace App\Exports\Kpi;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * One Excel sheet per KPI assessment template (monthly view), matching the
 * on-screen table:
 *
 *   Name / Assessment Date / Assessment Category   (info block)
 *   NO | KEY PERFORMANCE INDICATOR | WEIGHT | TARGET | Average(SCORE,%) | <assessor>(SCORE,%) ...
 *   <group rows + indicator rows>
 *   TOTAL row
 *
 * The "Average" column and its values are already produced by
 * EmployeeAssessmentController@buildAssessmentData (as the first scorer and the
 * first score/pct pair of every row), so the sheet just lays the data out.
 */
class KpiMonthlyAssessmentSheet implements FromArray, WithTitle
{
    protected $template;

    public function __construct(array $template)
    {
        $this->template = $template;
    }

    public function title(): string
    {
        $name = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', (string) $this->template['template']);
        return mb_substr($name ?: 'Assessment', 0, 31) ?: 'Assessment';
    }

    private function scorerName($scorer): string
    {
        if (is_array($scorer)) {
            $full = trim(($scorer['first_name'] ?? '').' '.($scorer['last_name'] ?? ''));
            return $scorer['full_name'] ?? ($full ?: ($scorer['name'] ?? ''));
        }
        if (is_object($scorer)) {
            return $scorer->full_name ?? (trim(($scorer->first_name ?? '').' '.($scorer->last_name ?? '')) ?: ($scorer->name ?? ''));
        }
        return (string) $scorer;
    }

    private function employeeName(): string
    {
        $e = $this->template['employee'] ?? null;
        if (is_array($e)) {
            return $e['name'] ?? '';
        }
        if (is_object($e)) {
            return $e->name ?? '';
        }
        return '';
    }

    public function array(): array
    {
        $scorers = $this->template['scorer'] ?? [];   // first entry is "Average"
        $rows = [];

        // --- info block ---
        $date = $this->template['date'] ?? '';
        $rows[] = ['Name', $this->employeeName()];
        $rows[] = ['Assessment Date', $date ? date('M Y', strtotime((string) $date)) : ''];
        $rows[] = ['Assessment Category', (string) ($this->template['template'] ?? '')];
        $rows[] = [];

        // --- table header (2 rows) ---
        $head1 = ['NO', 'KEY PERFORMANCE INDICATOR', 'WEIGHT', 'TARGET'];
        $head2 = ['', '', '', ''];
        foreach ($scorers as $scorer) {
            $head1[] = $this->scorerName($scorer);
            $head1[] = '';
            $head2[] = 'SCORE';
            $head2[] = 'SCORE PERCENTAGE';
        }
        $rows[] = $head1;
        $rows[] = $head2;

        // --- body: group summary row + indicator rows ---
        $no = 1;
        foreach ($this->template['data'] as $group) {
            $rows[] = array_merge([''], $group['data']);
            foreach ($group['indicator'] as $indicator) {
                $rows[] = array_merge([$no++], $indicator);
            }
        }

        // --- total row ---
        $total = $this->template['total'];
        $total[0] = 'TOTAL';
        $rows[] = array_merge([''], $total);

        return $rows;
    }
}
