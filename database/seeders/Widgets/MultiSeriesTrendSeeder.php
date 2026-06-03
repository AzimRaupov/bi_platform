<?php

namespace Database\Seeders\Widgets;

use App\Models\Widget;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MultiSeriesTrendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schemeData = [
            'series' => [
                [
                    'name' => 'string',
                    'data' => [
                        4164, 4652, 4817,
                    ],
                ],
            ],
            'labels' => [
                'date|string',
            ],
        ];

        Widget::query()->updateOrCreate(
            ['name' => 'multi-series-trend'],
            [
                'name' => 'multi-series-trend',
                'description' => 'Интерактивный линейный график для сравнения нескольких показателей в разрезе времени.',
                'scheme' => json_encode($schemeData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]
        );
    }
}
