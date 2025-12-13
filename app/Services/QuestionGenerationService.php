<?php

namespace App\Services;

class QuestionGenerationService
{
    protected string $pythonPath;
    protected string $scriptPath;

    public function __construct()
    {
        $this->pythonPath = 'py'; // مهم جداً على Windows
        $this->scriptPath = base_path('ai-scripts/generate_questions.py');
    }

    public function generateQuestions(string $text): array
    {
        if (! file_exists($this->scriptPath)) {
            return [
                'error' => 'Python script not found',
                'script_path' => $this->scriptPath,
            ];
        }

        $escapedText = escapeshellarg($text);

        $command = sprintf(
            '%s %s %s',
            $this->pythonPath,
            escapeshellarg($this->scriptPath),
            $escapedText
        );

        $output = shell_exec($command);

        if ($output === null) {
            return [
                'error' => 'No output from Python script',
            ];
        }

        // إصلاح الترميز لضمان دعم UTF-8
        $output = mb_convert_encoding(trim($output), 'UTF-8', 'auto');

        try {
            return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'error' => 'Failed to decode JSON from Python script',
                'raw_output' => $output,
                'exception' => $e->getMessage(),
            ];
        }
    }
}
