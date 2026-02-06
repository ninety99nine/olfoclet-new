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
                        buttonClass="rounded-lg"
                        :action="setInitialFeatures"
                        v-if="displayFeatures[0]?.value !== 'content'">
                    </Button>
                    <div
                        :key="feature.value"
                        @click="selectFeature(feature)"
                        v-for="feature in displayFeatures"
                        class="group relative flex items-center gap-4 p-5 bg-white border border-slate-200 rounded-xl hover:border-indigo-400 hover:shadow-md transition-all duration-200 cursor-pointer">
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
                        <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <ArrowRight class="w-5 h-5 text-indigo-500" />
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <Button
                        size="md"
                        type="light"
                        mode="outline"
                        :action="() => versionState.featuresModal?.hideModal()"
                        buttonClass="rounded-lg">
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
    import { ArrowLeft, ArrowRight, Code, MessageSquare, TextCursorInput, ListChecks, Plus } from 'lucide-vue-next';

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
        components: { Modal, Button, ArrowRight, Code, MessageSquare, TextCursorInput, ListChecks, Plus },
        inject: ['versionState'],
        data() {
            return {
                ArrowLeft,
                displayFeatures: [],
            };
        },
        methods: {
            setInitialFeatures() {
                this.displayFeatures = [...(this.versionState.initialFeatures || [])];
            },
            getFeatureIcon(value) {
                return ICONS[value] || Plus;
            },
            getFeatureDescription(value) {
                return DESCRIPTIONS[value] || 'Add custom behavior to this step';
            },
            selectFeature(selectedFeature) {
                // 1. Handle nested sub-options navigation (Content -> Basic/Code)
                if (selectedFeature.subOptions) {
                    this.displayFeatures = selectedFeature.subOptions;
                    return;
                }

                const type = selectedFeature.value;
                const state = this.versionState;

                // 2. Close and Reset UI
                state.featuresModal?.hideModal();

                // 3. Map feature values to specific Pinia actions
                const actionMap = {
                    'basic content': () => state.addBasicContentFeature(),
                    'code content':  () => state.addCodeContentFeature(),
                    'select':        () => state.addSelectFeature(),
                    'input':         () => state.addInputFeature(),
                };

                if (actionMap[type]) {
                    actionMap[type]();
                } else {
                    // Fallback for any generic types added later
                    state.addFeature(type);
                }

                // Handle UI navigation/modals based on what was just added
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
