<template>
    <Modal
        size="md"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false"
        :fullscreen="isFullscreen"
        :onDismiss="() => isFullscreen ? isFullscreen = false : versionState.selectFeatureModal?.hideModal()">
        <template #content>
            <div v-if="effectiveFeature" @keydown.esc="isFullscreen = false">
                <div class="flex items-center justify-between border-b border-gray-300 border-dashed pb-4 mb-6 pr-16">
                    <p class="text-lg font-bold">Menu Selection</p>
                    <Tabs
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

                <div class="space-y-4">
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

                    <transition name="fade-slide" mode="out-in">
                        <div :key="effectiveFeature.source">
                            <div v-if="effectiveFeature.source === 'list'" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-bold text-slate-900 uppercase tracking-wide">Menu Options</p>
                                    <button @click="addOption" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition-colors cursor-pointer">
                                        <Plus size="14" /> Add Option
                                    </button>
                                </div>
                                <div v-if="!effectiveOptions.length" class="text-center py-6 border-2 border-dashed border-slate-200 rounded-lg bg-white/50">
                                    <p class="text-[11px] text-slate-400">No options defined.</p>
                                </div>
                                <draggable v-else v-model="effectiveFeature.options" handle=".drag-handle" ghost-class="opacity-50" class="space-y-2">
                                    <div v-for="(option, index) in effectiveOptions" :key="index" class="group flex items-center gap-2 p-1.5 pr-2 rounded-lg border border-slate-200 bg-white hover:border-indigo-200 transition-all shadow-sm">
                                        <div class="flex items-center justify-center h-6 w-6 rounded bg-slate-100 text-[10px] font-black text-slate-500">{{ index + 1 }}</div>
                                        <div class="flex-1 flex items-center gap-2">
                                            <Input placeholder="Label..." v-model="option.label" class="flex-1" size="sm" />
                                            <div class="h-4 w-px bg-slate-200 shrink-0"></div>
                                            <StepSelector size="sm" class="flex-1" :showLabel="false" v-model="option.next_step_id" />
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button @click="removeOption(index)" class="opacity-0 group-hover:opacity-100 p-1.5 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-all cursor-pointer"><Trash2 size="14" /></button>
                                            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-slate-300 hover:text-slate-500 transition-colors"><GripVertical size="14" /></div>
                                        </div>
                                    </div>
                                </draggable>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-if="!isFullscreen" class="bg-slate-950 rounded-xl border border-slate-800 shadow-lg flex flex-col overflow-hidden transition-all duration-300 h-[400px]">
                                    <div class="flex items-center justify-between bg-slate-900 px-4 py-2 border-b border-slate-800 shrink-0">
                                        <div class="flex gap-4">
                                            <button @click="activeCodeView = 'editor'" :class="['text-[10px] font-bold uppercase tracking-widest cursor-pointer', activeCodeView === 'editor' ? 'text-amber-400' : 'text-slate-500 hover:text-slate-300']">Editor</button>
                                            <button @click="activeCodeView = 'guide'" :class="['text-[10px] font-bold uppercase tracking-widest cursor-pointer', activeCodeView === 'guide' ? 'text-amber-400' : 'text-slate-500 hover:text-slate-300']">Guide</button>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-1 bg-slate-800 px-3 py-1 rounded">
                                                <Terminal size="14" class="text-slate-500" />
                                                <select v-model="effectiveFeature.language" @change="syncSampleCode" class="bg-transparent text-[10px] font-bold text-white border-none outline-none cursor-pointer pr-2">
                                                    <option value="php">PHP</option>
                                                    <option value="javascript">JS</option>
                                                    <option value="python">PY</option>
                                                </select>
                                            </div>
                                            <select v-model="fontSize" class="bg-slate-800 text-[10px] font-bold text-white px-3 py-1 rounded border-none cursor-pointer outline-none">
                                                <option v-for="s in [10, 12, 14, 16, 18, 20]" :key="s" :value="s">{{ s }}px</option>
                                            </select>
                                            <button @click="isFullscreen = true" class="p-1.5 hover:bg-slate-800 rounded text-slate-400 hover:text-white transition-colors cursor-pointer">
                                                <Maximize2 size="14" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex-1 overflow-hidden">
                                        <transition name="fade-fast" mode="out-in">
                                            <div v-if="activeCodeView === 'editor'" key="inline-editor" class="h-full">
                                                <CodeEditor v-model="effectiveFeature.content" :language="effectiveFeature.language" :options="{ fontSize: fontSize }" />
                                            </div>
                                            <div v-else key="inline-guide" class="p-5 space-y-4 h-full overflow-y-auto bg-slate-950">
                                                <div class="space-y-4">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <button @click="prevTemplate" class="p-1 hover:bg-slate-800 rounded text-slate-400 cursor-pointer"><ChevronLeft size="16" /></button>
                                                            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter">{{ currentTemplate.title }}</span>
                                                            <button @click="nextTemplate" class="p-1 hover:bg-slate-800 rounded text-slate-400 cursor-pointer"><ChevronRight size="16" /></button>
                                                        </div>
                                                        <button @click="useCodeTemplate" class="text-[11px] font-bold text-slate-400 hover:text-amber-400 hover:underline cursor-pointer">Use Template</button>
                                                    </div>
                                                    <div class="relative bg-slate-900/50 border border-slate-800 rounded-lg p-3">
                                                        <pre class="m-0 text-[10px] font-mono text-indigo-200/80 overflow-auto max-h-[150px]">{{ currentTemplate.code }}</pre>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 leading-relaxed border-l-2 border-amber-500/50 pl-3">
                                                        <p class="text-slate-600 text-xs">{{ currentTemplate.description }}</p>
                                                        <p>Return an <span class="text-slate-200">Array</span> of objects with <code class="text-indigo-400">label</code>, <code class="text-indigo-400">trigger</code>, and <code class="text-indigo-400">next_step_id</code>.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>

                                <Teleport to="body">
                                    <transition name="fullscreen-fade">
                                        <div v-if="isFullscreen" class="fixed inset-0 z-1000 flex items-center justify-center p-4 md:p-10 bg-slate-950/80 backdrop-blur-md">
                                            <div class="w-full h-full bg-slate-950 rounded-2xl border border-slate-700 shadow-2xl flex flex-col overflow-hidden">
                                                <div class="flex items-center justify-between bg-slate-900 px-6 py-4 border-b border-slate-800">
                                                    <div class="flex gap-6">
                                                        <button @click="activeCodeView = 'editor'" :class="['text-xs font-bold uppercase tracking-widest cursor-pointer hover:text-slate-400', activeCodeView === 'editor' ? 'text-amber-400' : 'text-slate-500']">Editor</button>
                                                        <button @click="activeCodeView = 'guide'" :class="['text-xs font-bold uppercase tracking-widest cursor-pointer hover:text-slate-400', activeCodeView === 'guide' ? 'text-amber-400' : 'text-slate-500']">Guide</button>
                                                    </div>
                                                    <div class="flex items-center gap-4">
                                                        <div class="flex items-center gap-2 bg-slate-800 px-3 py-1 rounded">
                                                            <Terminal size="14" class="text-slate-500" />
                                                            <select v-model="effectiveFeature.language" @change="syncSampleCode" class="bg-transparent text-xs font-bold text-white border-none outline-none cursor-pointer">
                                                                <option value="php">PHP</option>
                                                                <option value="javascript">JS</option>
                                                                <option value="python">PY</option>
                                                            </select>
                                                        </div>
                                                        <select v-model="fontSize" class="bg-slate-800 text-xs font-bold text-white px-3 py-1 rounded border-none cursor-pointer outline-none">
                                                            <option v-for="s in [10, 12, 14, 16, 18, 20]" :key="s" :value="s">{{ s }}px</option>
                                                        </select>
                                                        <button @click="isFullscreen = false" class="p-2 bg-slate-800 hover:bg-slate-600 rounded-full text-white transition-colors cursor-pointer">
                                                            <Minimize2 size="18" />
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex-1 overflow-hidden relative bg-slate-950">
                                                    <transition name="fade-fast" mode="out-in">
                                                        <CodeEditor v-if="activeCodeView === 'editor'" key="full-editor" v-model="effectiveFeature.content" :language="effectiveFeature.language" :options="{ fontSize: fontSize }" />
                                                        <div v-else key="full-guide" class="p-10 space-y-6 overflow-y-auto h-full">
                                                            <div class="max-w-4xl mx-auto space-y-4">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-center justify-center gap-4">
                                                                        <button @click="prevTemplate" class="p-2 hover:bg-slate-800 rounded text-slate-400 cursor-pointer"><ChevronLeft size="20" /></button>
                                                                        <h3 class="w-80 text-center text-lg text-amber-400 font-bold uppercase tracking-widest">{{ currentTemplate.title }}</h3>
                                                                        <button @click="nextTemplate" class="p-2 hover:bg-slate-800 rounded text-slate-400 cursor-pointer"><ChevronRight size="20" /></button>
                                                                    </div>
                                                                    <Button mode="solid" type="primary" :iconLeft="Plus" :action="useCodeTemplate" buttonClass="rounded-lg">
                                                                        <span>Use Template</span>
                                                                    </Button>
                                                                </div>
                                                                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-8 shadow-inner">
                                                                    <pre class="text-indigo-300 text-xs font-mono overflow-auto">{{ currentTemplate.code }}</pre>
                                                                </div>
                                                                <div class="text-slate-400 leading-relaxed border-l-2 border-amber-500/50 pl-3">
                                                                    <p class="text-slate-600 text-md">{{ currentTemplate.description }}</p>
                                                                    <p>Return an <span class="text-slate-200">Array</span> of objects with <code class="text-indigo-400">label</code>, <code class="text-indigo-400">trigger</code>, and <code class="text-indigo-400">next_step_id</code>.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </Teleport>
                            </div>
                        </div>
                    </transition>

                    <Input label="Save selection as" placeholder="e.g. choice" :model-value="effectiveFeature.variable" @update:model-value="val => effectiveFeature.variable = toCamelCase(val)">
                        <template #prefix><span class="text-xs text-slate-600 border-r border-slate-300 pr-2 mr-2">@</span></template>
                    </Input>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button @click="onCancel" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <Button size="md" type="primary" mode="solid" :action="onApply" :disabled="!canApply" buttonClass="rounded-lg shadow-md">Apply</Button>
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
import CodeEditor from '@Partials/CodeEditor.vue';
import { toCamelCase } from '@Utils/stringUtils';
import { VueDraggableNext } from 'vue-draggable-next';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import StepSelector from '@Pages/versions/_components/selectors/StepSelector.vue';
import { X, Plus, Trash2, GripVertical, Terminal, ChevronLeft, ChevronRight, Maximize2, Minimize2 } from 'lucide-vue-next';

export default {
    name: 'SelectFeatureModal',
    components: { Modal, Tabs, Button, Input, draggable: VueDraggableNext, ContentEditor, StepSelector, CodeEditor, X, Plus, Trash2, Terminal, GripVertical, ChevronLeft, ChevronRight, Maximize2, Minimize2 },
    inject: ['versionState'],
    data() {
        return {
            Plus,
            instruction: '',
            showInstructionField: false,
            activeCodeView: 'editor',
            currentTemplateIndex: 0,
            isFullscreen: false,
            fontSize: 14,
            draftFeature: {
                type: 'select', source: 'list', variable: 'choice', language: 'php', content: '',
                options: [{ label: '', next_step_id: null }],
            }
        };
    },
    computed: {
        isEditing() { return !!this.versionState.currentFeatureId; },
        isNewSelectWithoutContent() { return !this.isEditing && !this.versionState.currentStepHasContentFeature; },
        effectiveFeature() { return this.isEditing ? this.versionState.currentFeature : this.draftFeature; },
        effectiveOptions() { return this.effectiveFeature?.options || []; },
        allRawTemplates() {
            return [
                {
                    title: "Standard Services",
                    desc: "Basic numbered menu options with direct step links",
                    php: "<?php\n\nreturn [\n\t[\n\t\t'label' => '1. Check Balance',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t],\n\t[\n\t\t'label' => '2. Buy Airtime',\n\t\t'trigger' => '2',\n\t\t'next_step_id' => '550e8400-e29b-41d4-a716-446655440000'\n\t]\n];",
                    javascript: "return [\n\t{\n\t\tlabel: '1. Check Balance',\n\t\ttrigger: '1',\n\t\tnext_step_id: '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\tlabel: '2. Buy Airtime',\n\t\ttrigger: '2',\n\t\tnext_step_id: '550e8400-e29b-41d4-a716-446655440000'\n\t}\n];",
                    python: "return [\n\t{\n\t\t'label': '1. Check Balance',\n\t\t'trigger': '1',\n\t\t'next_step_id': '123c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t},\n\t{\n\t\t'label': '2. Buy Airtime',\n\t\t'trigger': '2',\n\t\t'next_step_id': '550e8400-e29b-41d4-a716-446655440000'\n\t}\n]"
                },
                {
                    title: "Dynamic User Data",
                    desc: "Using @variables with dynamic routing",
                    php: "<?php\n\nreturn [\n\t[\n\t\t'label' => 'Pay for @billName',\n\t\t'trigger' => '1',\n\t\t'next_step_id' => '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t]\n];",
                    javascript: "return [\n\t{\n\t\tlabel: 'Pay for @billName',\n\t\ttrigger: '1',\n\t\tnext_step_id: '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t}\n];",
                    python: "return [\n\t{\n\t\t'label': 'Pay for @billName',\n\t\t'trigger': '1',\n\t\t'next_step_id': '456c0b5d-cb83-7120-b649-8c3a373ebf0b'\n\t}\n]"
                }
            ];
        },
        currentTemplates() {
            const lang = (this.effectiveFeature?.language || 'php').toLowerCase();
            const key = lang === 'javascript' || lang === 'js' ? 'javascript' : (lang === 'python' || lang === 'py' ? 'python' : 'php');
            return this.allRawTemplates.map(s => ({ title: s.title, description: s.desc, code: s[key] }));
        },
        currentTemplate() { return this.currentTemplates[this.currentTemplateIndex] || this.currentTemplates[0]; },
        canApply() {
            const feat = this.effectiveFeature;
            if (!feat || !(feat.variable || '').trim()) return false;
            if (feat.source === 'code') return (feat.content || '').trim().length > 2;
            return this.effectiveOptions.length > 0 && this.effectiveOptions.every(opt => (opt.label || '').trim() !== '');
        }
    },
    methods: {
        toCamelCase,
        nextTemplate() { this.currentTemplateIndex = (this.currentTemplateIndex + 1) % this.currentTemplates.length; },
        prevTemplate() { this.currentTemplateIndex = (this.currentTemplateIndex - 1 + this.currentTemplates.length) % this.currentTemplates.length; },
        useCodeTemplate() {
            this.effectiveFeature.content = this.currentTemplate.code;
            this.activeCodeView = 'editor';
        },
        handleSourceChange(val) {
            this.effectiveFeature.source = val;
            if (val === 'code' && !this.effectiveFeature.content.trim()) this.syncSampleCode();
        },
        syncSampleCode() {
            const currentContent = this.effectiveFeature.content.trim();
            const isTemplateMatch = this.allRawTemplates.some(t =>
                t.php.trim() === currentContent ||
                t.javascript.trim() === currentContent ||
                t.python.trim() === currentContent
            );

            if (!currentContent || isTemplateMatch) {
                this.effectiveFeature.content = this.currentTemplate.code;
            }
        },
        reset() {
            this.instruction = ''; this.showInstructionField = false; this.isFullscreen = false;
            this.draftFeature = {
                type: 'select', source: 'list', variable: 'choice', language: 'php', content: '',
                options: [{ label: '', next_step_id: null }]
            };
        },
        addOption() { this.effectiveOptions.push({ label: '', next_step_id: null }); },
        removeOption(index) { this.effectiveOptions.splice(index, 1); },
        onCancel() { this.reset(); this.versionState.selectFeatureModal?.hideModal(); },
        onApply() {
            const state = this.versionState;
            if (!this.isEditing) {
                if (this.showInstructionField && this.instruction.trim()) state.addBasicContentFeature({ content: this.instruction.trim() });
                state.addFeature('select', { ...this.draftFeature });
            }
            state.selectFeatureModal?.hideModal();
            setTimeout(() => { this.reset(); state.setCurrentFeatureId(null); }, 300);
        }
    }
};
</script>

<style scoped>
.fullscreen-fade-enter-active, .fullscreen-fade-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.fullscreen-fade-enter-from, .fullscreen-fade-leave-to { opacity: 0; backdrop-filter: blur(0px); }

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.fade-fast-enter-active, .fade-fast-leave-active { transition: opacity 0.2s ease; }
.fade-fast-enter-from, .fade-fast-leave-to { opacity: 0; }
</style>
