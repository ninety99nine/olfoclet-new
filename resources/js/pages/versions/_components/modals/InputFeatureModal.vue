<template>

    <Modal
        size="md"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">

        <template #content>

            <div>

                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-6">
                    Input
                </p>

                <div class="space-y-6">

                    <div v-if="isNewInputWithoutContent" class="w-full min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <p class="text-sm font-semibold text-slate-900">Instruction for this step <span class="text-rose-600">*</span></p>
                        <p class="text-xs text-slate-500">
                            A step with an input must have content so users know what to enter. Add a short message below; it will be added as content above the input.
                        </p>
                        <ContentEditor v-model="instruction" :fullWidth="true" :showHeader="false" />
                    </div>

                    <div class="space-y-4">
                        <Input
                            class="flex-1"
                            label="Save input as"
                            placeholder="e.g. firstName"
                            :model-value="effectiveFeature.variable"
                            @update:model-value="val => effectiveFeature.variable = toCamelCase(val)">
                            <template #prefix>
                                <span class="text-xs text-slate-600 border-r border-slate-300 pr-2 mr-2">@</span>
                            </template>
                        </Input>
                        <p class="text-xs text-slate-500 -mt-2">
                            You can reference this later as <strong>@{{ effectiveFeature.variable || 'value' }}</strong>
                        </p>
                    </div>

                    <StepSelector
                        v-model="effectiveFeature.next_step_id"
                        @update:pendingStep="syncPendingStepState"
                    />

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">Validation rules</p>
                            <Button size="xs" type="primary" mode="outline" :action="addRule" buttonClass="rounded-lg">Add rule</Button>
                        </div>

                        <div v-if="!effectiveRules.length" class="mt-3 text-sm text-slate-500">No rules yet.</div>
                        <div v-else class="mt-4 space-y-3">
                            <div v-for="(entry, index) in effectiveRules" :key="entry.vid" class="rounded-lg border border-slate-200 bg-white">
                                <button type="button" class="w-full px-3 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors rounded-lg cursor-pointer" @click="toggleRule(index)">
                                    <div class="min-w-0 text-left">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ versionState.getValidationRuleLabel(entry.rule.type) }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ versionState.getValidationRuleSummary(entry.rule) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <ChevronDown v-if="expandedIndex !== index" size="14" class="text-slate-400" />
                                        <ChevronUp v-else size="14" class="text-slate-400" />
                                        <Button size="xs" type="danger" mode="outline" :action="(e) => { e?.stopPropagation?.(); removeRule(entry.vid, index); }" buttonClass="rounded-lg">Remove</Button>
                                    </div>
                                </button>

                                <transition name="collapsible">
                                    <div v-if="expandedIndex === index" class="px-3 pb-3 space-y-3">
                                        <Select label="Rule" v-model="entry.rule.type" :options="versionState.inputValidationRuleOptions" @change="() => updateRuleMessage(entry.rule)" />
                                        <div v-if="['min_length','max_length','equal_length'].includes(entry.rule.type)">
                                            <Input label="Length" v-model="entry.rule.value" placeholder="e.g. 3" @change="() => updateRuleMessage(entry.rule)" />
                                        </div>
                                        <div v-else-if="['eq','neq','lt','lte','gt','gte'].includes(entry.rule.type)">
                                            <Input label="Value" v-model="entry.rule.value" placeholder="e.g. 3" @change="() => updateRuleMessage(entry.rule)" />
                                        </div>
                                        <div v-else-if="['between_inclusive','between_exclusive'].includes(entry.rule.type)" class="grid grid-cols-2 gap-3">
                                            <Input label="Min" v-model="entry.rule.min" placeholder="e.g. 1" @change="() => updateRuleMessage(entry.rule)" />
                                            <Input label="Max" v-model="entry.rule.max" placeholder="e.g. 10" @change="() => updateRuleMessage(entry.rule)" />
                                        </div>
                                        <div v-else-if="entry.rule.type === 'regex'">
                                            <Input label="Regex pattern" v-model="entry.rule.pattern" placeholder="e.g. ^[A-Z]{3}$" @change="() => updateRuleMessage(entry.rule)" />
                                        </div>
                                        <Input label="Error message" v-model="entry.rule.message" placeholder="What should we show if validation fails?" />
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <Button size="md" type="light" mode="outline" :action="onCancel" buttonClass="rounded-lg">Cancel</Button>
                    <Button size="md" type="primary" mode="solid" :action="onApply" :disabled="!canApply" buttonClass="rounded-lg">Apply</Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import { v4 as uuidv4 } from 'uuid';
import Modal from '@Partials/Modal.vue';
import Input from '@Partials/Input.vue';
import Button from '@Partials/Button.vue';
import Select from '@Partials/Select.vue';
import { toCamelCase } from '@Utils/stringUtils';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import StepSelector from '@Pages/versions/_components/selectors/StepSelector.vue';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';

export default {
    name: 'InputFeatureModal',
    components: { Modal, Button, Input, Select, ContentEditor, StepSelector, ChevronDown, ChevronUp },
    inject: ['versionState', 'notificationState'],
    data() {
        return {
            instruction: '',
            draftFeature: {
                type: 'input',
                variable: 'value',
                next_step_id: null,
                validation_rules: [],
            },
            expandedIndex: null,
            pendingNewStep: false,
            pendingNewStepName: null
        };
    },
    computed: {
        isNewInputWithoutContent() {
            return !this.versionState.currentStepHasContentFeature;
        },
        effectiveFeature() {
            return this.isNewInputWithoutContent ? this.draftFeature : this.versionState.currentFeature;
        },
        canApply() {
            const feat = this.effectiveFeature;
            if (!feat) return false;

            // 1. Must have instruction if creating combined content
            if (this.isNewInputWithoutContent && !(this.instruction || '').trim()) return false;

            // 2. Must have a variable name
            if (!(feat.variable || '').trim()) return false;

            // 3. Must have a step name if creating a new step
            if (this.pendingNewStep && !(this.pendingNewStepName || '').trim()) return false;

            return true;
        },
        effectiveRules() {
            if (this.isNewInputWithoutContent && this.draftFeature?.validation_rules) {
                return this.draftFeature.validation_rules.map((rule, i) => ({ vid: `draft-${i}`, rule }));
            }
            const f = this.effectiveFeature;
            if (!f?.validation_rule_ids?.length) return [];
            const rules = this.versionState.validationRules || {};
            return f.validation_rule_ids.map(vid => ({ vid, rule: rules[vid] })).filter(({ rule }) => rule);
        },
    },
    methods: {
        toCamelCase,
        reset() {
            this.instruction = '';
            this.draftFeature = {
                type: 'input',
                variable: 'value',
                next_step_id: null,
                validation_rules: [],
            };
            this.expandedIndex = null;
            this.pendingNewStep = false;
            this.pendingNewStepName = null;
        },
        syncPendingStepState(state) {
            this.pendingNewStep = state.isPending;
            this.pendingNewStepName = state.name;
        },
        addRule() {
            if (this.isNewInputWithoutContent) {

                // Use UUID even for draft IDs so the UI lists don't jump around
                const rule = {
                    id: uuidv4(),
                    type: 'alpha',
                    message: ''
                };

                rule.message = this.versionState.getValidationRuleDefaultMessage(rule);
                this.draftFeature.validation_rules.push(rule);
                return;

            }

            // For existing features, let the store handle UUID generation
            this.versionState.addValidationRule(this.versionState.currentFeatureId);
        },
        removeRule(vid, index) {
            if (this.isNewInputWithoutContent) {
                this.draftFeature.validation_rules.splice(index, 1);
                return;
            }
            this.versionState.removeValidationRule(this.versionState.currentFeatureId, vid);
        },
        toggleRule(index) {
            this.expandedIndex = this.expandedIndex === index ? null : index;
        },
        updateRuleMessage(rule) {
            rule.message = this.versionState.getValidationRuleDefaultMessage(rule);
        },
        onCancel() {
            this.reset();
            this.versionState.inputFeatureModal?.hideModal();
        },
        onApply() {
            const state = this.versionState;
            let nextStepId = this.effectiveFeature.next_step_id;

            // 1. Handle New Step Creation once (Centralized)
            if (this.pendingNewStep) {
                const newStep = state.addNewStepToCanvas(this.pendingNewStepName, true);
                nextStepId = newStep.id;
            }

            // 2. Scenario A: New Input (Requires Content + Feature Creation)
            if (this.isNewInputWithoutContent) {

                // Create Instruction
                state.addBasicContentFeature({ content: this.instruction.trim() });

                // Map Rules
                const vids = this.draftFeature.validation_rules.map(rule => {
                    state.builder.validation_rules[rule.id] = rule;
                    return rule.id;
                });

                // Create Input (using the new step ID if it was generated)
                state.addInputFeature({
                    variable: this.draftFeature.variable.trim(),
                    next_step_id: nextStepId,
                    validation_rule_ids: vids
                });

            }

            // 3. Scenario B: Existing Input
            else {

                // Just update the next_step_id (in case a new step was created)
                this.effectiveFeature.next_step_id = nextStepId;

            }

            // Crucial: Clear the selection so the next "Add" starts fresh
            setTimeout(() => {
                this.reset();
                state.setCurrentFeatureId(null);
            }, 300);

            state.inputFeatureModal?.hideModal();
        }
    },
};
</script>

<style scoped>
    .collapsible-enter-active,
    .collapsible-leave-active { transition: all 0.18s ease-out; }
    .collapsible-enter-from,
    .collapsible-leave-to { opacity: 0; max-height: 0; transform: translateY(-4px); }
    .collapsible-enter-to,
    .collapsible-leave-from { opacity: 1; max-height: 320px; transform: translateY(0); }
</style>
