<?php

namespace App\Jobs;

use App\Models\Item;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessFile implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
                throw new Exception('File not found or not readable: ' . $this->filePath);
            }

            $chunkSize = 1000;
            $file = fopen($this->filePath, 'r');
            if (!$file) {
                throw new Exception('Failed to open file: ' . $this->filePath);
            }

            $header = fgetcsv($file);
            if ($header === false) {
                throw new Exception('Failed to read header from file: ' . $this->filePath);
            }

            DB::transaction(function () use ($file, $header, $chunkSize) {
                DB::table('items')->truncate();

                $data = [];
                while (($row = fgetcsv($file)) !== false) {
                    $rowData = array_combine($header, $row);
                    $data[] = [
                        'mfg_name' => $rowData['MFGName'],
                        'mfg_item_number' => $rowData['MFG Item Number'],
                        'item_number' => $rowData['Item Number'],
                        'available' => (int)$rowData['Available'],
                        'ltl' => filter_var($rowData['LTL'], FILTER_VALIDATE_BOOLEAN),
                        'mfg_qty_available' => isset($rowData['MFG Qty Available']) ? (int)$rowData['MFG Qty Available'] : null,
                        'stocking' => $rowData['Stocking'],
                        'special_order' => $rowData['Special Order'],
                        'oversize' => $rowData['Oversize'],
                        'addtl_handling_charge' => $rowData['Addtl Handling Charge'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($data) >= $chunkSize) {
                        DB::table('items')->insert($data);
                        $data = [];
                    }
                }

                if (count($data) > 0) {
                    DB::table('items')->insert($data);
                }

                fclose($file);
            });

            Log::info('File processed successfully.');
        } catch (Exception $e) {
            Log::error('File processing failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
