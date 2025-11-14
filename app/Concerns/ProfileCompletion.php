<?php

namespace App\Concerns;

trait ProfileCompletion
{
    /**
     * Check if the user's profile is complete based on their role.
     */
    public function isProfileComplete(): bool
    {
        if ($this->hasRole('patient')) {
            return $this->isPatientProfileComplete();
        }

        if ($this->hasRole('employee')) {
            return $this->isEmployeeProfileComplete();
        }

        if ($this->hasRole('agent')) {
            return $this->isAgentProfileComplete();
        }

        // For other roles, consider profile complete
        return true;
    }

    /**
     * Get missing profile fields for the user's role.
     */
    public function getMissingProfileFields(): array
    {
        if ($this->hasRole('patient')) {
            return $this->getMissingPatientFields();
        }

        if ($this->hasRole('employee')) {
            return $this->getMissingEmployeeFields();
        }

        if ($this->hasRole('agent')) {
            return $this->getMissingAgentFields();
        }

        return [];
    }

    /**
     * Check if patient profile is complete.
     */
    protected function isPatientProfileComplete(): bool
    {
        $requiredFields = [
            'fname', 'lname', 'email', 'gender', 'dob',
            'address1', 'city', 'state', 'zip', 'phone'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        // Check if terms have been agreed to
        if (!$this->hasAgreedToTerms()) {
            return false;
        }

        return true;
    }

    /**
     * Check if employee profile is complete.
     */
    protected function isEmployeeProfileComplete(): bool
    {
        // Check basic user fields
        $requiredUserFields = [
            'fname', 'lname', 'email', 'gender', 'dob',
            'address1', 'city', 'state', 'zip', 'phone'
        ];

        foreach ($requiredUserFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        // Check if employee record exists
        $employee = $this->businessEmployee;
        if (!$employee) {
            return false;
        }

        // Check if terms have been agreed to
        if (!$this->hasAgreedToTerms()) {
            return false;
        }

        return true;
    }

    /**
     * Check if agent profile is complete.
     */
    protected function isAgentProfileComplete(): bool
    {
        // Check basic user fields
        $requiredUserFields = ['fname', 'lname', 'email', 'phone'];

        foreach ($requiredUserFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        // Check agent-specific fields
        $agent = $this->agent;
        if (!$agent) {
            return false;
        }

        $requiredAgentFields = ['company', 'experience'];
        foreach ($requiredAgentFields as $field) {
            if (empty($agent->$field)) {
                return false;
            }
        }

        // Check if terms have been agreed to
        if (!$this->hasAgreedToTerms()) {
            return false;
        }

        return true;
    }

    /**
     * Get missing patient profile fields.
     */
    protected function getMissingPatientFields(): array
    {
        $requiredFields = [
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'email' => 'Email',
            'gender' => 'Gender',
            'dob' => 'Date of Birth',
            'address1' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'ZIP Code',
            'phone' => 'Phone Number'
        ];

        $missing = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($this->$field)) {
                $missing[$field] = $label;
            }
        }

        // Check terms agreement
        if (!$this->hasAgreedToTerms()) {
            $missing['terms_agreement'] = 'Terms and Conditions Agreement';
        }

        return $missing;
    }

    /**
     * Get missing employee profile fields.
     */
    protected function getMissingEmployeeFields(): array
    {
        $requiredUserFields = [
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'email' => 'Email',
            'gender' => 'Gender',
            'dob' => 'Date of Birth',
            'address1' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'ZIP Code',
            'phone' => 'Phone Number'
        ];

        $missing = [];
        foreach ($requiredUserFields as $field => $label) {
            if (empty($this->$field)) {
                $missing[$field] = $label;
            }
        }

        // Check if employee record exists
        $employee = $this->businessEmployee;
        if (!$employee) {
            $missing['employee_record'] = 'Employee Record';
        }

        // Check terms agreement
        if (!$this->hasAgreedToTerms()) {
            $missing['terms_agreement'] = 'Terms and Conditions Agreement';
        }

        return $missing;
    }

    /**
     * Get missing agent profile fields.
     */
    protected function getMissingAgentFields(): array
    {
        $requiredUserFields = [
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone Number'
        ];

        $missing = [];
        foreach ($requiredUserFields as $field => $label) {
            if (empty($this->$field)) {
                $missing[$field] = $label;
            }
        }

        // Check agent-specific fields
        $agent = $this->agent;
        if (!$agent) {
            $missing['agent_profile'] = 'Agent Profile';
            return $missing;
        }

        $requiredAgentFields = [
            'company' => 'Company',
            'experience' => 'Experience'
        ];

        foreach ($requiredAgentFields as $field => $label) {
            if (empty($agent->$field)) {
                $missing[$field] = $label;
            }
        }

        // Check terms agreement
        if (!$this->hasAgreedToTerms()) {
            $missing['terms_agreement'] = 'Terms and Conditions Agreement';
        }

        return $missing;
    }

    /**
     * Check if user has agreed to terms and conditions.
     */
    protected function hasAgreedToTerms(): bool
    {
        // Check if user has a terms_agreed_at timestamp
        return !empty($this->terms_agreed_at);
    }

    /**
     * Check if user has completed a medical questionnaire.
     */
    public function hasMedicalQuestionnaire(): bool
    {
        return $this->medicalQuestionnaires()->exists();
    }

    /**
     * Check if user has temporarily dismissed medical questionnaire prompt.
     */
    public function hasTemporarilyDismissedMedicalQuestionnaire(): bool
    {
        $dismissedAt = session('medical_questionnaire_dismissed_at');
        if (!$dismissedAt) {
            return false;
        }

        // Show again after 24 hours
        return now()->diffInHours($dismissedAt) < 24;
    }

    /**
     * Temporarily dismiss medical questionnaire prompt.
     */
    public function temporarilyDismissMedicalQuestionnaire(): void
    {
        session(['medical_questionnaire_dismissed_at' => now()]);
    }
}
