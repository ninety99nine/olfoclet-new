<template>
    <Modal
        size="md"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">

        <template #content>

            <div class="flex flex-col h-full min-h-[550px] select-none">
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-6 pt-2">
                    <div>
                        <p class="text-lg font-bold text-slate-900">Input Feature</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                            {{ activeTab === 'templates' ? 'Browse Samples' : 'Configure Details' }}
                        </p>
                    </div>

                    <div class="mr-20">
                        <Tabs
                            design="1"
                            v-model="activeTab"
                            :tabs="[
                                { label: 'Custom', value: 'form' },
                                { label: 'Templates', value: 'templates' }
                            ]"
                        />
                    </div>
                </div>

                <Transition name="fade-slide" mode="out-in">

                    <div v-if="activeTab === 'templates'" key="templates" class="flex flex-col h-full">
                        <div class="mb-4 flex items-center justify-between h-8">
                            <Transition name="fade" mode="out-in">
                                <button v-if="selectedCategory" @click="selectedCategory = null" class="flex items-center gap-1 text-xs font-bold text-indigo-600 cursor-pointer hover:underline">
                                    <ChevronLeft size="14" /> All Categories
                                </button>
                                <p v-else class="text-xs font-medium text-slate-500">Choose a category to find a starting point</p>
                            </Transition>
                        </div>

                        <div class="relative flex-1">
                            <Transition name="fade" mode="out-in">
                                <div v-if="!selectedCategory" key="categories" class="grid grid-cols-1 gap-3">
                                    <button
                                        v-for="(cat, key) in categories"
                                        :key="key"
                                        @click="selectedCategory = key"
                                        class="flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-white hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 flex items-center justify-center transition-colors">
                                                <component :is="cat.icon" size="20" />
                                            </div>
                                            <div class="text-left">
                                                <p class="font-bold text-slate-700 group-hover:text-indigo-900">{{ cat.label }}</p>
                                                <p class="text-[10px] text-slate-400">{{ cat.templates.length }} pre-defined samples</p>
                                            </div>
                                        </div>
                                        <ChevronRight size="18" class="text-slate-300 group-hover:text-indigo-500" />
                                    </button>
                                </div>

                                <div v-else key="samples" class="space-y-4">
                                    <div
                                        v-for="(t, idx) in categories[selectedCategory].templates"
                                        :key="idx"
                                        @click="applyTemplate(t)"
                                        class="flex items-start justify-between p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-lg transition-all cursor-pointer group"
                                    >
                                        <div class="min-w-0 pr-4">
                                            <p class="text-sm font-bold text-slate-900 group-hover:text-emerald-700">@{{ t.variable }}</p>
                                            <p class="text-xs text-slate-500 mt-1 italic">"{{ t.instruction }}"</p>
                                            <div class="mt-3 flex gap-1.5">
                                                <span v-for="rule in t.rules" :key="rule.type" class="text-[9px] font-black uppercase tracking-tighter bg-slate-100 px-1.5 py-0.5 rounded text-slate-500">
                                                    {{ rule.type }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                            <Check size="16" />
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <div v-else key="form" class="flex flex-col h-full">
                        <div class="flex-1 space-y-6 pb-10">
                            <div v-if="isNewInputWithoutContent" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                <p class="text-sm font-semibold text-slate-900">Instruction Text <span class="text-rose-600">*</span></p>
                                <ContentEditor v-model="instruction" :fullWidth="true" :showHeader="false" />
                            </div>

                            <div class="space-y-4">
                                <Input
                                    class="flex-1"
                                    label="Variable Name"
                                    placeholder="e.g. accountNo"
                                    :model-value="effectiveFeature.variable"
                                    @update:model-value="val => effectiveFeature.variable = toCamelCase(val)">
                                    <template #prefix>
                                        <span class="text-sm font-black text-indigo-500 pr-2 border-r border-slate-300 pl-2.5">@</span>
                                    </template>
                                </Input>
                            </div>

                            <StepSelector v-model="effectiveFeature.next_step_id" @update:pendingStep="syncPendingStepState" />

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <p class="text-sm font-semibold text-slate-900">Validation Rules</p>
                                    <Button size="xs" type="primary" mode="outline" :action="addRule" buttonClass="rounded-lg">Add Rule</Button>
                                </div>

                                <div v-if="!effectiveRules.length" class="text-xs text-slate-400 italic py-2">No validation rules applied.</div>
                                <div v-else class="space-y-3">
                                    <div v-for="(entry, index) in effectiveRules" :key="entry.vid" class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                                        <button type="button" class="w-full px-3 py-2.5 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors cursor-pointer" @click="toggleRule(index)">
                                            <div class="min-w-0 text-left">
                                                <p class="text-sm font-semibold text-slate-900 truncate">{{ versionState.getValidationRuleLabel(entry.rule.type) }}</p>
                                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ versionState.getValidationRuleSummary(entry.rule) }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <ChevronDown size="14" :class="['text-slate-400 transition-transform duration-300', { 'rotate-180': expandedIndex === index }]" />
                                                <button @click.stop="removeRule(entry.vid, index)" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors cursor-pointer outline-none">
                                                    <Trash2 size="14" />
                                                </button>
                                            </div>
                                        </button>

                                        <vue-slide-up-down :active="expandedIndex === index" :duration="300">
                                            <div class="px-3 pb-4 space-y-4 border-t border-slate-50 pt-3 bg-slate-50/30">
                                                <Select label="Rule Type" v-model="entry.rule.type" :options="versionState.inputValidationRuleOptions" @change="() => updateRuleMessage(entry.rule)" />

                                                <div v-if="['min_length','max_length','equal_length'].includes(entry.rule.type)" class="space-y-1">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Required Length</label>
                                                    <ContentEditor
                                                        type="input"
                                                        :showHeader="false"
                                                        v-model="entry.rule.value"
                                                        placeholder="Number or @variable"
                                                        @update:modelValue="() => updateRuleMessage(entry.rule)"
                                                    />
                                                </div>

                                                <div v-else-if="['eq','neq','lt','lte','gt','gte'].includes(entry.rule.type)" class="space-y-1">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Value to Compare</label>
                                                    <ContentEditor
                                                        type="input"
                                                        :showHeader="false"
                                                        v-model="entry.rule.value"
                                                        placeholder="Value or @variable"
                                                        @update:modelValue="() => updateRuleMessage(entry.rule)"
                                                    />
                                                </div>

                                                <div v-else-if="['between_inclusive','between_exclusive'].includes(entry.rule.type)" class="grid grid-cols-2 gap-3">
                                                   <div class="space-y-1">
                                                       <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Min</label>
                                                       <ContentEditor type="input" :showHeader="false" v-model="entry.rule.min" placeholder="Min" @update:modelValue="() => updateRuleMessage(entry.rule)" />
                                                   </div>
                                                   <div class="space-y-1">
                                                       <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Max</label>
                                                       <ContentEditor type="input" :showHeader="false" v-model="entry.rule.max" placeholder="Max" @update:modelValue="() => updateRuleMessage(entry.rule)" />
                                                   </div>
                                                </div>

                                                <div v-else-if="entry.rule.type === 'regex'" class="space-y-1">
                                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Regex Pattern</label>
                                                    <ContentEditor
                                                        type="input"
                                                        :showHeader="false"
                                                        v-model="entry.rule.pattern"
                                                        placeholder="Pattern or @variable"
                                                        @update:modelValue="() => updateRuleMessage(entry.rule)"
                                                    />
                                                </div>

                                                <Input label="Error Message" v-model="entry.rule.message" placeholder="Message shown on failure" />
                                            </div>
                                        </vue-slide-up-down>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 pt-5 border-t border-slate-100">
                            <Button size="md" type="light" mode="outline" :action="onCancel" buttonClass="rounded-lg">Cancel</Button>
                            <Button size="md" type="primary" mode="solid" :action="onApply" :disabled="!canApply" buttonClass="rounded-lg px-10 font-bold">
                                {{ isNewInputWithoutContent ? 'Create Input' : 'Save Changes' }}
                            </Button>
                        </div>
                    </div>

                </Transition>
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
import Tabs from '@Partials/Tabs.vue';
import VueSlideUpDown from 'vue-slide-up-down';
import { toCamelCase } from '@Utils/stringUtils';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import StepSelector from '@Pages/versions/_components/selectors/StepSelector.vue';
import { ChevronRight, ChevronLeft, ChevronDown, ChevronUp, Sparkles, Check, Trash2, User, CreditCard, ShieldCheck } from 'lucide-vue-next';

export default {
    name: 'InputFeatureModal',
    components: {
        Modal, Button, Input, Select, Tabs, ContentEditor, StepSelector,
        ChevronRight, ChevronLeft, ChevronDown, ChevronUp, Sparkles, Check, Trash2,
        VueSlideUpDown
    },
    inject: ['versionState', 'notificationState'],
    data() {
        return {
            activeTab: 'form',
            selectedCategory: null,
            instruction: '',
            draftFeature: { type: 'input', variable: 'value', next_step_id: null, validation_rules: [] },
            expandedIndex: null,
            pendingNewStep: false,
            pendingNewStepName: null,
            categories: {
                identity: {
                    label: 'Identification',
                    icon: User,
                    templates: [
                        { label: 'First Name', variable: 'firstName', instruction: 'Enter your first name:', rules: [{ type: 'min_length', value: '3', message: 'First name too short.' }] },
                        { label: 'Last Name', variable: 'lastName', instruction: 'Enter your last name:', rules: [{ type: 'min_length', value: '3', message: 'Last name too short.' }] },
                        { label: 'Full Name', variable: 'fullName', instruction: 'Enter your full name:', rules: [{ type: 'min_length', value: '3', message: 'Name too short.' }] },
                        { label: 'ID Number', variable: 'idNumber', instruction: 'Enter ID number:', rules: [{ type: 'numeric', message: 'Digits only.' }, { type: 'equal_length', value: '9', message: 'Invalid ID.' }] }
                    ]
                },
                financial: {
                    label: 'Financials',
                    icon: CreditCard,
                    templates: [
                        { label: 'Amount', variable: 'amount', instruction: 'Enter amount:', rules: [{ type: 'numeric', message: 'Numbers only.' }, { type: 'gt', value: '0', message: 'Min 1 required.' }] },
                        { label: 'Bank Account', variable: 'accountNo', instruction: 'Enter account number:', rules: [{ type: 'numeric', message: 'Invalid format.' }, { type: 'min_length', value: '10', message: 'Too short.' }] }
                    ]
                },
                security: {
                    label: 'Security',
                    icon: ShieldCheck,
                    templates: [
                        { label: '4-Digit PIN', variable: 'pin', instruction: 'Enter secret PIN:', rules: [{ type: 'numeric', message: 'Numbers only.' }, { type: 'equal_length', value: '4', message: 'Must be 4 digits.' }] },
                        { label: 'OTP Code', variable: 'otp', instruction: 'Enter 6-digit OTP:', rules: [{ type: 'numeric', message: 'Invalid code.' }, { type: 'equal_length', value: '6', message: 'Enter 6 digits.' }] }
                    ]
                }
            }
        };
    },
    computed: {
        isNewInputWithoutContent() { return !this.versionState.currentStepHasContentFeature; },
        effectiveFeature() { return this.isNewInputWithoutContent ? this.draftFeature : this.versionState.currentFeature; },
        canApply() {
            const feat = this.effectiveFeature;
            if (!feat) return false;
            if (this.isNewInputWithoutContent && !(this.instruction || '').trim()) return false;
            if (!(feat.variable || '').trim()) return false;
            return true;
        },
        effectiveRules() {
            if (this.isNewInputWithoutContent) return this.draftFeature.validation_rules.map(rule => ({ vid: rule.id, rule }));
            const f = this.effectiveFeature;
            if (!f?.validation_rule_ids?.length) return [];
            const rules = this.versionState.validationRules || {};
            return f.validation_rule_ids.map(vid => ({ vid, rule: rules[vid] })).filter(({ rule }) => rule);
        }
    },
    methods: {
        toCamelCase,
        applyTemplate(t) {
            const target = this.effectiveFeature;
            target.variable = t.variable;
            if (this.isNewInputWithoutContent) {
                this.instruction = t.instruction;
                this.draftFeature.validation_rules = t.rules.map(r => ({ id: uuidv4(), ...r }));
            } else {
                this.versionState.clearValidationRules(this.versionState.currentFeatureId);
                t.rules.forEach(r => this.versionState.addValidationRule(this.versionState.currentFeatureId, r));
            }
            this.activeTab = 'form';
            this.notificationState.showSuccessNotification(`Loaded ${t.label} template.`);
        },
        reset() {
            this.activeTab = 'form';
            this.selectedCategory = null;
            this.instruction = '';
            this.expandedIndex = null;
            this.draftFeature = { type: 'input', variable: 'value', next_step_id: null, validation_rules: [] };
        },
        syncPendingStepState(state) { this.pendingNewStep = state.isPending; this.pendingNewStepName = state.name; },
        addRule() {
            if (this.isNewInputWithoutContent) {
                const rule = { id: uuidv4(), type: 'alpha', message: '' };
                rule.message = this.versionState.getValidationRuleDefaultMessage(rule);
                this.draftFeature.validation_rules.push(rule);
                this.expandedIndex = this.draftFeature.validation_rules.length - 1;
                return;
            }
            this.versionState.addValidationRule(this.versionState.currentFeatureId);
            this.expandedIndex = this.effectiveRules.length - 1;
        },
        removeRule(vid, index) {
            if (this.isNewInputWithoutContent) { this.draftFeature.validation_rules.splice(index, 1); return; }
            this.versionState.removeValidationRule(this.versionState.currentFeatureId, vid);
            if (this.expandedIndex === index) this.expandedIndex = null;
        },
        toggleRule(index) {
            this.expandedIndex = this.expandedIndex === index ? null : index;
        },
        updateRuleMessage(rule) {
            rule.message = this.versionState.getValidationRuleDefaultMessage(rule);
        },
        onCancel() { this.reset(); this.versionState.inputFeatureModal?.hideModal(); },
        onApply() {
            const state = this.versionState;
            let nextStepId = this.effectiveFeature.next_step_id;
            if (this.pendingNewStep) nextStepId = state.addInteractiveScreenStep({ name: this.pendingNewStepName });

            if (this.isNewInputWithoutContent) {
                state.addBasicContentFeature({ content: this.instruction.trim() });
                const vids = this.draftFeature.validation_rules.map(rule => {
                    state.builder.validation_rules[rule.id] = rule;
                    return rule.id;
                });
                state.addInputFeature({ variable: this.draftFeature.variable.trim(), next_step_id: nextStepId, validation_rule_ids: vids });
            } else {
                this.effectiveFeature.next_step_id = nextStepId;
            }
            setTimeout(() => { this.reset(); state.setCurrentFeatureId(null); }, 300);
            state.inputFeatureModal?.hideModal();
        }
    }
};
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.25s ease-out;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
