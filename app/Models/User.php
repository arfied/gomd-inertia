<?php

namespace App\Models;

use App\Concerns\HandlesCommissionTiers;
use App\Concerns\LOAAccessControl;
use App\Concerns\ProfileCompletion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasRoles, ProfileCompletion;
    use HandlesCommissionTiers;
    use LOAAccessControl;

    const ROLES = [
        'admin' => 'Admin',
        'staff' => 'Staff',
        'doctor' => 'Doctor',
        'patient' => 'Patient',
        'pharmacist' => 'Pharmacist',
        'agent' => 'Agent',
        'loa' => 'LOA',
        'business_admin' => 'Business Admin',
        'business_hr' => 'Business HR',
        'employee' => 'Employee',
    ];

    const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending_approval' => 'Pending Approval',
        'deleted' => 'Deleted',
        'awaiting_verification' => 'Awaiting Verification',
        'payment_due' => 'Payment Due',
        'on_hold' => 'On Hold',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'authorize_net_customer_id',
        'password',
        'role',
        'status',
        'fname',
        'lname',
        'gender',
        'dob',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'bio',
        'image',
        'phone',
        'mobile_phone',
        'business_id',
        'referring_agent_id',
        'referring_loa_id',
        'managing_agent_id',
        'loa_referral_code',
        'terms_agreed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'datetime:Y-m-d',
            'terms_agreed_at' => 'datetime',
        ];
    }

    // public function roles(): HasMany
    // {
    //     return $this->hasMany(Role::class);
    // }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function allergies()
    {
        return $this->hasMany(Allergy::class);
    }

    public function doctorConsultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    public function patientConsultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }

    public function measurements()
    {
        return $this->hasMany(UserMeasurement::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function createdMedicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id');
    }

    public function delete() {
        $this->status = 'deleted';
        $this->save();
    }

    public function reactivate() {
        $this->status = 'active';
        $this->save();
    }

    public function doctorAssignments()
    {
        return $this->hasMany(DoctorPatientAssignment::class, 'doctor_id');
    }

    public function patientAssignments()
    {
        return $this->hasMany(DoctorPatientAssignment::class, 'patient_id');
    }

    public function assignedPatients()
    {
        // the select is to fix the overwriting of the users.id
        return $this->belongsToMany(User::class, 'doctor_patient_assignments', 'doctor_id', 'patient_id')
            ->select([
                'users.*',
                'doctor_patient_assignments.id as assignment_id',
                'doctor_patient_assignments.assigned_by',
                'doctor_patient_assignments.assigned_at'
            ]);
    }

    public function assignedDoctors()
    {
        return $this->belongsToMany(User::class, 'doctor_patient_assignments', 'patient_id', 'doctor_id');
    }

    public function prescriptionsAsDoctor()
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    public function prescriptionsAsPatient()
    {
        return $this->hasMany(Prescription::class, 'user_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function userServices()
    {
        return $this->hasMany(UserService::class, 'user_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'user_services', 'user_id', 'service_id')
                    ->withPivot('created_at', 'updated_at', 'video_path', 'status')
                    ->withTimestamps();
    }

    // subscriptions has it's own table now
    public function hasActiveSubscription()
    {
        return $this->subscription_ends_at && $this->subscription_ends_at > now();
    }

    public function canAccessService()
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        if ($this->subscriptionPlan->service_limit === null) {
            return true;
        }

        return $this->usedServices()->count() < $this->subscriptionPlan->service_limit;
    }

    public function medications()
    {
        return $this->hasManyThrough(
            Medication::class,
            PrescriptionItem::class,
            'prescription_id', // Foreign key on prescription_items table...
            'id', // Foreign key on medications table...
            'id', // Local key on users table...
            'medication_id' // Local key on prescription_items table...
        )->join('prescriptions', 'prescriptions.id', '=', 'prescription_items.prescription_id')
        ->where('prescriptions.user_id', $this->id);
    }

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class);
    }

    public function allCreditCards()
    {
        return $this->hasMany(CreditCard::class)->withTrashed();
    }

    public function defaultCreditCard()
    {
        return $this->creditCards()->where('is_default', true)->first();
    }

    /**
     * Get all payment methods for the user.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get all payment methods for the user including soft deleted ones.
     */
    public function allPaymentMethods()
    {
        return $this->hasMany(PaymentMethod::class)->withTrashed();
    }

    /**
     * Get the default payment method for the user.
     */
    public function defaultPaymentMethod()
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    /**
     * Get all credit card payment methods from payment_methods table.
     */
    public function getAllCreditCards()
    {
        return $this->paymentMethods()
            ->where('type', 'credit_card')
            ->get()
            ->map(function ($method) {
                // Create a virtual CreditCard-like object for backward compatibility
                return (object) [
                    'id' => $method->id,
                    'user_id' => $method->user_id,
                    'brand' => $method->cc_brand,
                    'last_four' => $method->cc_last_four,
                    'expiration_month' => $method->cc_expiration_month,
                    'expiration_year' => $method->cc_expiration_year,
                    'is_default' => $method->is_default,
                    'token' => $method->cc_token,
                    'source' => 'payment_methods',
                    'model' => $method
                ];
            });
    }

    /**
     * Get the default credit card from payment_methods table.
     */
    public function getDefaultCreditCard()
    {
        $defaultPaymentMethod = $this->paymentMethods()
            ->where('type', 'credit_card')
            ->where('is_default', true)
            ->first();

        if ($defaultPaymentMethod) {
            return (object) [
                'id' => $defaultPaymentMethod->id,
                'user_id' => $defaultPaymentMethod->user_id,
                'brand' => $defaultPaymentMethod->cc_brand,
                'last_four' => $defaultPaymentMethod->cc_last_four,
                'expiration_month' => $defaultPaymentMethod->cc_expiration_month,
                'expiration_year' => $defaultPaymentMethod->cc_expiration_year,
                'is_default' => $defaultPaymentMethod->is_default,
                'token' => $defaultPaymentMethod->cc_token,
                'source' => 'payment_methods',
                'model' => $defaultPaymentMethod
            ];
        }

        return null;
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    /**
     * Get the user's subscription that is pending payment.
     *
     * @return \App\Models\Subscription|null
     */
    public function pendingPaymentSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'pending_payment')
            ->latest('created_at')
            ->first();
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function painAssessment()
    {
        return $this->hasOne(PainAssessment::class, 'patient_id');
    }

    public function medicalConditions()
    {
        return $this->hasMany(MedicalCondition::class, 'patient_id');
    }

    public function lifestyleHabit()
    {
        return $this->hasOne(LifestyleHabit::class, 'patient_id');
    }

    public function mentalHealthAssessment()
    {
        return $this->hasOne(MentalHealthAssessment::class, 'patient_id');
    }

    public function familyMedicalHistory()
    {
        return $this->hasOne(FamilyMedicalHistory::class, 'patient_id');
    }

    public function additionalInformation()
    {
        return $this->hasOne(AdditionalInformation::class, 'patient_id');
    }

    public function medicalSurgicalHistory ()
    {
        return $this->hasOne(MedicalSurgicalHistory::class, 'patient_id');
    }

    public function psychologicalSocialFactors()
    {
        return $this->hasOne(PsychologicalSocialFactor::class, 'patient_id');
    }

    public function physicalExaminationIndicators()
    {
        return $this->hasOne(PhysicalExaminationIndicator::class, 'patient_id');
    }

    public function diagnosticTests()
    {
        return $this->hasMany(DiagnosticTest::class, 'patient_id');
    }

    public function medicationScreening()
    {
        return $this->hasOne(MedicationScreening::class, 'patient_id');
    }

    public function userReportedMedication()
    {
        return $this->hasMany(UserReportedMedication::class);
    }

    public function healthQuestion()
    {
        return $this->hasOne(HealthQuestion::class);
    }

    public function preferredMedications()
    {
        return $this->hasMany(PreferredMedication::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function medicalQuestionnaires()
    {
        return $this->hasMany(MedicalQuestionnaire::class);
    }

    public function videoRecordings()
    {
        return $this->hasMany(VideoRecording::class);
    }

    public function symptoms()
    {
        return $this->belongsToMany(Symptom::class, 'patient_id');
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    /**
     * Get the agent that manages this LOA user.
     */
    public function managingAgent()
    {
        return $this->belongsTo(Agent::class, 'managing_agent_id');
    }

    /**
     * Get the LOA referrals created by this user (if they are an LOA).
     */
    public function loaReferrals()
    {
        return $this->hasMany(LOAReferral::class, 'loa_user_id');
    }

    /**
     * Get the agent who referred this user.
     */
    public function referringAgent()
    {
        return $this->belongsTo(Agent::class, 'referring_agent_id');
    }

    /**
     * Get the LOA user who referred this user.
     */
    public function referringLOA()
    {
        return $this->belongsTo(User::class, 'referring_loa_id');
    }

    /**
     * Get the business employee record associated with this user.
     */
    public function businessEmployee()
    {
        return $this->hasOne(BusinessEmployee::class);
    }

    /**
     * Get the business this user belongs to.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function createPreferredMedications($questionnaireData)
    {
        $medicationTypes = [
            1 => 'ed_medications',
            2 => 'hair_loss_medications',
            3 => 'weight_loss_medications',
            4 => 'anxiety_medications',
            5 => 'anti_aging_medications',
            6 => 'herpes_medications',
            7 => 'pain_medications'
        ];

        $serviceId = $questionnaireData['service_id'];
        $medicationType = $medicationTypes[$serviceId] ?? null;

        if ($medicationType && isset($questionnaireData[$medicationType])) {
            foreach ($questionnaireData[$medicationType] as $medicationName => $medicationData) {
                if ($medicationData['preferred'] === true) {
                    $this->preferredMedications()->create([
                        'medication_name' => $medicationName,
                        'taken_before' => $medicationData['taken_before'],
                        'effectiveness' => $medicationData['taken_before'] ? $medicationData['effectiveness'] : null
                    ]);
                }
            }
        }
    }

    /**
     * Get the medication orders created by this user as a patient.
     */
    public function medicationOrders()
    {
        return $this->hasMany(MedicationOrder::class, 'patient_id');
    }

    /**
     * Get the medication orders assigned to this user as a doctor.
     */
    public function assignedMedicationOrders()
    {
        return $this->hasMany(MedicationOrder::class, 'doctor_id');
    }

    /**
     * Get family memberships where this user is the primary account holder.
     */
    public function primaryFamilyMemberships()
    {
        return $this->hasMany(FamilyMember::class, 'primary_user_id');
    }

    /**
     * Get family memberships where this user is a dependent.
     */
    public function dependentFamilyMemberships()
    {
        return $this->hasMany(FamilyMember::class, 'dependent_user_id');
    }

    /**
     * Get the primary account holder for this dependent user.
     *
     * @return User|null
     */
    public function primaryAccountHolder()
    {
        $membership = $this->dependentFamilyMemberships()->first();
        return $membership ? $membership->primaryUser : null;
    }

    /**
     * Get all dependents for this primary account holder.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function dependents()
    {
        return $this->primaryFamilyMemberships()
            ->with('dependentUser')
            ->get()
            ->pluck('dependentUser');
    }

    /**
     * Get minor dependents (under 18) for this primary account holder.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function minorDependents()
    {
        return $this->primaryFamilyMemberships()
            ->whereHas('dependentUser', function ($query) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18');
            })
            ->with('dependentUser')
            ->get()
            ->pluck('dependentUser');
    }

    /**
     * Get adult dependents (18-23) for this primary account holder.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function adultDependents()
    {
        return $this->primaryFamilyMemberships()
            ->whereHas('dependentUser', function ($query) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 18')
                      ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= 23');
            })
            ->with('dependentUser')
            ->get()
            ->pluck('dependentUser');
    }

    /**
     * Check if this user is a dependent in a family plan.
     *
     * @return bool
     */
    public function isDependent(): bool
    {
        return $this->dependentFamilyMemberships()->exists();
    }

    /**
     * Check if this user is a minor dependent (under 18).
     *
     * @return bool
     */
    public function isMinorDependent(): bool
    {
        if (!$this->isDependent() || !$this->dob) {
            return false;
        }

        return $this->dob->age < 18;
    }

    /**
     * Check if this user is an adult dependent (18-23).
     *
     * @return bool
     */
    public function isAdultDependent(): bool
    {
        if (!$this->isDependent() || !$this->dob) {
            return false;
        }

        $age = $this->dob->age;
        return $age >= 18 && $age <= 23;
    }

    /**
     * Check if this user is a primary account holder in a family plan.
     *
     * @return bool
     */
    public function isPrimaryAccountHolder(): bool
    {
        return $this->primaryFamilyMemberships()->exists();
    }

    /**
     * Check if this user account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Generate a unique LOA referral code for this user.
     *
     * @return void
     */
    public function generateLOAReferralCode(): void
    {
        if ($this->isLOA() && !$this->loa_referral_code) {
            do {
                $code = 'LOA-' . strtoupper(\Illuminate\Support\Str::random(8));
            } while (self::where('loa_referral_code', $code)->exists());

            $this->loa_referral_code = $code;
            $this->save();
        }
    }

    /**
     * Get general referral URLs for LOA user.
     *
     * @return array
     */
    public function getLOAReferralUrls(): array
    {
        if (!$this->isLOA()) {
            return [];
        }

        // Ensure LOA has a referral code
        if (!$this->loa_referral_code) {
            $this->generateLOAReferralCode();
        }

        $managingAgent = $this->managingAgent;
        $agentParam = $managingAgent && $managingAgent->referral_code
            ? '&agent_ref=' . $managingAgent->referral_code
            : '';

        return [
            'prescription' => url('/rx') . '?loa_ref=' . $this->loa_referral_code . $agentParam,
            'urgent_care' => url('/urgent-care') . '?loa_ref=' . $this->loa_referral_code . $agentParam,
            'health_plan' => url('/health-plan') . '?loa_ref=' . $this->loa_referral_code . $agentParam,
            'general' => url('/') . '?loa_ref=' . $this->loa_referral_code . $agentParam,
            'business' => url('/business/register') . '?loa_ref=' . $this->loa_referral_code . $agentParam,
        ];
    }

    /**
     * Get users referred by this LOA user.
     */
    public function loaReferredUsers()
    {
        return $this->hasMany(User::class, 'referring_loa_id');
    }
}
