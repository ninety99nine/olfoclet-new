<template>
    <Modal
        size="sm"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">

        <template #content>
            <div>
                <div class="mt-2 mb-6">
                    <p class="text-sm text-slate-500">
                        Choose what this step should do for the user.
                    </p>
                </div>
                <div
                    :key="displayFeatures[0]?.value"
                    :class="['grid grid-cols-1 gap-4 transition-opacity duration-750']">

                    <Button
                        size="xs"
                        type="basic"
                        rightIconSize="24"
                        :rightIcon="ArrowLeft"
                        buttonClass="rounded-lg mb-2"
                        :action="setInitialFeatures"
                        v-if="displayFeatures[0]?.value !== 'content'">
                    </Button>

                    <div
                        v-for="feature in displayFeatures"
                        :key="feature.value"
                        @click="isFeatureDisabled(feature) ? null : selectFeature(feature)"
                        :class="[
                            'group relative flex items-center gap-4 p-5 bg-white border rounded-xl transition-all duration-200',
                            isFeatureDisabled(feature)
                                ? 'opacity-40 border-slate-100 cursor-not-allowed pointer-events-none'
                                : 'border-slate-200 hover:border-indigo-400 hover:shadow-md cursor-pointer'
                        ]">

                        <div class="shrink-0 w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <component :is="getFeatureIcon(feature.value)" class="w-6 h-6 text-indigo-600" />
                        </div>

                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-slate-900 group-hover:text-indigo-700 transition-colors">
                                {{ feature.label }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ getFeatureDescription(feature.value) }}
                            </p>
                        </div>

                        <div v-if="!isFeatureDisabled(feature)" class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <ArrowRight class="w-5 h-5 text-indigo-500" />
                        </div>
                    </div>
                </div>

                <div v-if="isTerminalStep" class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-3">
                    <Info class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] leading-relaxed text-amber-800 font-medium">
                            Interactive features like the <span class="font-bold">Input</span> and <span class="font-bold">Selection</span> are disabled because this step is set to end the session.
                        </p>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                @click="goToStepSettings"
                                class="mt-1.5 text-[11px] font-bold text-amber-700 hover:text-amber-900 underline underline-offset-2 cursor-pointer transition-colors bg-transparent border-none p-0 outline-none">
                                Change Settings
                        </button>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="() => versionState.featuresModal?.hideModal()"
                        buttonClass="rounded-lg px-6 font-bold">
                        Cancel
                    </Button>
                </div>
            </div>
        </template>

    </Modal>
</template>

<script>
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import { ArrowLeft, ArrowRight, Code, MessageSquare, TextCursorInput, ListChecks, Plus, Info } from 'lucide-vue-next';

    const ICONS = {
        select: ListChecks,
        'code content': Code,
        input: TextCursorInput,
        content: MessageSquare,
        'basic content': MessageSquare,
    };
    const DESCRIPTIONS = {
        select: 'Show a list of options for the user to choose from',
        content: 'Display text, instructions or information to the user',
        'code content': 'Write PHP, Python or JavaScript code for dynamic content',
        'basic content': 'Simple text with support for variables like {{ firstName }}',
        input: 'Let the user type or enter information (PIN, amount, phone number, etc.)',
    };

    export default {
        name: 'FeaturesModal',
        components: { Modal, Button, ArrowRight, Code, MessageSquare, TextCursorInput, ListChecks, Plus, Info },
        inject: ['versionState'],
        data() {
            return {
                ArrowLeft,
                displayFeatures: [],
            };
        },
        computed: {
            currentStep() {
                return this.versionState.currentStep;
            },
            isTerminalStep() {
                return this.currentStep?.is_terminal || false;
            }
        },
        methods: {
            setInitialFeatures() {
                this.displayFeatures = [...(this.versionState.initialFeatures || [])];
            },
            isFeatureDisabled(feature) {
                return this.isTerminalStep && ['input', 'select'].includes(feature.value);
            },
            getFeatureIcon(value) {
                return ICONS[value] || Plus;
            },
            getFeatureDescription(value) {
                return DESCRIPTIONS[value] || 'Add custom behavior to this step';
            },
            goToStepSettings() {
                // Close current modal
                this.versionState.featuresModal?.hideModal();

                // Allow a small delay for the backdrop to transition
                setTimeout(() => {
                    // Open step settings
                    this.versionState.stepEditModal?.showModal();
                }, 300);
            },
            selectFeature(selectedFeature) {
                if (selectedFeature.subOptions) {
                    this.displayFeatures = selectedFeature.subOptions;
                    return;
                }

                const type = selectedFeature.value;
                const state = this.versionState;

                state.featuresModal?.hideModal();

                const actionMap = {
                    'basic content': () => state.addBasicContentFeature(),
                    'code content':  () => state.addCodeContentFeature(),
                    'select':        () => state.addSelectFeature(),
                    'input':         () => state.addInputFeature(),
                };

                if (actionMap[type]) {
                    actionMap[type]();
                } else {
                    state.addFeature(type);
                }

                if (type === 'input' && state.currentStepHasContentFeature) state.inputFeatureModal?.showModal();
                if (type === 'basic content') state.basicContentEditorModal?.showModal();
                if (type === 'code content') state.codeContentEditorModal?.showModal();
                if (type === 'select') state.selectFeatureModal?.showModal();

                this.setInitialFeatures();
            },
        },
        created() {
            this.setInitialFeatures();
        },
    };
</script>
