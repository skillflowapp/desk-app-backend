<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\PdfUpload;

class ExamGenerationService
{
    /**
     * Generate exam questions from PDF content using AI
     * This service integrates with OpenAI, Gemini, or local LLM
     */
    public function generateQuestionsFromPdf(
        PdfUpload $pdf,
        string $aiPrompt,
        int $numberOfQuestions = 10,
        string $questionTypes = 'mcq,short_answer'
    ): array {
        // Validate PDF has been processed
        if (!$pdf->ocr_processed) {
            throw new \Exception('PDF has not been processed yet. Please wait for OCR to complete.');
        }

        if ($pdf->status !== 'completed') {
            throw new \Exception('PDF processing failed. Error: ' . $pdf->ocr_error);
        }

        // Prepare the prompt for AI
        $systemPrompt = $this->buildSystemPrompt($questionTypes, $numberOfQuestions);
        $userPrompt = $this->buildUserPrompt($pdf->extracted_text, $aiPrompt);

        try {
            // Call AI service (implemented below)
            $response = $this->callAiService($systemPrompt, $userPrompt);
            
            // Parse response into structured questions
            $questions = $this->parseQuestionsFromResponse($response);
            
            return $questions;
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate questions: ' . $e->getMessage());
        }
    }

    private function buildSystemPrompt(string $questionTypes, int $numberOfQuestions): string
    {
        return <<<PROMPT
You are an expert educational content creator. Your task is to generate high-quality exam questions from the provided text.

Requirements:
1. Generate exactly $numberOfQuestions questions
2. Use ONLY these question types: $questionTypes
3. For MCQ: provide 4 options (A, B, C, D)
4. For short answer: provide a brief model answer
5. Format output as JSON array

JSON Schema:
{
  "questions": [
    {
      "question_number": 1,
      "type": "mcq" | "short_answer" | "essay",
      "question_text": "string",
      "options": { "A": "text", "B": "text", "C": "text", "D": "text" } (for MCQ only),
      "correct_answer": "A" (for MCQ only),
      "model_answer": "string" (for short answer/essay),
      "marks": 1,
      "explanation": "Why this answer is correct",
      "difficulty": "easy" | "medium" | "hard"
    }
  ]
}

Ensure questions test comprehension and critical thinking, not just memorization.
PROMPT;
    }

    private function buildUserPrompt(string $pdfText, string $aiPrompt): string
    {
        return <<<PROMPT
Content from PDF:
---
$pdfText
---

Teacher's Instructions:
$aiPrompt

Please generate exam questions based on the above content and instructions.
PROMPT;
    }

    /**
     * Call AI service (supports OpenAI, Gemini, or local LLM)
     */
    private function callAiService(string $systemPrompt, string $userPrompt): string
    {
        $provider = config('ai.provider', 'openai');

        return match ($provider) {
            'openai' => $this->callOpenAi($systemPrompt, $userPrompt),
            'gemini' => $this->callGemini($systemPrompt, $userPrompt),
            'local' => $this->callLocalLLM($systemPrompt, $userPrompt),
            default => throw new \Exception("Unsupported AI provider: $provider"),
        };
    }

    private function callOpenAi(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = config('ai.openai.api_key');
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $client = new \OpenAI\Client($apiKey);

        $response = $client->chat()->create([
            'model' => config('ai.openai.model', 'gpt-4-turbo'),
            'temperature' => 0.7,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
        ]);

        return $response['choices'][0]['message']['content'];
    }

    private function callGemini(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = config('ai.gemini.api_key');
        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured');
        }

        // Implementation would use Gemini SDK
        // This is a placeholder
        throw new \Exception('Gemini implementation pending');
    }

    private function callLocalLLM(string $systemPrompt, string $userPrompt): string
    {
        $endpoint = config('ai.local.endpoint', 'http://localhost:8000');

        // Use Ollama or similar local LLM
        $response = \Illuminate\Support\Facades\Http::post("$endpoint/api/generate", [
            'model' => config('ai.local.model', 'llama2'),
            'prompt' => "$systemPrompt\n\n$userPrompt",
            'stream' => false,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Local LLM request failed: ' . $response->body());
        }

        return $response->json('response');
    }

    /**
     * Parse AI response into structured questions
     */
    private function parseQuestionsFromResponse(string $response): array
    {
        try {
            // Extract JSON from response (AI might include extra text)
            preg_match('/\{[\s\S]*"questions"[\s\S]*\}/', $response, $matches);
            
            if (!$matches) {
                throw new \Exception('No JSON found in response');
            }

            $data = json_decode($matches[0], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in response: ' . json_last_error_msg());
            }

            return $data['questions'] ?? [];
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse AI response: ' . $e->getMessage());
        }
    }
}
