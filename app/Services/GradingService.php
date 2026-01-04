<?php

namespace App\Services;

use App\Models\ExamSession;
use App\Models\Result;
use App\Models\StudentAnswer;

class GradingService
{
    /**
     * Grade an exam session
     */
    public function gradeExamSession(ExamSession $examSession): Result
    {
        $exam = $examSession->exam;
        $result = $examSession->result;
        $answers = $examSession->answers()->with('question')->get();

        $totalMarks = 0;
        $obtainedMarks = 0;
        $questionScores = [];
        $aiComments = [];

        foreach ($exam->questions as $question) {
            $answer = $answers->firstWhere('exam_question_id', $question->id);
            $marks = $question->marks;
            $totalMarks += $marks;

            $scoreData = match ($question->type) {
                'mcq' => $this->gradeMultipleChoice($question, $answer, $marks),
                'short_answer' => $this->gradeShortAnswer($question, $answer, $marks),
                'essay' => $this->gradeEssay($question, $answer, $marks),
                default => ['obtained' => 0, 'total' => $marks, 'feedback' => ''],
            };

            $obtainedMarks += $scoreData['obtained'];
            $questionScores[$question->id] = [
                'question_number' => $question->question_number,
                'type' => $question->type,
                'obtained' => $scoreData['obtained'],
                'total' => $marks,
                'percentage' => ($marks > 0) ? ($scoreData['obtained'] / $marks) * 100 : 0,
            ];

            if (isset($scoreData['feedback'])) {
                $aiComments[$question->id] = $scoreData['feedback'];
            }
        }

        // Calculate percentage
        $percentage = ($totalMarks > 0) ? ($obtainedMarks / $totalMarks) * 100 : 0;
        $isPassed = $obtainedMarks >= $exam->passing_marks;

        // Update result
        $result->update([
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => round($percentage, 2),
            'is_passed' => $isPassed,
            'status' => 'graded',
            'question_scores' => $questionScores,
            'ai_feedback' => $this->generateFeedback($exam, $obtainedMarks, $totalMarks, $aiComments),
            'graded_at' => now(),
        ]);

        return $result;
    }

    /**
     * Grade multiple choice question
     */
    private function gradeMultipleChoice($question, ?StudentAnswer $answer, int $marks): array
    {
        if (!$answer || !$answer->selected_option) {
            return [
                'obtained' => 0,
                'total' => $marks,
                'feedback' => 'Not answered',
            ];
        }

        $isCorrect = $answer->selected_option === $question->correct_answer;

        return [
            'obtained' => $isCorrect ? $marks : 0,
            'total' => $marks,
            'feedback' => $isCorrect ? 'Correct' : 'Incorrect. ' . $question->explanation,
        ];
    }

    /**
     * Grade short answer using AI
     */
    private function gradeShortAnswer($question, ?StudentAnswer $answer, int $marks): array
    {
        if (!$answer || !$answer->answer_text) {
            return [
                'obtained' => 0,
                'total' => $marks,
                'feedback' => 'Not answered',
            ];
        }

        try {
            $aiScore = $this->callAiForGrading(
                $question->question_text,
                $question->model_answer,
                $answer->answer_text,
                'short_answer',
                $marks
            );

            return $aiScore;
        } catch (\Exception $e) {
            // Fallback to partial credit
            return [
                'obtained' => intval($marks * 0.5),
                'total' => $marks,
                'feedback' => 'Answer provided. Manual review recommended.',
            ];
        }
    }

    /**
     * Grade essay using AI
     */
    private function gradeEssay($question, ?StudentAnswer $answer, int $marks): array
    {
        if (!$answer || !$answer->answer_text) {
            return [
                'obtained' => 0,
                'total' => $marks,
                'feedback' => 'Not answered',
            ];
        }

        try {
            $aiScore = $this->callAiForGrading(
                $question->question_text,
                $question->model_answer,
                $answer->answer_text,
                'essay',
                $marks
            );

            return $aiScore;
        } catch (\Exception $e) {
            // Fallback to partial credit
            return [
                'obtained' => intval($marks * 0.3),
                'total' => $marks,
                'feedback' => 'Essay submitted. Manual review recommended.',
            ];
        }
    }

    /**
     * Call AI service for grading
     */
    private function callAiForGrading(
        string $question,
        ?string $modelAnswer,
        string $studentAnswer,
        string $questionType,
        int $maxMarks
    ): array {
        $provider = config('ai.provider', 'openai');

        return match ($provider) {
            'openai' => $this->gradeWithOpenAi($question, $modelAnswer, $studentAnswer, $questionType, $maxMarks),
            'gemini' => $this->gradeWithGemini($question, $modelAnswer, $studentAnswer, $questionType, $maxMarks),
            'local' => $this->gradeWithLocalLLM($question, $modelAnswer, $studentAnswer, $questionType, $maxMarks),
            default => throw new \Exception("Unsupported AI provider: $provider"),
        };
    }

    private function gradeWithOpenAi(string $question, ?string $modelAnswer, string $studentAnswer, string $questionType, int $maxMarks): array
    {
        $apiKey = config('ai.openai.api_key');
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $systemPrompt = $this->buildGradingSystemPrompt($questionType, $maxMarks);
        $userPrompt = $this->buildGradingUserPrompt($question, $modelAnswer, $studentAnswer);

        $client = new \OpenAI\Client($apiKey);

        $response = $client->chat()->create([
            'model' => config('ai.openai.model', 'gpt-4-turbo'),
            'temperature' => 0.3,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        return $this->parseGradingResponse($response['choices'][0]['message']['content']);
    }

    private function gradeWithGemini(string $question, ?string $modelAnswer, string $studentAnswer, string $questionType, int $maxMarks): array
    {
        // Gemini implementation
        throw new \Exception('Gemini grading implementation pending');
    }

    private function gradeWithLocalLLM(string $question, ?string $modelAnswer, string $studentAnswer, string $questionType, int $maxMarks): array
    {
        $endpoint = config('ai.local.endpoint', 'http://localhost:8000');

        $prompt = $this->buildGradingSystemPrompt($questionType, $maxMarks) . "\n\n" .
                 $this->buildGradingUserPrompt($question, $modelAnswer, $studentAnswer);

        $response = \Illuminate\Support\Facades\Http::post("$endpoint/api/generate", [
            'model' => config('ai.local.model', 'llama2'),
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Local LLM request failed');
        }

        return $this->parseGradingResponse($response->json('response'));
    }

    private function buildGradingSystemPrompt(string $questionType, int $maxMarks): string
    {
        return <<<PROMPT
You are an expert academic evaluator. Your task is to grade student answers fairly and objectively.

You must respond ONLY with a JSON object in this exact format:
{
  "obtained": <integer from 0 to $maxMarks>,
  "total": $maxMarks,
  "feedback": "<string explanation of the grade>"
}

Scoring guidelines:
- For $questionType questions, award marks based on accuracy, completeness, and understanding
- Consider partial credit for partially correct answers
- Maximum marks available: $maxMarks
- Provide constructive feedback

Return ONLY the JSON object, nothing else.
PROMPT;
    }

    private function buildGradingUserPrompt(string $question, ?string $modelAnswer, string $studentAnswer): string
    {
        return <<<PROMPT
Question: $question

Model/Expected Answer:
$modelAnswer

Student's Answer:
$studentAnswer

Please evaluate and grade the student's answer.
PROMPT;
    }

    private function parseGradingResponse(string $response): array
    {
        try {
            // Extract JSON from response
            preg_match('/\{[\s\S]*\}/', $response, $matches);
            
            if (!$matches) {
                throw new \Exception('No JSON found in response');
            }

            $data = json_decode($matches[0], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON: ' . json_last_error_msg());
            }

            return [
                'obtained' => intval($data['obtained'] ?? 0),
                'total' => intval($data['total'] ?? 1),
                'feedback' => $data['feedback'] ?? 'Graded by AI',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse grading response: ' . $e->getMessage());
        }
    }

    private function generateFeedback(Exam $exam, float $obtainedMarks, int $totalMarks, array $aiComments): array
    {
        $percentage = ($totalMarks > 0) ? ($obtainedMarks / $totalMarks) * 100 : 0;

        $overallFeedback = match (true) {
            $percentage >= 90 => 'Excellent performance!',
            $percentage >= 80 => 'Good job! Keep it up.',
            $percentage >= 70 => 'Satisfactory performance.',
            $percentage >= 60 => 'You need to improve.',
            $percentage >= 50 => 'Below average. Needs significant improvement.',
            default => 'Poor performance. Please review the material.',
        };

        return [
            'overall' => $overallFeedback,
            'percentage' => round($percentage, 2),
            'passed' => $obtainedMarks >= $exam->passing_marks,
            'question_comments' => $aiComments,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
