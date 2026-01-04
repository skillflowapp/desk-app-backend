<?php

namespace App\Http\Controllers\Api;

use App\Models\PdfUpload;
use App\Models\AuditLog;
use App\Jobs\ProcessPdfOcr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfUploadController
{
    /**
     * Upload PDF file
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            $file = $validated['pdf'];
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store in private storage
            $path = Storage::disk('local')->putFileAs(
                'pdfs',
                $file,
                $filename,
                'private'
            );

            // Create PDF upload record
            $pdfUpload = $request->user()->pdfUploads()->create([
                'filename' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'status' => 'pending',
                'pages' => 0,
            ]);

            // Queue OCR processing
            ProcessPdfOcr::dispatch($pdfUpload);

            AuditLog::log(
                userId: $request->user()->id,
                action: 'pdf_uploaded',
                entityType: 'pdf_upload',
                entityId: $pdfUpload->id,
                newValues: ['filename' => $filename, 'status' => 'pending']
            );

            return response()->json([
                'message' => 'PDF uploaded successfully. Processing started.',
                'pdf_upload' => $pdfUpload,
            ], 201);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $request->user()->id,
                action: 'pdf_upload_failed',
                entityType: 'pdf_upload',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => 'Failed to upload PDF',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's PDF uploads
     */
    public function index(Request $request)
    {
        $pdfs = $request->user()
            ->pdfUploads()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'pdfs' => $pdfs,
        ], 200);
    }

    /**
     * Get PDF upload details
     */
    public function show(Request $request, PdfUpload $pdfUpload)
    {
        // Authorization: only user who uploaded can view
        if ($request->user()->id !== $pdfUpload->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'pdf_upload' => $pdfUpload,
        ], 200);
    }
}
