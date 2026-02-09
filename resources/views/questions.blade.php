<x-front-alt-layout>
    @include('front.landing-pages.medications.medical-intake-header')

    <x-splade-rehydrate on="item-removed">
        <div class="max-w-5xl mx-auto px-4 py-4 sm:py-8 space-y-4">
            <OrderProgressBar
                current-step="medical_questions"
                :completed-steps="{{ json_encode(Session::get('completed_steps', [])) }}"
                :progress="{{ $progress ?? 50 }}"
            />
            <div class="bg-white border border-gray-300 rounded-lg p-4 shadow-md">
                <div class="flex flex-col gap-4">
                    <div class="flex justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold">Medications</h2>
                            <h3 class="text-lg">{{ $plan['name'] }}</h3>
                        </div>
                        <div class="flex items-center">
                            <GenericsAZ @cart-updated="console.log('Cart updated')" />
                        </div>
                    </div>
                    @if($cartItems->isEmpty())
                        <p class="text-gray-600">No medications added to cart.</p>
                    @else
                        <table class="table-auto">
                            <thead>
                                <tr>
                                    <td>Name</td>
                                    <td>Strength</td>
                                    <td>Dosage Form</td>
                                    <td>Categories</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartItems as $item)
                                    <tr>
                                        <td class="border px-2 py-2">{{ $item['brand_name'] }}</td>
                                        <td class="border px-2 py-2">{{ $item['strength'] }}</td>
                                        <td class="border px-2 py-2">{{ $item['dosage_form'] }}</td>
                                        <td class="border px-2 py-2">{{ $item['categories']->implode(', ') }}</td>
                                        <td class="border px-2 py-2 max-w-12">
                                            <x-splade-form method="DELETE" action="/cart/{{$item['id']}}" stay @success="$splade.emit('item-removed', $event.detail)">
                                                <button type="submit" class="btn btn-ghost btn-xs text-error">Remove</button>
                                            </x-splade-form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            @if($cartItems->isNotEmpty())
            <div class="flex flex-col gap-4">
                <h2 class="text-2xl font-semibold">Comprehensive Health Screening Form</h2>
                <p class="text-gray-600 mb-4">All fields are required, but you may answer with "None" or "Never" or "Not Applicable" ("N/A")</p>

                <x-splade-form :default="$medicalQuestions">
                    @php
                        $cardioVascular = collect(['Cardiovascular', 'Heart Conditions', 'Blood Pressure', 'Anticoagulation', 'Cholesterol Management']);
                        $hasCardio = $categories->intersect($cardioVascular)->isNotEmpty();
                    @endphp
                    @if($hasCardio)
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Cardiovascular Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="cardiovascular_diagnosis" label="Have you been diagnosed with high blood pressure, high cholesterol, or heart disease?" />
                            <x-splade-textarea name="cardiovascular_symptoms" label="Have you experienced chest pain, shortness of breath, or irregular heartbeat recently?" />
                            <x-splade-textarea name="cardiovascular_medications" label="List all current heart/blood pressure medications" />
                            <x-splade-textarea name="cardiovascular_family" label="Family history of heart disease, stroke, or high cholesterol?" />
                            <x-splade-textarea name="cardiovascular_diet" label="Describe your current diet and salt intake" />
                            <x-splade-textarea name="cardiovascular_lifestyle" label="Describe your exercise routine and stress levels" />
                            <x-splade-textarea name="cardiovascular_monitoring" label="How do you monitor your blood pressure/cholesterol? List recent readings" />
                        </div>
                    </div>
                    @endif

                    @php
                        $neurological = collect(['Neurological', 'Seizures', 'RLS']);
                        $hasNeurological = $categories->intersect($neurological)->isNotEmpty();
                    @endphp
                    @if($hasNeurological)
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h3 class="text-xl font-semibold mb-4">Neurological Assessment</h3>
                        <div class="space-y-4">
                            <x-splade-textarea name="neuro_diagnosis" label="Describe any diagnosed neurological conditions (seizures, RLS, etc.)" />
                            <x-splade-textarea name="neuro_frequency" label="How often do you experience symptoms? Include dates of recent episodes" />
                            <x-splade-textarea name="neuro_triggers" label="What triggers your symptoms?" />
                            <x-splade-textarea name="neuro_sleep" label="How do symptoms affect your sleep?" />
                            <x-splade-textarea name="neuro_daily_impact" label="Impact on daily activities (driving, work, etc.)" />
                            <x-splade-textarea name="neuro_medications" label="Current neurological medications and their effectiveness" />
                            <x-splade-textarea name="neuro_side_effects" label="Side effects from current or past treatments" />
                        </div>
                    </div>
                    @endif

                    @if($categories->contains('Gastrointestinal'))
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h3 class="text-xl font-semibold mb-4">Gastrointestinal Assessment</h3>
                        <div class="space-y-4">
                            <x-splade-textarea name="gi_symptoms" label="Describe your digestive symptoms (pain, bloating, changes in bowel movements)" />
                            <x-splade-textarea name="gi_frequency" label="How often do symptoms occur? Include pattern and timing" />
                            <x-splade-textarea name="gi_diet" label="List foods that trigger symptoms" />
                            <x-splade-textarea name="gi_medications" label="Current medications for digestive issues" />
                            <x-splade-textarea name="gi_procedures" label="Previous GI procedures or surgeries" />
                            <x-splade-textarea name="gi_weight" label="Recent weight changes" />
                        </div>
                    </div>
                    @endif

                    @if($categories->contains('Endocrine/Hormonal'))
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Endocrine Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="endocrine_diagnosis" label="Diagnosed endocrine conditions (thyroid, diabetes, etc.)" />
                            <x-splade-textarea name="endocrine_symptoms" label="Current symptoms (fatigue, weight changes, temperature sensitivity)" />
                            <x-splade-textarea name="endocrine_labs" label="Recent lab results and dates" />
                            <x-splade-textarea name="endocrine_medications" label="Hormone medications or supplements" />
                            <x-splade-textarea name="endocrine_monitoring" label="How do you monitor your condition? (blood sugar, etc.)" />
                        </div>
                    </div>
                    @endif

                    @php
                        $pc = collect(['Preventive Care', 'Osteoporosis Prevention']);
                        $hasPreventiveCare = $categories->intersect($pc)->isNotEmpty();
                    @endphp
                    @if($hasPreventiveCare)
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Preventive Care Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="preventive_risk" label="Risk factors (family history, lifestyle, medications)" />
                            <x-splade-textarea name="preventive_diet" label="Calcium and vitamin D intake" />
                            <x-splade-textarea name="preventive_exercise" label="Exercise routine, particularly weight-bearing activities" />
                            <x-splade-textarea name="preventive_screenings" label="Previous bone density scans or relevant tests" />
                            <x-splade-textarea name="preventive_falls" label="History of falls or fractures" />
                        </div>
                    </div>
                    @endif

                    @php
                        $infection = collect(['Prophylaxis', 'Bacterial', 'Fungal', 'Viral']);
                        $hasInfection = $categories->intersect($infection)->isNotEmpty();
                    @endphp
                    @if($hasInfection)
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Infection Prevention Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="prophylaxis_history" label="History of infections requiring treatment" />
                            <x-splade-textarea name="prophylaxis_risk" label="Risk factors (medical procedures, chronic conditions)" />
                            <x-splade-textarea name="prophylaxis_immunity" label="Immune system status and recent illnesses" />
                            <x-splade-textarea name="prophylaxis_allergies" label="Medication allergies or adverse reactions" />
                            <x-splade-textarea name="prophylaxis_current" label="Current preventive medications" />
                        </div>
                    </div>
                    @endif

                    @if($categories->contains('Skin/Dermatological'))
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Dermatological Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="skin_conditions" label="Current skin conditions and affected areas" />
                            <x-splade-textarea name="skin_symptoms" label="Describe symptoms (itching, pain, appearance changes)" />
                            <x-splade-textarea name="skin_triggers" label="Known triggers (stress, foods, environmental factors)" />
                            <x-splade-textarea name="skin_treatments" label="Previous treatments and their effectiveness" />
                            <x-splade-textarea name="skin_impact" label="Impact on daily life and sleep" />
                        </div>
                    </div>
                    @endif

                    @if($categories->contains('Immunology & Allergies'))
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Immunology Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="immune_conditions" label="Diagnosed autoimmune or immune conditions" />
                            <x-splade-textarea name="immune_allergies" label="Known allergies and reactions" />
                            <x-splade-textarea name="immune_symptoms" label="Current symptoms and their frequency" />
                            <x-splade-textarea name="immune_treatments" label="Current treatments (medications, injections)" />
                            <x-splade-textarea name="immune_triggers" label="Environmental or seasonal triggers" />
                            <x-splade-textarea name="immune_emergency" label="Emergency plan for severe reactions" />
                        </div>
                    </div>
                    @endif

                    @php
                        $mentalHealth = collect(['Mental Health', 'Anxiety Disorders', 'Depression']);
                        $hasMentalHealth = $categories->intersect($mentalHealth)->isNotEmpty();
                    @endphp
                    @if($hasMentalHealth)
                    <div class="bg-white border shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Mental Health Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="mh_symptoms_severity" label="Rate severity of symptoms (anxiety, depression, mood changes)" />
                            <x-splade-textarea name="mh_sleep_patterns" label="Describe your sleep patterns and any disturbances" />
                            <x-splade-textarea name="mh_concentration" label="Impact on concentration and daily activities" />
                            <x-splade-textarea name="mh_support_system" label="Current support system and therapy/counseling" />
                            <x-splade-textarea name="mh_coping_methods" label="Current coping mechanisms" />
                            <x-splade-textarea name="mh_suicidal_thoughts" label="Any thoughts of self-harm or suicide?" />
                            <x-splade-textarea name="mh_treatment_history" label="Previous mental health treatments and their effectiveness" />
                        </div>
                    </div>
                    @endif

                    @php
                        $pain = collect(['Pain & Inflammation', 'Migraine', 'Musculoskeletal']);
                        $hasPain = $categories->intersect($pain)->isNotEmpty();
                    @endphp
                    @if($hasPain)
                    <div class="bg-white border shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Pain Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="pain_location_type" label="Location and type of pain (sharp, dull, throbbing)" />
                            <x-splade-textarea name="pain_frequency" label="Frequency and duration of pain episodes" />
                            <x-splade-textarea name="pain_severity" label="Pain severity (1-10) and how it varies" />
                            <x-splade-textarea name="pain_triggers" label="Known triggers (activities, foods, weather)" />
                            <x-splade-textarea name="pain_relief" label="What provides relief? (medications, rest, etc.)" />
                            <x-splade-textarea name="pain_impact" label="Impact on work, sleep, and daily activities" />
                            <x-splade-textarea name="pain_associated_symptoms" label="Associated symptoms (nausea, sensitivity to light/sound)" />
                        </div>
                    </div>
                    @endif

                    @php
                        $respiratory = collect(['Respiratory', 'Nasal Conditions']);
                        $hasRespiratory = $categories->intersect($respiratory)->isNotEmpty();
                    @endphp
                    @if($hasRespiratory)
                    <div class="bg-white border shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Respiratory Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="respiratory_symptoms" label="Current symptoms (cough, congestion, breathing difficulty)" />
                            <x-splade-textarea name="respiratory_triggers" label="Environmental triggers or allergens" />
                            <x-splade-textarea name="respiratory_sleep" label="Breathing difficulties during sleep" />
                            <x-splade-textarea name="respiratory_exercise" label="Impact on physical activity" />
                            <x-splade-textarea name="respiratory_treatments" label="Current treatments (inhalers, nebulizers)" />
                            <x-splade-textarea name="respiratory_smoking" label="Smoking history and exposure" />
                        </div>
                    </div>
                    @endif

                    @php
                        $prevention = collect(['Surgical Prophylaxis', 'UTI Prevention', 'Stroke Prevention']);
                        $hasPrevention = $categories->intersect($prevention)->isNotEmpty();
                    @endphp
                    @if($hasPrevention)
                    <div class="bg-white border shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Prevention Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="prevention_risk_factors" label="Known risk factors" />
                            <x-splade-textarea name="prevention_history" label="Previous related conditions or surgeries" />
                            <x-splade-textarea name="prevention_medications" label="Current preventive medications" />
                            <x-splade-textarea name="prevention_lifestyle" label="Lifestyle factors (diet, exercise, smoking)" />
                            <x-splade-textarea name="prevention_monitoring" label="Current monitoring methods" />
                            <x-splade-textarea name="prevention_family_history" label="Relevant family medical history" />
                        </div>
                    </div>
                    @endif

                    @if($categories->contains('Weight Management'))
                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Weight Management Assessment</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="weight_history" label="Describe your weight history, including any significant weight changes (gain or loss) over the past year." />
                            <x-splade-textarea name="current_weight_goals" label="What is your current weight, and what are your weight loss or management goals?" />
                            <x-splade-textarea name="lifestyle_factors" label="Describe your current diet, exercise routine, and any lifestyle factors that may affect your weight (e.g., stress, sleep habits)." />
                            <x-splade-textarea name="underlying_conditions" label="Have you been diagnosed with any conditions that may contribute to weight issues, such as Type 2 Diabetes, PCOS, or hypothyroidism?" />
                            <x-splade-textarea name="previous_attempts" label="Have you tried any weight loss programs, diets, or medications in the past? If so, what were the results?" />
                            <x-splade-textarea name="weight_medication_history" label="Are you currently taking or have you taken any medications for weight management? If yes, please list them and describe their effectiveness." />
                            <x-splade-textarea name="barriers_to_weight_loss" label="What challenges or barriers do you face in achieving your weight management goals?" />
                            <x-splade-textarea name="family_weight_history" label="Is there a family history of obesity, diabetes, or other weight-related conditions?" />
                        </div>
                    </div>
                    @endif

                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Additional Information</h2>
                        <div class="space-y-4">
                            <x-splade-textarea name="additional_symptoms" label="Any other symptoms not mentioned above" />
                            <x-splade-textarea name="quality_of_life" label="How do your conditions affect your quality of life?" />
                            <x-splade-textarea name="treatment_goals" label="What are your primary goals for treatment?" />
                            <x-splade-textarea name="concerns" label="Additional concerns or questions for the doctor" />
                        </div>
                    </div>

                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Current Medication</h2>
                        <div v-for="(meds, index) in form.medications" :key="index"class="space-y-4 pb-4">
                            <x-splade-input v-model="form.medications[index].medication_name" label="<div class='flex justify-between'>Medication Name <span class='text-red-500'>*required</span></div>" />
                            <x-splade-input v-model="form.medications[index].dosage" label="Dosage" />
                            <x-splade-input v-model="form.medications[index].frequency" label="Frequency" />
                            <x-splade-input v-model="form.medications[index].reaction" label="Reaction" />
                            <x-splade-input v-model="form.medications[index].side_effects" label="Side Effects" />
                            <button type="button" @click="form.medications.splice(index, 1)"
                                class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">Remove</button>
                        </div>
                        <button type="button" @click="form.medications.push({})"
                            class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">Add Medication</button>
                    </div>

                    <div class="bg-white border shadow-md rounded-lg p-4 mb-4">
                        <h2 class="text-xl font-semibold mb-4">Allergies</h2>
                        <div v-for="(meds, index) in form.allergies" :key="index"class="space-y-4 pb-4">
                            <x-splade-input v-model="form.allergies[index].allergen" label="<div class='flex justify-between'>Allergen <span class='text-red-500'>*required</span></div>" />
                            <x-splade-textarea v-model="form.allergies[index].reaction" label="Reaction" />
                            <button type="button" @click="form.allergies.splice(index, 1)"
                                class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">Remove</button>
                        </div>
                        <button type="button" @click="form.allergies.push({})"
                            class="px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">Add Allergy</button>
                    </div>

                    <x-splade-submit />
                </x-splade-form>
            </div>
            @endif
        </div>
    </x-splade-rehydrate>

    @include('front.landing-pages.medications.how-it-works')
</x-front-alt-layout>
