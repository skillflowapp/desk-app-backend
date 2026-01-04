<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\PdfUploadController;
use App\Http\Controllers\Api\ExamSessionController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\AuditLogController;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/send-email-otp', [AuthController::class, 'sendEmailVerificationOtp']);
Route::post('/auth/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
Route::post('/auth/send-password-reset-otp', [AuthController::class, 'sendPasswordResetOtp']);
Route::post('/auth/reset-password-otp', [AuthController::class, 'resetPasswordWithOtp']);
Route::post('/auth/check-otp-validity', [AuthController::class, 'checkOtpValidity']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Teacher only routes
    Route::middleware('teacher')->group(function () {
        // Exam management
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams', [ExamController::class, 'index']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy']);
        Route::post('/exams/{exam}/publish', [ExamController::class, 'publish']);
        Route::post('/exams/{exam}/generate-code', [ExamController::class, 'generateCode']);
        Route::post('/exams/{exam}/generate-from-pdf', [ExamController::class, 'generateFromPdf']);

        // Exam questions
        Route::post('/exams/{exam}/questions', [ExamController::class, 'addQuestion']);
        Route::put('/exams/{exam}/questions/{question}', [ExamController::class, 'updateQuestion']);
        Route::delete('/exams/{exam}/questions/{question}', [ExamController::class, 'deleteQuestion']);

        // PDF uploads
        Route::post('/pdfs/upload', [PdfUploadController::class, 'store']);
        Route::get('/pdfs', [PdfUploadController::class, 'index']);
        Route::get('/pdfs/{pdfUpload}', [PdfUploadController::class, 'show']);

        // Results & grading
        Route::get('/exams/{exam}/results', [ExamController::class, 'results']);
        Route::post('/exams/{exam}/grade', [ExamController::class, 'grade']);
        Route::post('/results/{result}/publish', [ResultController::class, 'publish']);
        Route::post('/results/{result}/remarks', [ResultController::class, 'addRemarks']);
    });

    // Student only routes
    Route::middleware('student')->group(function () {
        // Exam entry
        Route::post('/exams/enter', [ExamSessionController::class, 'enter']);
        Route::get('/exams/active', [ExamSessionController::class, 'active']);

        // Taking exam
        Route::get('/exams/session/{examSession}', [ExamSessionController::class, 'show']);
        Route::post('/exams/session/{examSession}/answer', [ExamSessionController::class, 'submitAnswer']);
        Route::post('/exams/session/{examSession}/submit', [ExamSessionController::class, 'submit']);

        // Results
        Route::get('/results', [ExamSessionController::class, 'myResults']);
        Route::get('/results/{result}', [ResultController::class, 'show']);
        Route::get('/results/{result}/export', [ResultController::class, 'exportPdf']);
        Route::post('/results/{result}/flag', [ResultController::class, 'flag']);

        // Offline sync
        Route::post('/sync', [SyncController::class, 'sync']);
        Route::get('/sync/pending', [SyncController::class, 'getPending']);
        Route::post('/sync/acknowledge', [SyncController::class, 'acknowledge']);
        Route::get('/sync/status', [SyncController::class, 'status']);
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show']);
        Route::get('/users/{user}/activity', [AuditLogController::class, 'userActivity']);
        Route::post('/audit-logs/export', [AuditLogController::class, 'export']);
    });
});
