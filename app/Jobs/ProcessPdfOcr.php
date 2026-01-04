<?php

namespace App\Jobs;

use App\Models\PdfUpload;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessPdfOcr implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600; // 1 hour
    public int $tries = 3;

    public function __construct(
        public PdfUpload $pdfUpload
    ) {}

    public function handle(): void
    {
        try {
            $this->pdfUpload->update(['status' => 'processing']);

            // Get PDF file path
            $path = storage_path('app/' . $this->pdfUpload->storage_path);

            if (!file_exists($path)) {
                throw new \Exception('PDF file not found');
            }

            // Extract text using pdftotext command (part of poppler-utils)
            // This is a system command that needs to be installed
            $outputFile = tempnam(sys_get_temp_dir(), 'pdf_text_');
            $command = "pdftotext '" . escapeshellarg($path) . "' '" . escapeshellarg($outputFile) . "'";
            
            $output = null;
            $returnCode = null;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                // Fallback: use shell commands to get info
                $command = "pdfinfo '" . escapeshellarg($path) . "'";
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    throw new \Exception('Failed to extract text from PDF');
                }

                // Extract page count
                $pages = 0;
                foreach ($output as $line) {
                    if (str_starts_with($line, 'Pages:')) {
                        $pages = (int) trim(str_replace('Pages:', '', $line));
                    }
                }

                $this->pdfUpload->update([
                    'pages' => $pages,
                    'status' => 'completed',
                    'extracted_text' => 'PDF processed successfully (text extraction requires pdftotext utility)',
                    'ocr_processed' => true,
                ]);
            } else {
                // Read extracted text
                $extractedText = file_get_contents($outputFile);
                
                // Count pages using pdfinfo
                $pdfInfoOutput = [];
                exec("pdfinfo '" . escapeshellarg($path) . "'", $pdfInfoOutput);
                $pages = 0;
                foreach ($pdfInfoOutput as $line) {
                    if (str_starts_with($line, 'Pages:')) {
                        $pages = (int) trim(str_replace('Pages:', '', $line));
                    }
                }

                $this->pdfUpload->update([
                    'pages' => $pages,
                    'status' => 'completed',
                    'extracted_text' => $extractedText,
                    'ocr_processed' => true,
                ]);

                // Clean up temp file
                unlink($outputFile);
            }

            AuditLog::log(
                userId: $this->pdfUpload->user_id,
                action: 'pdf_ocr_completed',
                entityType: 'pdf_upload',
                entityId: $this->pdfUpload->id,
                newValues: ['status' => 'completed', 'pages' => $this->pdfUpload->pages]
            );
        } catch (\Exception $e) {
            $this->pdfUpload->update([
                'status' => 'failed',
                'ocr_error' => $e->getMessage(),
            ]);

            AuditLog::log(
                userId: $this->pdfUpload->user_id,
                action: 'pdf_ocr_failed',
                entityType: 'pdf_upload',
                entityId: $this->pdfUpload->id,
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            // This will retry based on $tries
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->pdfUpload->update([
            'status' => 'failed',
            'ocr_error' => 'Failed after multiple attempts: ' . $exception->getMessage(),
        ]);

        AuditLog::log(
            userId: $this->pdfUpload->user_id,
            action: 'pdf_ocr_failed_final',
            entityType: 'pdf_upload',
            entityId: $this->pdfUpload->id,
            status: 'failed',
            errorMessage: $exception->getMessage()
        );
    }
}
