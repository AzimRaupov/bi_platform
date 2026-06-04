<?php

namespace App\Helpers;

class PythonRunner
{
    public string $command;

    public function __construct($pathPython, $pathData)
    {
        $scriptDirectory = dirname($pathPython);
        $pythonBinary = base_path('venv/bin/python');

        if (! file_exists($pythonBinary)) {
            $pythonBinary = 'python3';
        }

        $this->command = sprintf(
            'cd %s && %s %s --path=%s',
            escapeshellarg($scriptDirectory),
            escapeshellarg($pythonBinary),
            escapeshellarg($pathPython),
            escapeshellarg($pathData)
        );
    }

    public function run(): array
    {
        $output = [];
        $exitCode = 0;

        exec($this->command.' 2>&1', $output, $exitCode);

        return [
            'command' => $this->command,
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }
}
