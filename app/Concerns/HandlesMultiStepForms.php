<?php

namespace App\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

trait HandlesMultiStepForms
{
    /**
     * Store form data in the session and redirect to the next step.
     *
     * @param Request $request The current request
     * @param string $sessionKey The session key to store the form data
     * @param int $currentStep The current step number
     * @param int $totalSteps The total number of steps
     * @param string $routeName The route name for redirection
     * @param array $routeParams Additional route parameters
     * @return RedirectResponse
     */
    protected function storeStepAndRedirect(
        Request $request,
        string $sessionKey,
        int $currentStep,
        int $totalSteps,
        string $routeName,
        array $routeParams = []
    ): RedirectResponse {
        // Store the current form data in the session
        $formData = $request->except('step');

        // Preserve agent referral information
        $referringAgentId = \Illuminate\Support\Facades\Cookie::get('referring_agent_id') ??
                           session('referring_agent_id') ??
                           null;
        if ($referringAgentId) {
            $formData['referring_agent_id'] = $referringAgentId;
            \Illuminate\Support\Facades\Log::info('Preserving agent referral in multi-step form', [
                'session_key' => $sessionKey,
                'step' => $currentStep,
                'referring_agent_id' => $referringAgentId
            ]);
        }

        session()->put($sessionKey, array_merge(session($sessionKey, []), $formData));

        // Determine the next step
        $nextStep = $currentStep + 1;

        // If this is the final step, return null to indicate completion
        if ($nextStep > $totalSteps) {
            return null;
        }

        // Add the next step to the route parameters
        $routeParams['step'] = $nextStep;

        // Redirect to the next step
        return redirect()->route($routeName, $routeParams);
    }

    /**
     * Get the stored form data from the session.
     *
     * @param string $sessionKey The session key where form data is stored
     * @param array $defaults Default values to merge with the session data
     * @return array The combined form data
     */
    protected function getStoredFormData(string $sessionKey, array $defaults = []): array
    {
        return array_merge($defaults, session($sessionKey, []));
    }

    /**
     * Clear the stored form data from the session.
     *
     * @param string $sessionKey The session key to clear
     * @return void
     */
    protected function clearStoredFormData(string $sessionKey): void
    {
        session()->forget($sessionKey);
    }
}
