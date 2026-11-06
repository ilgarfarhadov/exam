<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateQuestions extends Command
{
    protected $signature = 'app:translate-questions';

    protected $description = 'Apply bilingual English translations to questions and answers in Bank 6 and Bank 7.';

    public function handle(): int
    {
        $this->info('Starting bilingual translation update...');

        $dataPath = base_path('database/data/bilingual_updates.json');

        if (!file_exists($dataPath)) {
            $this->error("Translation data file not found at: {$dataPath}");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($dataPath), true);

        if (!$data || !isset($data['questions'], $data['answers'])) {
            $this->error('Failed to parse translation JSON data.');
            return Command::FAILURE;
        }

        $questionUpdates = $data['questions'];
        $answerUpdates   = $data['answers'];

        DB::transaction(function () use ($questionUpdates, $answerUpdates) {
            $this->info('Updating ' . count($questionUpdates) . ' questions...');
            foreach ($questionUpdates as $q) {
                DB::table('questions')
                    ->where('id', $q['id'])
                    ->update(['question_text' => $q['text']]);
            }

            $this->info('Updating ' . count($answerUpdates) . ' answers...');
            foreach ($answerUpdates as $a) {
                DB::table('answers')
                    ->where('id', $a['id'])
                    ->update(['answer_text' => $a['text']]);
            }
        });

        $this->info('Bilingual translations successfully applied!');

        return Command::SUCCESS;
    }
}
