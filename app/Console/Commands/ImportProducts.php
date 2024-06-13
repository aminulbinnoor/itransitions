<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Facades\Validator;

class ImportProducts extends Command
{
    protected $signature = 'import:products {file? : The path to the CSV file} {--test}';
    protected $description = 'Import products from a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = $this->argument('file') ?? storage_path('app/stock.csv');
        $isTestMode = $this->option('test');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error('File not found or not readable.');
            return 1;
        }

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0); // Set the CSV header offset
        } catch (\Exception $e) {
            $this->error('Error reading CSV file: ' . $e->getMessage());
            return 1;
        }

        $stmt = (new Statement());
        $records = $stmt->process($csv);

        $processed = $success = $skipped = 0;
        $failedRecords = [];

        foreach ($records as $record) {
            $processed++;

            $validator = Validator::make($record, [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                $skipped++;
                Log::warning('Validation failed for record', ['record' => $record, 'errors' => $validator->errors()]);
                continue;
            }

            if ($this->shouldSkip($record)) {
                $skipped++;
                Log::info('Record skipped based on business rules', ['record' => $record]);
                continue;
            }

            $record['discontinued_date'] = $record['discontinued'] ? Carbon::now() : null;

            try {
                if (!$isTestMode) {
                    DB::table('products')->insert([
                        'name' => $record['name'],
                        'price' => $record['price'],
                        'stock' => $record['stock'],
                        'discontinued' => $record['discontinued'],
                        'discontinued_date' => $record['discontinued_date'],
                    ]);
                }
                $success++;
            } catch (\Exception $e) {
                $failedRecords[] = $record;
                Log::error('Failed to insert product', ['product' => $record, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Processed: $processed");
        $this->info("Successful: $success");
        $this->info("Skipped: $skipped");

        if (!empty($failedRecords)) {
            $this->error('Failed to insert the following records:');
            foreach ($failedRecords as $record) {
                $this->line(json_encode($record));
            }
        }

        return 0;
    }

    private function shouldSkip($record)
    {
        return ($record['price'] < 5 && $record['stock'] < 10) || $record['price'] > 1000;
    }
}