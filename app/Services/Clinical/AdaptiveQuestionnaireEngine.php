<?php

namespace App\Services\Clinical;

/**
 * Adaptive Questionnaire Engine
 *
 * Implements adaptive questioning logic that adjusts questions based on
 * patient responses. Supports branching logic, conditional questions,
 * and dynamic question sequencing.
 */
class AdaptiveQuestionnaireEngine
{
    /**
     * Evaluate branching logic based on responses.
     *
     * @param  array<string, mixed>  $questions
     * @param  array<string, mixed>  $responses
     * @return array<string, mixed>
     */
    public function evaluateBranching(array $questions, array $responses): array
    {
        $nextQuestions = [];

        foreach ($questions as $question) {
            if ($this->shouldIncludeQuestion($question, $responses)) {
                $nextQuestions[] = $question;
            }
        }

        return $nextQuestions;
    }

    /**
     * Determine if a question should be included based on conditions.
     *
     * @param  array<string, mixed>  $question
     * @param  array<string, mixed>  $responses
     */
    private function shouldIncludeQuestion(array $question, array $responses): bool
    {
        if (! isset($question['conditions'])) {
            return true;
        }

        foreach ($question['conditions'] as $condition) {
            if (! $this->evaluateCondition($condition, $responses)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition.
     *
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $responses
     */
    private function evaluateCondition(array $condition, array $responses): bool
    {
        $questionId = $condition['question_id'] ?? null;
        $operator = $condition['operator'] ?? '==';
        $value = $condition['value'] ?? null;

        if (! isset($responses[$questionId])) {
            return false;
        }

        $responseValue = $responses[$questionId];

        return match ($operator) {
            '==' => $responseValue == $value,
            '!=' => $responseValue != $value,
            '>' => $responseValue > $value,
            '<' => $responseValue < $value,
            '>=' => $responseValue >= $value,
            '<=' => $responseValue <= $value,
            'in' => in_array($responseValue, (array) $value),
            'not_in' => ! in_array($responseValue, (array) $value),
            default => false,
        };
    }

    /**
     * Calculate a risk score based on responses.
     *
     * @param  array<string, mixed>  $responses
     * @param  array<string, mixed>  $scoringRules
     */
    public function calculateRiskScore(array $responses, array $scoringRules): float
    {
        $score = 0;

        foreach ($scoringRules as $rule) {
            if ($this->evaluateCondition($rule, $responses)) {
                $score += $rule['points'] ?? 0;
            }
        }

        return min($score, 100); // Cap at 100
    }
}

