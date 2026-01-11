<?php

class QuestionValidationService
{
    public function validate(array $postData): array
    {
        $questionText = trim($postData['question'] ?? '');
        if (!$questionText) {
            throw new InvalidArgumentException('Question text is required');
        }

        $answers = [
            'A' => trim($postData['ans_A'] ?? ''),
            'B' => trim($postData['ans_B'] ?? ''),
            'C' => trim($postData['ans_C'] ?? ''),
            'D' => trim($postData['ans_D'] ?? '')
        ];

        // 2. Filter null answers
        $answers = $answers = array_filter($answers, fn($ans) => !empty($ans));

        $correctKey = $postData['correct'] ?? null;
        if (!$correctKey) {
            throw new InvalidArgumentException('Correct answer is required');
        }

        // 2b. Check if correct answer exits
        if (!array_key_exists($correctKey, $answers)) {
            throw new InvalidArgumentException('The correct answer does not exist or is empty');
        }

        return [
            'question_text' => $questionText,
            'answers' => $answers,
            'correct_key' => $correctKey
        ];
    }
}
