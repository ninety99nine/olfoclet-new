<template>
    <Modal
        size="md"
        ref="modal"
        :showFooter="false"
        :scrollOnContent="false">
        <template #content>
            <div
                class="w-full min-w-0 space-y-4 pt-4"
                v-if="versionState.currentStep && versionState.currentFeature">

                <ContentEditor
                    :fullWidth="true"
                    :showActions="false"
                    v-model="versionState.currentFeature.content"
                />

                <div class="rounded-xl border border-slate-200 bg-slate-50/50 overflow-hidden">

                    <div class="flex items-center justify-between px-4 py-3 bg-slate-100/50 border-b border-slate-200">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Guide</div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition-colors"
                            @click="showGuide = !showGuide">
                            <component :is="showGuide ? 'ChevronUp' : 'ChevronDown'" size="14" />
                            <span class="text-[11px] font-semibold">
                                {{ showGuide ? 'Hide guide' : 'Show guide' }}
                            </span>
                        </button>
                    </div>

                    <div v-if="showGuide" class="p-4 space-y-4 animate-in fade-in duration-300">

                        <div class="text-[11px] text-slate-600 leading-relaxed">
                            <p>
                                Provide a clear, short instruction. Use
                                <span class="text-indigo-600 font-bold">@</span> to insert variables like
                                <span class="font-medium text-slate-900">@firstName</span>.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Example {{ currentTemplateIndex + 1 }}/{{ templates.length }}</div>
                                    <div class="flex items-center gap-1 ml-2">
                                        <button
                                            @click="prevTemplate"
                                            class="p-1 hover:bg-slate-200 rounded transition-colors text-slate-500 active:scale-90">
                                            <ChevronLeft size="14" />
                                        </button>
                                        <button
                                            @click="nextTemplate"
                                            class="p-1 hover:bg-slate-200 rounded transition-colors text-slate-500 active:scale-90">
                                            <ChevronRight size="14" />
                                        </button>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="text-[11px] font-medium text-indigo-600 hover:text-indigo-800 underline underline-offset-2 hover:scale-105 transition-all duration-300 cursor-pointer"
                                    @click="useBasicTextTemplate">
                                    Use template
                                </button>
                            </div>

                            <div class="relative overflow-hidden h-[80px] bg-white border border-slate-200 rounded-md shadow-sm">
                                <Transition :name="transitionName" mode="out-in">
                                    <pre
                                        :key="currentTemplateIndex"
                                        class="absolute inset-0 m-0 p-3 text-[11px] leading-5 font-mono text-slate-700 overflow-auto whitespace-pre-wrap"
                                        v-html="highlightedTemplate"
                                    ></pre>
                                </Transition>
                            </div>

                            <div class="text-[10px] text-slate-400 italic">
                                Tip: USSD works best with under 160 characters.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <Button
                        size="md"
                        type="primary"
                        mode="solid"
                        :action="() => versionState.basicContentEditorModal?.hideModal()"
                        buttonClass="rounded-lg px-8">
                        Done
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
    import { ChevronUp, ChevronDown, ChevronLeft, ChevronRight } from 'lucide-vue-next';

    export default {
        name: 'BasicContentEditorModal',
        components: { Modal, Button, ContentEditor, ChevronUp, ChevronDown, ChevronLeft, ChevronRight },
        inject: ['appState', 'versionState'],
        data() {
            return {
                showGuide: true,
                currentTemplateIndex: 0,
                transitionName: 'slide-left',
            };
        },
        computed: {
            app() {
                return this.appState.app;
            },
            currentTemplate() {
                return this.templates[this.currentTemplateIndex];
            },
            highlightedTemplate() {
                const regex = /(@\w+)/g;
                let text = this.currentTemplate;
                let escaped = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                return escaped.replace(regex, '<span class="variable-tag">$1</span>');
            },
            templates() {
                return [
                    `Hi @firstName, welcome to ${this.app.name}.`,
                    'You are sending @amount to @phoneNumber, please confirm.',
                    'Your account number is @accountNumber. Reply 0 to go back',
                    'Thank you @firstName. We have sent a code to @phoneNumber.',
                    'Transfer Successful! @amount has been sent to @lastName.',
                    'Enter the amount you wish to pay for @billName.',
                    'Rate your experience with @productName from 1 (Poor) to 5 (Great).',
                    'Sorry @firstName, @accountNumber is currently unavailable.',
                    'Inquiry: @firstName @lastName, how can we help you today?',
                    'Security: Is this your phone @phoneNumber? Reply 1 for Yes.'
                ];
            }
        },
        methods: {
            useBasicTextTemplate() {
                if (this.versionState.currentFeature) {
                    this.versionState.currentFeature.content = this.currentTemplate;
                }
            },
            nextTemplate() {
                this.transitionName = 'slide-left';
                this.currentTemplateIndex = (this.currentTemplateIndex + 1) % this.templates.length;
            },
            prevTemplate() {
                this.transitionName = 'slide-right';
                this.currentTemplateIndex = (this.currentTemplateIndex - 1 + this.templates.length) % this.templates.length;
            }
        },
    };
</script>

<style scoped>
    :deep(.variable-tag) {
        background-color: #e0e7ff;
        color: #4338ca;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: 600;
        display: inline;
    }

    /* Slide Left Transition */
    .slide-left-enter-active, .slide-left-leave-active,
    .slide-right-enter-active, .slide-right-leave-active {
        transition: all 0.2s ease-out;
    }

    .slide-left-enter-from { opacity: 0; transform: translateX(20px); }
    .slide-left-leave-to { opacity: 0; transform: translateX(-20px); }

    /* Slide Right Transition */
    .slide-right-enter-from { opacity: 0; transform: translateX(-20px); }
    .slide-right-leave-to { opacity: 0; transform: translateX(20px); }
</style>
