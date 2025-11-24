import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface SignupState {
    signupId: string | null
    userId: string | null
    signupPath: 'medication_first' | 'condition_first' | 'plan_first' | null
    medicationNames: string[]
    conditionId: string | null
    planId: number | null
    questionnaireResponses: Record<string, any>
    paymentId: string | null
    paymentAmount: number | null
    paymentStatus: 'success' | 'pending' | 'failed' | null
    subscriptionId: string | null
    status: 'pending' | 'completed' | 'failed'
    failureReason: string | null
    failureMessage: string | null
}

export const useSignupStore = defineStore('signup', () => {
    const state = ref<SignupState>({
        signupId: null,
        userId: null,
        signupPath: null,
        medicationNames: [],
        conditionId: null,
        planId: null,
        questionnaireResponses: {},
        paymentId: null,
        paymentAmount: null,
        paymentStatus: null,
        subscriptionId: null,
        status: 'pending',
        failureReason: null,
        failureMessage: null,
    })

    const loading = ref(false)
    const error = ref<string | null>(null)

    const isStarted = computed(() => state.value.signupId !== null)
    const isCompleted = computed(() => state.value.status === 'completed')
    const isFailed = computed(() => state.value.status === 'failed')

    async function startSignup(signupPath: 'medication_first' | 'condition_first' | 'plan_first') {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/signup/start', { signup_path: signupPath })
            state.value.signupId = response.data.signup_id
            state.value.signupPath = signupPath
            return response.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to start signup'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function selectMedication(medicationName: string) {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/select-medication', {
                signup_id: state.value.signupId,
                medication_name: medicationName,
            })
            // Add medication to array if not already present
            if (!state.value.medicationNames.includes(medicationName)) {
                state.value.medicationNames.push(medicationName)
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to select medication'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function selectCondition(conditionId: string) {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/select-condition', {
                signup_id: state.value.signupId,
                condition_id: conditionId,
            })
            state.value.conditionId = conditionId
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to select condition'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function selectPlan(planId: number) {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/select-plan', {
                signup_id: state.value.signupId,
                plan_id: planId,
            })
            state.value.planId = planId
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to select plan'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function completeQuestionnaire(responses: Record<string, any>, questionnaireUuid: string | null) {
        if (!state.value.signupId) throw new Error('Signup not started')
        if (!questionnaireUuid) throw new Error('Questionnaire UUID not available')
        loading.value = true
        error.value = null
        try {
            await axios.post('/api/questionnaires/submit', {
                questionnaire_uuid: questionnaireUuid,
                patient_id: state.value.userId,
                responses,
            })
            state.value.questionnaireResponses = responses
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to complete questionnaire'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function processPayment(paymentId: string, amount: number, paymentStatus: 'success' | 'pending' | 'failed') {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/process-payment', {
                signup_id: state.value.signupId,
                payment_id: paymentId,
                amount,
                status: paymentStatus,
            })
            state.value.paymentId = paymentId
            state.value.paymentAmount = amount
            state.value.paymentStatus = paymentStatus
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to process payment'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function createSubscription(subscriptionId: string, userId: string) {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/create-subscription', {
                signup_id: state.value.signupId,
                subscription_id: subscriptionId,
                user_id: userId,
                plan_id: state.value.planId,
                medication_names: state.value.medicationNames,
                condition_id: state.value.conditionId,
            })
            state.value.subscriptionId = subscriptionId
            state.value.userId = userId
            state.value.status = 'completed'
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create subscription'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function failSignup(reason: 'validation_error' | 'payment_failed' | 'system_error', message: string) {
        if (!state.value.signupId) throw new Error('Signup not started')
        loading.value = true
        error.value = null
        try {
            await axios.post('/signup/fail', {
                signup_id: state.value.signupId,
                reason,
                message,
            })
            state.value.status = 'failed'
            state.value.failureReason = reason
            state.value.failureMessage = message
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fail signup'
            throw err
        } finally {
            loading.value = false
        }
    }

    function reset() {
        state.value = {
            signupId: null,
            userId: null,
            signupPath: null,
            medicationNames: [],
            conditionId: null,
            planId: null,
            questionnaireResponses: {},
            paymentId: null,
            paymentAmount: null,
            paymentStatus: null,
            subscriptionId: null,
            status: 'pending',
            failureReason: null,
            failureMessage: null,
        }
        loading.value = false
        error.value = null
    }

    return {
        state,
        loading,
        error,
        isStarted,
        isCompleted,
        isFailed,
        startSignup,
        selectMedication,
        selectCondition,
        selectPlan,
        completeQuestionnaire,
        processPayment,
        createSubscription,
        failSignup,
        reset,
    }
})

