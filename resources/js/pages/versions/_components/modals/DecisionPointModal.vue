<template>
    <Modal
        size="lg"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false"
        :backdropClass="{ 'opacity-0 pointer-events-none' : isFullscreen }">

        <template #content>

            <div v-if="step" class="flex flex-col h-full bg-white select-none">

                <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-amber-500 rounded-xl text-white shadow-lg shadow-amber-100">
                            <GitBranch size="20" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight">Decision Point</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                Paths Guided By Rules
                            </p>
                        </div>
                    </div>
                    <Tabs
                        design="1"
                        class="mr-20"
                        v-model="step.source"
                        :tabs="[{label: 'Visual Builder', value: 'manual'}, {label: 'Code Mode', value: 'code'}]"
                    />
                </div>

                <div class="flex-1 pr-2 pb-10 space-y-6">

                    <Input label="Name" v-model="step.name" placeholder="e.g., Validate Transaction Logic" />

                    <Transition name="fade-slide" mode="out-in">

                        <div v-if="step.source === 'manual'" key="manual" class="space-y-6">

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">Branching Rules</h4>
                                        <p class="text-xs text-slate-500">The first rule that meets <b>ALL</b> its requirements will be followed.</p>
                                    </div>
                                    <Button size="sm" type="primary" buttonClass="rounded-lg" :leftIcon="Plus" :action="addRule">Add New Rule</Button>
                                </div>

                                <div v-for="(rule, rIdx) in (step.rules || [])" :key="rIdx"
                                    class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:border-indigo-300 transition-all flex flex-col">

                                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 rounded-t-2xl flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-6 h-6 bg-indigo-600 text-white text-[10px] font-bold rounded-full shadow-sm shrink-0">
                                                {{ rIdx + 1 }}
                                            </span>
                                            <input v-model="rule.label" placeholder="Rule Label (e.g., Premium Customer)"
                                                class="w-80 px-2 py-1 bg-transparent text-sm leading-6 font-medium text-slate-700 focus:ring-0 focus:outline-none border border-slate-300 rounded-lg placeholder:text-slate-400" />
                                        </div>
                                        <button @click="removeRule(rIdx)" class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-md hover:bg-white">
                                            <Trash2 size="16" />
                                        </button>
                                    </div>

                                    <div class="p-4 space-y-4 flex-1">
                                        <div v-for="(check, cIdx) in rule.checks" :key="cIdx" class="flex items-center gap-3 group">
                                            <div class="shrink-0 w-10 text-center">
                                                <span v-if="cIdx > 0" class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter">AND</span>
                                                <span v-else class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">IF</span>
                                            </div>

                                            <div class="flex-1 grid grid-cols-12 gap-2">
                                                <div :class="operatorNeedsValue(check.operator) ? 'col-span-4' : 'col-span-5'">
                                                    <Input v-model="check.variable" placeholder="balance" size="sm" hideLabel>
                                                        <template #prefix><span class="pl-2 text-indigo-400 font-bold">@</span></template>
                                                    </Input>
                                                </div>

                                                <div :class="operatorNeedsValue(check.operator) ? 'col-span-4' : 'col-span-7'">
                                                    <Select
                                                        v-model="check.operator"
                                                        @update:modelValue="(val) => handleOperatorChange(check, val)"
                                                        :options="operators"
                                                        size="sm"
                                                        hideLabel
                                                    />
                                                </div>

                                                <div v-if="operatorNeedsValue(check.operator)" class="col-span-4">
                                                    <ContentEditor
                                                        type="input"
                                                        :showHeader="false"
                                                        v-model="check.value"
                                                        class="nested-editor"
                                                        placeholder="Value or type @"
                                                    />
                                                </div>
                                            </div>

                                            <button v-if="rule.checks.length > 1" @click="removeCheck(rIdx, cIdx)" class="opacity-0 group-hover:opacity-100 p-1.5 bg-slate-100 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all duration-300 cursor-pointer"><X size="14" /></button>
                                        </div>

                                        <div class="flex justify-end">
                                            <Button size="xs" type="primary" mode="outline" :action="() => addCheck(rIdx)" buttonClass="mt-2 rounded-lg font-bold px-4">
                                                <Plus size="14" class="mr-1" /> Add Requirement
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="px-4 py-3 bg-indigo-50 border-t border-indigo-100 rounded-b-2xl flex items-center gap-4">
                                        <div class="flex items-center gap-2 text-indigo-800 shrink-0">
                                            <ArrowRightCircle size="16" />
                                            <span class="text-xs font-bold uppercase tracking-wide">Then Go To</span>
                                        </div>
                                        <div class="flex-1">
                                            <StepSelector
                                                :showLabel="false"
                                                v-model="rule.destination"
                                                placeholder="Select a destination..."
                                                @update:pendingStep="(val) => pendingRuleSteps[rIdx] = val"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-1 rounded-2xl bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200">
                                <div class="bg-white rounded-xl p-4 flex items-center justify-between gap-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                            <CornerDownRight size="20" />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800">Fallback Path</h4>
                                            <p class="text-xs text-slate-500">If <b>none</b> of the rules above match, the user will be sent here.</p>
                                        </div>
                                    </div>
                                    <div class="w-1/2">
                                        <StepSelector
                                            :showLabel="false"
                                            v-model="step.default_destination"
                                            placeholder="Select fallback step..."
                                            @update:pendingStep="(val) => pendingDefaultStep = val"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-4">
                                <div class="shrink-0 text-amber-500 mt-0.5"><Info size="20" /></div>
                                <div class="space-y-1">
                                    <p class="text-[11px] font-bold text-amber-800 uppercase tracking-tight">Logic Ordering</p>
                                    <p class="text-xs text-amber-700 leading-relaxed">
                                        Requirements <i>within</i> a rule are joined by <b>AND</b>. Rules are evaluated top-to-bottom. The first matching rule determines the path.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div v-else key="code" class="h-full space-y-6">
                            <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xl">
                                <CodeEditorWrapper
                                    type="condition_logic"
                                    height="h-[450px]"
                                    v-model="step.code_content"
                                    v-model:language="step.code_language"
                                    v-model:isFullscreen="isFullscreen"
                                    @exit="$refs.modal.hideModal()"
                                />
                            </div>
                        </div>

                    </Transition>
                </div>

                <div class="flex justify-end pt-5 border-t border-slate-100">
                    <Button type="primary" mode="solid" buttonClass="rounded-lg px-8 font-bold" :action="saveAndClose">
                        Done
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Tabs from '@Partials/Tabs.vue';
import Input from '@Partials/Input.vue';
import Select from '@Partials/Select.vue';
import Button from '@Partials/Button.vue';
import { GitBranch, Plus, Trash2, Info, X, ArrowRightCircle, CornerDownRight } from 'lucide-vue-next';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import CodeEditorWrapper from '@Pages/versions/_components/editors/CodeEditor.vue';
import StepSelector from '@Pages/versions/_components/selectors/StepSelector.vue';

export default {
    name: 'DecisionPointModal',
    components: { Modal, Tabs, Plus, Input, Select, Button, Trash2, Info, X, GitBranch, ArrowRightCircle, CornerDownRight, ContentEditor, CodeEditorWrapper, StepSelector },
    inject: ['versionState'],
    data() {
        return {
            Plus,
            isFullscreen: false,
            pendingRuleSteps: {},
            pendingDefaultStep: null,
            operatorDefaults: {
                'contains': 'GOLD', 'not_contains': 'BLOCKED', 'starts_with': '267', 'ends_with': '@gmail.com', 'is_empty': '',
                '==': 'ACTIVE', '!=': 'INACTIVE', '>': '100.00', '<': '10', '>=': '18', '<=': '5',
                'in_list': '1, 2, 5', 'not_in_list': 'admin, test', 'regex': '^[0-9]{4}$', 'is_true': '', 'is_false': ''
            },
            operators: [
                { label: '--- Text Patterns ---', value: 'header', disabled: true },
                { label: 'Contains', value: 'contains' },
                { label: 'Does Not Contain', value: 'not_contains' },
                { label: 'Starts With', value: 'starts_with' },
                { label: 'Ends With', value: 'ends_with' },
                { label: 'Is Empty', value: 'is_empty' },
                { label: '--- Numbers & Math ---', value: 'header', disabled: true },
                { label: 'Equals (=)', value: '==' },
                { label: 'Not Equals (!=)', value: '!=' },
                { label: 'Greater Than (>)', value: '>' },
                { label: 'Less Than (<)', value: '<' },
                { label: 'Greater or Equal (>=)', value: '>=' },
                { label: 'Less or Equal (<=)', value: '<=' },
                { label: '--- Grouping ---', value: 'header', disabled: true },
                { label: 'In List (comma separated)', value: 'in_list' },
                { label: 'Not In List', value: 'not_in_list' },
                { label: '--- Logic & Flags ---', value: 'header', disabled: true },
                { label: 'Matches Pattern (Regex)', value: 'regex' },
                { label: 'Is Truthy (True/1/Yes)', value: 'is_true' },
                { label: 'Is Falsy (False/0/No)', value: 'is_false' }
            ]
        }
    },
    computed: {
        step() {
            const currentStep = this.versionState.currentStep;
            if (currentStep) {
                if (!currentStep.source) currentStep.source = 'manual';
                if (!currentStep.code_language) currentStep.code_language = 'php';
                if (currentStep.code_content === undefined) currentStep.code_content = "<?php\n\n// Return the ID of the next step\nreturn $variables['balance'] > 100 ? 'SUCCESS_STEP_ID' : 'FAIL_STEP_ID';";
                if (currentStep.default_destination === undefined) currentStep.default_destination = null;
            }
            return currentStep;
        }
    },
    methods: {
        operatorNeedsValue(operator) {
            return !['is_empty', 'is_true', 'is_false'].includes(operator);
        },
        handleOperatorChange(check, operator) {
            const currentDefaults = Object.values(this.operatorDefaults);
            const isDefaultOrEmpty = !check.value || check.value.trim() === '' || check.value === 'value' || currentDefaults.includes(check.value);
            if (isDefaultOrEmpty) check.value = this.operatorDefaults[operator] ?? '';
            if (!this.operatorNeedsValue(operator)) check.value = '';
        },
        addRule() {
            if (!this.step.rules) this.step.rules = [];
            this.step.rules.push({ label: '', checks: [{ variable: 'status', operator: '==', value: 'ACTIVE' }], destination: null });
        },
        addCheck(ruleIdx) {
            this.step.rules[ruleIdx].checks.push({ variable: 'status', operator: '==', value: 'ACTIVE' });
        },
        removeRule(idx) { this.step.rules.splice(idx, 1); },
        removeCheck(ruleIdx, checkIdx) { this.step.rules[ruleIdx].checks.splice(checkIdx, 1); },
        saveAndClose() {
            const state = this.versionState;
            const parentStep = this.step;
            const [startX, startY] = parentStep.position || [0, 0];
            let createdCount = 0;

            // 1. Process Rule Destinations
            if (parentStep.rules) {
                parentStep.rules.forEach((rule, idx) => {
                    const pending = this.pendingRuleSteps[idx];
                    if (rule.destination === '__pending_new__' && pending?.isPending && pending?.name) {
                        const pos = { x: startX + 450, y: startY + (createdCount * 180) - 100 };
                        const newId = state.addInteractiveScreenStep(pending.name, { position: pos, autoLayout: false });
                        rule.destination = newId;
                        createdCount++;
                    }
                });
            }

            // 2. Process Default Destination
            if (parentStep.default_destination === '__pending_new__' && this.pendingDefaultStep?.isPending && this.pendingDefaultStep?.name) {
                const pos = { x: startX + 450, y: startY + (createdCount * 180) - 100 };
                const newId = state.addInteractiveScreenStep(this.pendingDefaultStep.name, { position: pos, autoLayout: false });
                parentStep.default_destination = newId;
                createdCount++;
            }

            // 3. Trigger Auto-Layout if we added nodes
            if (createdCount > 0) {
                setTimeout(() => { state.autoLayoutNodes(); }, 300);
            }

            // 4. Cleanup and Close
            this.pendingRuleSteps = {};
            this.pendingDefaultStep = null;
            this.$refs.modal.hideModal();
        }
    }
}
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

:deep(.nested-editor .editor-base) {
    border-color: #cbd5e1 !important;
    box-shadow: none !important;
}
:deep(.nested-editor .editor-base:focus) {
    border-color: #6366f1 !important;
    background-color: #fff !important;
}
</style>
