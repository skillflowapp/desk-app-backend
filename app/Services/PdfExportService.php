<?php

namespace App\Services;

use App\Models\Result;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export result as PDF
     */
    public function exportResult(Result $result): string
    {
        $result->load('exam', 'examSession.answers', 'student');

        $html = View::make('pdf.result', [
            'result' => $result,
            'exam' => $result->exam,
            'student' => $result->student,
            'examSession' => $result->examSession,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('margin-top', 15)
            ->setOption('margin-bottom', 15)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->output();
    }

    /**
     * Export exam as PDF
     */
    public function exportExam(Exam $exam): string
    {
        $exam->load('questions', 'teacher');

        $html = View::make('pdf.exam', [
            'exam' => $exam,
            'teacher' => $exam->teacher,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('margin-top', 15)
            ->setOption('margin-bottom', 15)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->output();
    }

    /**
     * Export exam results summary
     */
    public function exportExamResults(Exam $exam): string
    {
        $results = $exam->results()
            ->with('student', 'examSession')
            ->where('status', 'published')
            ->get();

        $html = View::make('pdf.exam-results', [
            'exam' => $exam,
            'results' => $results,
            'totalStudents' => $results->count(),
            'averageMarks' => $results->avg('obtained_marks'),
            'passRate' => $results->where('is_passed', true)->count() / max(1, $results->count()) * 100,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 15)
            ->setOption('margin-bottom', 15)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->output();
    }
}
