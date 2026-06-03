<?php

namespace Database\Seeders\Widgets;

use App\Models\Widget;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schemeData = [
            'headers' => [
                'items' => 'string',
            ],
            'rows' => [
                'items' => [
                    'col' => 'string|int|float',
                ],
            ],
        ];

        Widget::query()->updateOrCreate(
            ['name' => 'table'],
            [
                'name' => 'table',
                'description' => 'Таблица с заголовками и строками.',
                'scheme' => json_encode($schemeData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]
        );
    }

}
