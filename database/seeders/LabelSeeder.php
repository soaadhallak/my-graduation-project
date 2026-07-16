<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            // Type
            ['name' => 'bug', 'color' => '#EF4444'],
            ['name' => 'feature', 'color' => '#3B82F6'],
            ['name' => 'enhancement', 'color' => '#8B5CF6'],
            ['name' => 'documentation', 'color' => '#06B6D4'],
            ['name' => 'question', 'color' => '#A855F7'],
            ['name' => 'refactor', 'color' => '#6366F1'],
            ['name' => 'test', 'color' => '#14B8A6'],
            ['name' => 'chore', 'color' => '#6B7280'],

            // Severity / Priority helpers
            ['name' => 'critical', 'color' => '#DC2626'],
            ['name' => 'blocker', 'color' => '#991B1B'],
            ['name' => 'major', 'color' => '#F97316'],
            ['name' => 'minor', 'color' => '#F59E0B'],
            ['name' => 'trivial', 'color' => '#84CC16'],

            // Area
            ['name' => 'frontend', 'color' => '#0EA5E9'],
            ['name' => 'backend', 'color' => '#2563EB'],
            ['name' => 'api', 'color' => '#4F46E5'],
            ['name' => 'database', 'color' => '#7C3AED'],
            ['name' => 'ui/ux', 'color' => '#EC4899'],
            ['name' => 'mobile', 'color' => '#22C55E'],
            ['name' => 'security', 'color' => '#B91C1C'],
            ['name' => 'performance', 'color' => '#EA580C'],
            ['name' => 'accessibility', 'color' => '#0891B2'],

            // Workflow / Status helpers
            ['name' => 'needs triage', 'color' => '#64748B'],
            ['name' => 'needs review', 'color' => '#F59E0B'],
            ['name' => 'needs info', 'color' => '#D97706'],
            ['name' => 'duplicate', 'color' => '#9CA3AF'],
            ['name' => 'wontfix', 'color' => '#6B7280'],
            ['name' => 'invalid', 'color' => '#9CA3AF'],
            ['name' => 'good first issue', 'color' => '#10B981'],
            ['name' => 'help wanted', 'color' => '#059669'],
        ];

        foreach ($labels as $label) {
            Label::updateOrCreate(
                ['name' => $label['name']],
                ['color' => $label['color']]
            );
        }
    }
}
