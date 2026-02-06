<template>
    <Modal
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false"
        :fullscreen="isFullscreen"
        :size="viewMode !== 'editor' || effectiveFeature.source === 'code' ? 'lg' : 'md'"
        :onDismiss="() => isFullscreen ? isFullscreen = false : versionState.selectFeatureModal?.hideModal()">
        <template #content>
            <div v-if="effectiveFeature" @keydown.esc="isFullscreen = false" class="flex flex-col h-full min-h-[550px]">

                <div class="flex items-center justify-between border-b border-gray-300 border-dashed pb-4 mb-6 pr-16">
                    <div class="flex items-center gap-3">
                        <button
                            v-if="viewMode !== 'editor'"
                            @click="navigateBack"
                            class="p-2 -ml-2 hover:bg-slate-100 rounded-full transition-colors cursor-pointer text-slate-500"
                        >
                            <ChevronLeft size="20" />
                        </button>
                        <div>
                            <p class="text-lg font-bold leading-tight">
                                {{ headerTitle }}
                            </p>
                            <p v-if="viewMode !== 'editor'" class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mt-0.5">
                                {{ headerSubtitle }}
                            </p>
                        </div>
                    </div>

                    <Tabs
                        v-if="viewMode === 'editor'"
                        :modelValue="effectiveFeature.source"
                        @update:modelValue="handleSourceChange"
                        design="1"
                        size="sm"
                        :tabs="[
                            { label: 'Static List', value: 'list' },
                            { label: 'Dynamic Code', value: 'code' }
                        ]"
                    />
                </div>

                <transition name="fade-slide" mode="out-in">

                    <div v-if="viewMode === 'categories'" key="categories" class="h-full space-y-6">
                        <div class="px-1">
                            <p class="text-sm text-slate-500">Choose your industry to see tailored menu structures and best-practice flows.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pb-8">
                            <button
                                v-for="(category, cIdx) in templateCategories"
                                :key="cIdx"
                                @click="selectCategory(category)"
                                class="group/cat flex items-center gap-5 bg-white border border-slate-200 rounded-2xl p-6 hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 cursor-pointer"
                            >
                                <div class="w-14 h-14 shrink-0 bg-slate-50 rounded-2xl flex items-center justify-center group-hover/cat:bg-indigo-200 group-hover/cat:text-indigo-700 transition-all duration-500 group-hover/cat:rotate-6">
                                    <component :is="category.icon" size="28" />
                                </div>
                                <div class="text-left min-w-0">
                                    <h4 class="font-bold text-slate-900 text-base mb-0.5">{{ category.name }}</h4>
                                    <p class="text-xs text-slate-500 truncate">{{ category.templates.length }} template flows</p>
                                </div>
                                <ChevronRight size="18" class="ml-auto text-slate-300 group-hover/cat:text-indigo-400 group-hover/cat:translate-x-1 transition-all" />
                            </button>
                        </div>
                    </div>

                    <div v-else-if="viewMode === 'templates'" key="templates" class="h-full space-y-6">
                        <div class="flex items-center justify-between px-1">
                            <p class="text-sm text-slate-500 italic">Showing menu examples for <span class="text-indigo-600 font-bold">{{ selectedCategory?.name }}</span></p>
                            <label class="flex items-center gap-2 cursor-pointer group bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                                <input type="checkbox" v-model="autoCreateScreens" class="w-3.5 h-3.5 rounded text-indigo-600 border-indigo-300 focus:ring-indigo-500">
                                <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Auto-create linked screens</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pb-8 max-h-[450px] overflow-y-auto pr-2">
                            <button
                                v-for="(tmpl, tIdx) in selectedCategory?.templates"
                                :key="tIdx"
                                @click="applyTemplate(tmpl)"
                                class="group/card flex flex-col text-left bg-white border border-slate-200 rounded-2xl p-5 hover:border-indigo-400 hover:shadow-xl transition-all duration-300 cursor-pointer"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-black text-slate-900 text-sm uppercase tracking-tight">{{ tmpl.name }}</h4>
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-bold text-slate-500 group-hover/card:bg-indigo-100 group-hover/card:text-indigo-600 transition-colors">
                                        {{ tmpl.options.length }} OPTIONS
                                    </span>
                                </div>

                                <div class="space-y-2 border-l-2 border-slate-100 pl-4 group-hover/card:border-indigo-200 transition-colors">
                                    <div v-for="(opt, oIdx) in tmpl.options.slice(0, 4)" :key="oIdx" class="text-[11px] text-slate-500 flex items-center gap-2">
                                        <span class="font-bold text-slate-300 group-hover/card:text-indigo-400 shrink-0 min-w-[14px]">{{ oIdx + 1 }}.</span>
                                        <span class="truncate">{{ opt }}</span>
                                    </div>
                                    <p v-if="tmpl.options.length > 4" class="text-[10px] text-slate-400 italic font-medium pl-5">+ {{ tmpl.options.length - 4 }} more items</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div v-else key="editor" class="space-y-4">
                        <transition name="fade-slide" mode="out-in">
                            <div v-if="isNewSelectWithoutContent && showInstructionField" class="relative shrink-0 w-full rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-slate-900">Instruction <span class="text-xs text-slate-400 font-normal">(Optional)</span></p>
                                    <button @click="showInstructionField = false" class="p-1 text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"><X size="14" /></button>
                                </div>
                                <ContentEditor v-model="instruction" :fullWidth="true" :showHeader="false" />
                            </div>
                            <div v-else-if="isNewSelectWithoutContent && !showInstructionField" class="shrink-0">
                                <button @click="showInstructionField = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 cursor-pointer">+ Add Instruction</button>
                            </div>
                        </transition>

                        <div v-if="effectiveFeature.source === 'list'">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-wide">Menu Options</p>
                                    <div class="flex items-center gap-2">
                                        <button @click="viewMode = 'categories'" class="text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-sm flex items-center gap-1 cursor-pointer hover:scale-105 transition-all duration-300">
                                            <LayoutTemplate size="14" /> Browse Templates
                                        </button>
                                        <div class="w-px h-3 bg-slate-200 mx-1"></div>
                                        <button @click="addOption" class="text-[11px] font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1 cursor-pointer hover:scale-105 transition-all duration-300">
                                            <Plus size="14" /> Add Option
                                        </button>
                                    </div>
                                </div>

                                <div v-if="!effectiveOptions.length" class="text-center py-10 border-2 border-dashed border-slate-200 rounded-lg bg-white/50">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                                            <ListFilter size="24" />
                                        </div>
                                        <p class="text-sm text-slate-500">Your menu is empty.</p>
                                        <div class="flex gap-2">
                                            <Button size="sm" type="primary" :action="addOption" buttonClass="rounded-lg">Start from Scratch</Button>
                                            <Button size="sm" type="light" mode="outline" :action="() => viewMode = 'categories'" buttonClass="rounded-lg">Browse Templates</Button>
                                        </div>
                                    </div>
                                </div>
                                <draggable v-else v-model="effectiveFeature.options" handle=".drag-handle" ghost-class="opacity-50" class="space-y-2">
                                    <div v-for="(option, index) in effectiveOptions" :key="index" class="group flex flex-col gap-2 p-3 rounded-lg border border-slate-200 bg-white hover:border-indigo-200 transition-all shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center justify-center h-6 w-6 shrink-0 rounded bg-slate-100 text-[10px] font-black text-slate-500">{{ index + 1 }}</div>
                                            <Input placeholder="Label (e.g. Check Balance)" v-model="option.label" class="flex-1" size="sm" />
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button @click="removeOption(index)" class="opacity-0 group-hover:opacity-100 p-1.5 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all cursor-pointer"><Trash2 size="14" /></button>
                                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-300 hover:text-slate-500 transition-colors"><GripVertical size="14" /></div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2 pt-2 border-t border-slate-100/60">
                                            <div class="flex-1 min-w-0">
                                                <transition name="morph" mode="out-in">
                                                    <div :key="option.is_terminal" class="h-10">
                                                        <StepSelector
                                                            size="sm"
                                                            :showLabel="false"
                                                            v-if="!option.is_terminal"
                                                            v-model="option.next_step_id"
                                                            @update:pendingStep="(val) => pendingStepsMap[index] = val"
                                                        />
                                                        <div v-else class="group/terminal relative h-8 flex items-center justify-between px-3 bg-amber-50/50 border border-amber-200/60 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <div class="relative flex h-2 w-2">
                                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                                </div>
                                                                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">End Session</span>
                                                            </div>
                                                            <button @click.stop="toggleOptionTerminal(option)" class="opacity-0 group-hover/terminal:opacity-100 p-1 text-amber-400 hover:text-amber-600 cursor-pointer">
                                                                <X size="12" stroke-width="3" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </transition>
                                            </div>
                                            <div class="shrink-0">
                                                <button @click="toggleOptionTerminal(option)" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-slate-300 text-slate-400 hover:border-indigo-300 hover:text-indigo-600 cursor-pointer">
                                                    <component :is="option.is_terminal ? 'Play' : 'LogOut'" size="14" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </draggable>
                            </div>
                        </div>

                        <div v-else class="space-y-3">
                            <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-xl min-h-[360px]">
                                <CodeEditorWrapper
                                    type="select_feature"
                                    height="h-[520px]"
                                    v-model="effectiveFeature.content"
                                    v-model:language="effectiveFeature.language"
                                    v-model:isFullscreen="isFullscreen"
                                    @exit="isFullscreen = false"
                                />
                            </div>
                        </div>

                        <Input label="Save selection as" placeholder="e.g. choice" :model-value="effectiveFeature.variable" @update:model-value="val => effectiveFeature.variable = toCamelCase(val)">
                            <template #prefix>
                                <span class="text-sm font-black text-indigo-500 pr-2 border-r border-slate-300 pl-2.5">@</span>
                            </template>
                        </Input>
                    </div>
                </transition>

                <div v-if="viewMode === 'editor'" class="mt-8 flex justify-end space-x-3">
                    <button @click="onCancel" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <Button size="md" type="primary" mode="solid" :action="onApply" :disabled="!canApply" buttonClass="rounded-lg shadow-md">Done</Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Tabs from '@Partials/Tabs.vue';
import Input from '@Partials/Input.vue';
import Button from '@Partials/Button.vue';
import Tooltip from '@Partials/Tooltip.vue';
import CodeEditorWrapper from '@Pages/versions/_components/editors/CodeEditor.vue';
import { toCamelCase } from '@Utils/stringUtils';
import { VueDraggableNext } from 'vue-draggable-next';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import StepSelector from '@Pages/versions/_components/selectors/StepSelector.vue';
import {
    X, Play, Plus, Trash2, GripVertical, Terminal, ChevronLeft, ChevronRight,
    Maximize2, Minimize2, LogOut, LayoutTemplate, ListFilter, CreditCard,
    Smartphone, CheckCircle2, Languages, Settings2, HelpCircle, Briefcase, ShoppingBag, Wallet
} from 'lucide-vue-next';

export default {
    name: 'SelectFeatureModal',
    components: { Modal, Tabs, Button, Input, Tooltip, draggable: VueDraggableNext, GripVertical, ContentEditor, StepSelector, CodeEditorWrapper, X, Play, Plus, Trash2, Terminal, ChevronLeft, ChevronRight, Maximize2, Minimize2, LogOut, LayoutTemplate, ListFilter },
    inject: ['versionState'],
    data() {
        return {
            Plus,
            instruction: '',
            pendingStepsMap: {},
            showInstructionField: false,
            isFullscreen: false,
            autoCreateScreens: true,
            viewMode: 'editor', // 'editor' | 'categories' | 'templates'
            selectedCategory: null,
            draftFeature: {
                type: 'select',
                source: 'list',
                variable: 'choice',
                language: 'php',
                content: '',
                options: [{ label: '', next_step_id: null, is_terminal: false }],
            },
            templateCategories: [
                {
                    name: 'Mobile Money',
                    icon: Wallet,
                    templates: [
                        { name: 'Main Transaction Menu', instruction: 'Welcome to Mobile Money. What would you like to do?', options: ['Send Money', 'Withdraw Cash', 'Buy Airtime', 'Pay Bill', 'My Account'] },
                        { name: 'Send Money Options', instruction: 'Select the recipient type for your transfer:', options: ['To Registered User', 'To Unregistered (Voucher)', 'To Bank Account', 'International Transfer'] },
                        { name: 'Cash Out / Withdrawal', instruction: 'Choose your preferred cash-out method:', options: ['At Authorized Agent', 'At ATM', 'Voucher Cashout'] },
                        { name: 'Merchant & Payments', instruction: 'Select payment category:', options: ['Pay Merchant (Till/QR)', 'Pay Utility Bill', 'School Fees', 'Government Services'] }
                    ]
                },
                {
                    name: 'Banking & Finance',
                    icon: CreditCard,
                    templates: [
                        { name: 'Main Menu', instruction: 'Welcome to Mobile Banking. Select a service:', options: ['Account Balance', 'Transfer Funds', 'Mini Statement', 'Pay Bills', 'PIN Services'] },
                        { name: 'Transfer Options', instruction: 'Choose transfer destination:', options: ['To Own Account', 'To Registered Proxy', 'To Other Bank', 'Inter-wallet Transfer'] },
                        { name: 'Loan Services', instruction: 'Finance & Loans Menu:', options: ['Apply for Loan', 'Check Eligibility', 'Outstanding Balance', 'Repay Loan'] }
                    ]
                },
                {
                    name: 'Telecommunications',
                    icon: Smartphone,
                    templates: [
                        { name: 'Bundle Purchase', instruction: 'Select a data or voice bundle:', options: ['Daily Data', 'Weekly Data', 'Monthly Data', 'Social Media Packs', 'Voice Bundles'] },
                        { name: 'SIM Services', instruction: 'Manage your SIM card settings:', options: ['SIM Swap', 'Check My Number', 'PUK Request', 'Roaming Settings'] },
                        { name: 'Value Added Services', options: ['Caller Tunes', 'News Alerts', 'Sports Updates', 'Horoscope'] }
                    ]
                },
                {
                    name: 'Retail & E-Commerce',
                    icon: ShoppingBag,
                    templates: [
                        { name: 'Order Management', options: ['Track Order', 'Cancel Order', 'Refund Request', 'View History'] },
                        { name: 'Promotions', instruction: 'Available Rewards & Deals:', options: ['Current Deals', 'Redeem Voucher', 'Loyalty Points', 'Spin & Win'] }
                    ]
                },
                {
                    name: 'General UI',
                    icon: LayoutTemplate,
                    templates: [
                        { name: 'Confirmation Flow', instruction: 'Are you sure you want to proceed?', options: ['Yes, Proceed', 'No, Go Back', 'Cancel Transaction'] },
                        { name: 'Language Picker', instruction: 'Select Language / Tlhopha Puo:', options: ['English', 'Setswana', 'French', 'Swahili', 'Portuguese'] },
                        { name: 'Support Menu', options: ['Talk to Agent', 'FAQs', 'Location Finder', 'Operating Hours'] }
                    ]
                }
            ]
        };
    },
    computed: {
        isEditing() { return !!this.versionState.currentFeatureId; },
        isNewSelectWithoutContent() { return !this.isEditing && !this.versionState.currentStepHasContentFeature; },
        effectiveFeature() { return this.isEditing ? this.versionState.currentFeature : this.draftFeature; },
        effectiveOptions() { return this.effectiveFeature?.options || []; },
        canApply() {
            const feat = this.effectiveFeature;
            if (!feat || !(feat.variable || '').trim()) return false;
            if (feat.source === 'code') return (feat.content || '').trim().length > 2;
            return this.effectiveOptions.length > 0 && this.effectiveOptions.every(opt => (opt.label || '').trim() !== '');
        },
        headerTitle() {
            if (this.viewMode === 'categories') return 'Industry Templates';
            if (this.viewMode === 'templates') return this.selectedCategory?.name || 'Templates';
            return 'Menu Selection';
        },
        headerSubtitle() {
            if (this.viewMode === 'categories') return 'Choose a category';
            if (this.viewMode === 'templates') return 'Choose a flow example';
            return null;
        }
    },
    methods: {
        toCamelCase,
        handleSourceChange(val) { this.effectiveFeature.source = val; },
        selectCategory(category) {
            this.selectedCategory = category;
            this.viewMode = 'templates';
        },
        navigateBack() {
            if (this.viewMode === 'templates') this.viewMode = 'categories';
            else if (this.viewMode === 'categories') this.viewMode = 'editor';
        },
        applyTemplate(template) {
            this.pendingStepsMap = {};

            // Auto-populate instruction if the template provides one
            if (template.instruction) {
                this.instruction = template.instruction;
                this.showInstructionField = true;
            } else {
                this.instruction = '';
                this.showInstructionField = false;
            }

            this.effectiveFeature.options = template.options.map((label, index) => {
                let nextStepId = null;
                if (this.autoCreateScreens) {
                    nextStepId = '__pending_new__';
                    this.pendingStepsMap[index] = { isPending: true, name: label };
                }
                return { label: label, next_step_id: nextStepId, is_terminal: false };
            });
            this.viewMode = 'editor';
        },
        reset() {
            this.instruction = '';
            this.pendingStepsMap = {};
            this.isFullscreen = false;
            this.showInstructionField = false;
            this.viewMode = 'editor';
            this.selectedCategory = null;
            this.draftFeature = {
                type: 'select', source: 'list', variable: 'choice', language: 'php', content: '',
                options: [{ label: '', next_step_id: null, is_terminal: false }]
            };
        },
        addOption() { this.effectiveOptions.push({ label: '', next_step_id: null, is_terminal: false }); },
        removeOption(index) { this.effectiveOptions.splice(index, 1); },
        toggleOptionTerminal(option) {
            option.is_terminal = !option.is_terminal;
            if (option.is_terminal) option.next_step_id = null;
        },
        onCancel() { this.reset(); this.versionState.selectFeatureModal?.hideModal(); },
        onApply() {
    const state = this.versionState;
    const feat = this.effectiveFeature;

    if (feat.source === 'list') {
        const itemsToCreate = feat.options
            .map((opt, idx) => ({ opt, idx, pending: this.pendingStepsMap[idx] }))
            .filter(item => item.opt.next_step_id === '__pending_new__' && item.pending?.isPending);

        if (itemsToCreate.length > 0) {
            // We can place them roughly near the parent initially so they don't jump wildly
            const [currentX, currentY] = state.currentStep?.position || [0, 0];

            itemsToCreate.forEach((item, index) => {
                // Temporary positions - the AutoLayout will fix them in 300ms
                const options = {
                    position: { x: currentX + 400, y: currentY + (index * 200) },
                    autoLayout: false // DEFER layout until loop finishes
                };
                const newId = state.addInteractiveScreenStep(item.pending.name, options, false);
                item.opt.next_step_id = newId;
            });
        }
    }

    if (!this.isEditing) {
        if (this.showInstructionField && this.instruction.trim()) {
            state.addBasicContentFeature({ content: this.instruction.trim() });
        }
        state.clearFeaturesByType(state.currentStep, ['input', 'select']);
        state.addFeature('select', { ...this.draftFeature });
    }

    state.selectFeatureModal?.hideModal();

    // Trigger ONE global reflow that accounts for the new heights
    setTimeout(() => {
        this.reset();
        state.setCurrentFeatureId(null);
        state.autoLayoutNodes();
    }, 300);
}
    }
};
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
