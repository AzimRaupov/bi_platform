<?php

namespace Database\Seeders\Widgets;

use App\Models\Widget;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MiniCountersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schemeData = [
            'counters'=>[
                [
                    'name'=>'string',
                    'value'=>'number',
                    'prefix'=>'string|nullable',
                    'suffix'=>'string|nullable',
                ]
            ]
        ];

        Widget::query()->updateOrCreate(
            ['name' => 'mini-counters'],
            [
                'name' => 'mini-counters',
                'description' => 'Предназначен для отображения набора мини-счётчиков (статистики), где для каждого элемента задаются название, значение, префикс и суффикс (необязательно).',
                'scheme' => json_encode($schemeData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]
        );
    }
}
