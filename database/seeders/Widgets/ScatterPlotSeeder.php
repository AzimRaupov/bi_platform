<?php

namespace Database\Seeders\Widgets;

use App\Models\Widget;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScatterPlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schemeData = [
            'series'=> [
              [
                  'name'=> "string",
                  'data'=> [11,20],
              ]
            ],
            'categories'=>["string|month|data|category", ""],

        ];

        Widget::query()->updateOrCreate(
            ['name' => 'scatter-plot'],
            [
                'name' => 'scatter-plot',
                'description' => 'Диаграмма рассеяния.Отображает значения в виде точек на графике.',
                'scheme' => json_encode($schemeData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]
        );
    }
}
