<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTeacher();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:0',
            'pdf_upload_id' => 'nullable|exists:pdf_uploads,id',
            'show_answers_after' => 'nullable|boolean',
            'ai_prompt' => 'nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'duration_minutes.min' => 'Exam duration must be at least 5 minutes',
            'duration_minutes.max' => 'Exam duration cannot exceed 480 minutes',
            'total_marks.min' => 'Total marks must be at least 1',
            'passing_marks.min' => 'Passing marks cannot be negative',
        ];
    }
}
