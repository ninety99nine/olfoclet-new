<template>
    <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">

        <div class="min-w-0 flex-1 flex items-center gap-2">

            <div class="flex items-center gap-2 min-w-0">

                <div
                    v-if="step.type == 'decision_point'"
                    :class="['p-1.5 rounded-lg shrink-0', step.type == 'interactive_screen' ? 'bg-indigo-100 text-indigo-600 ' : 'bg-amber-100 text-amber-600 ']">
                    <component :is="step.type == 'interactive_screen' ? Smartphone : GitBranch" size="14" />
                </div>

                <Tooltip position="top" content="Click to edit this step name">
                    <template #trigger>

                        <h3
                            ref="stepNameInput"
                            spellcheck="false"
                            contenteditable="true"
                            @keydown.enter.prevent="$event.target.blur()"
                            @click.stop @mousedown.stop @input="handleNameInput" @blur="handleNameBlur"
                            class="h-5 focus:underline max-w-45 font-semibold text-slate-800 text-sm truncate bg-transparent outline-none border-none hover:border border-amber-500 hover:bg-slate-100 cursor-text transition-colors duration-300">
                        </h3>

                    </template>
                </Tooltip>

            </div>

            <Tooltip v-if="isFirstStep" position="top" content="The entry point where users begin their journey.">
                <template #trigger>
                    <div class="shrink-0 items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 cursor-help">Start</div>
                </template>
            </Tooltip>

        </div>

        <div class="flex items-center gap-1 shrink-0 transition-all opacity-0 group-hover:opacity-100">

            <Tooltip v-if="step.type == 'interactive_screen'" position="top" :content="step?.is_terminal ? 'Continue session so that the user can take more actions' : 'Stop session so that the user cannot take any more actions'">
                <template #trigger>
                    <button
                        type="button"
                        @click.stop="toggleTermination"
                        :class="[
                            'p-1.5 rounded-md transition-all active:scale-90 bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer'
                        ]"
                    >
                        <component :is="step?.is_terminal ? 'Play' : 'LogOut'" size="14" />
                    </button>
                </template>
            </Tooltip>

            <Dropdown position="left" dropdownClasses="w-52">
                <template #trigger="{ toggleDropdown }">
                    <button type="button" @click="toggleDropdown" class="hover:text-indigo-500 transition-all p-1 text-slate-400 cursor-pointer">
                        <EllipsisVertical size="20" />
                    </button>
                </template>
                <template #content="{ toggleDropdown }">
                    <div class="py-1">
                        <div @click="(e) => openStepEdit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                            <SquarePen size="14" /> Edit
                        </div>

                        <div @click="(e) => copyStepId(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                            <Copy size="14" /> Copy ID
                        </div>

                        <div v-if="step.type == 'interactive_screen'" @click="(e) => { toggleTermination(); toggleDropdown(e); }" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                            <component :is="step?.is_terminal ? 'Play' : 'LogOut'" size="14" />
                            {{ step?.is_terminal ? 'Continue session' : 'End session' }}
                        </div>

                        <div @click="(e) => setAsStartStep(() => toggleDropdown(e))" :class="['px-4 py-2.5 text-sm cursor-pointer flex items-center gap-2', isFirstStep ? 'text-emerald-700 hover:bg-emerald-50' : 'text-slate-700 hover:bg-slate-50']">
                            <Sparkles size="14" /> Set as start step
                        </div>

                        <div v-if="canDeleteStep" class="my-1 border-t border-slate-200"></div>
                        <div v-if="canDeleteStep" @click="(e) => deleteStep(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2">
                            <Trash size="14" /> Delete step
                        </div>
                    </div>
                </template>
            </Dropdown>
        </div>

    </div>
</template>

<script>
    import Dropdown from '@Partials/Dropdown.vue';
    import Tooltip from '@Partials/Tooltip.vue';
    import { EllipsisVertical, SquarePen, Copy, Sparkles, Trash, Play, LogOut, Smartphone, GitBranch } from 'lucide-vue-next';

    export default {
        name: 'StepNodeHeader',
        inject: ['versionState', 'notificationState'],
        components: { Dropdown, Tooltip, EllipsisVertical, SquarePen, Copy, Sparkles, Trash, Play, LogOut },
        props: { isFirstStep: Boolean, canDeleteStep: Boolean, stepId: { required: true } },
        watch: {
            'step.name'() { if (document.activeElement !== this.$refs.stepNameInput) this.updateDisplay(); },
            'versionState.versionForm.builder.initial_step_id': {
                handler(newId) { this.versionState.addToInitialStepHistory(newId); },
                immediate: true
            }
        },
        data() {
            return {
                Smartphone,
                GitBranch
            }
        },
        computed: {
            step() { return this.versionState.versionForm.builder.steps[this.stepId]; }
        },
        methods: {
            handleNameInput(e) {
                const newName = e.target.textContent;
                if (this.versionState.versionForm.builder.steps[this.stepId]) {
                    this.versionState.versionForm.builder.steps[this.stepId].name = newName;
                }
            },
            handleNameBlur(e) {
                const finalName = (e.target.textContent || '').trim() || 'Unnamed';
                this.versionState.versionForm.builder.steps[this.stepId].name = finalName;
                this.updateDisplay();
            },
            updateDisplay() {
                const el = this.$refs.stepNameInput;
                if (!el || !this.step) return;

                const currentName = this.step.name || 'Unnamed';
                if (el.textContent !== currentName) {
                    el.textContent = currentName;
                }
            },
            toggleTermination() {
                const status = this.versionState.toggleStepTermination(this.stepId);
                this.notificationState.showInfoNotification(
                    `Step set to ${status}.`,
                    2000
                );
            },
            openStepEdit(close) {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.stepEditModal?.showModal();
                if(close) close();
            },
            copyStepId(close) {
                navigator.clipboard.writeText(this.stepId);
                this.notificationState?.showSuccessNotification('Step ID copied');
                if(close) close();
            },
            setAsStartStep(close) {
                if (this.isFirstStep) {
                    const result = this.versionState.reassignInitialStep(this.stepId);
                    if (result.success) {
                        this.notificationState.showInfoNotification(`Entry point moved to "${result.name}"`, 3000);
                    } else {
                        this.notificationState.showWarningNotification("App must have at least one starting step.", 5000);
                    }
                } else {
                    this.versionState.setInitialStepId(this.stepId);
                    this.notificationState.showSuccessNotification(`"${this.step.name}" is now the entry point.`, 5000);
                }
                if (close) close();
            },
            deleteStep(close) {
                if (!this.stepId) return;
                const state = this.versionState;
                if (this.isFirstStep) {
                    const result = state.reassignInitialStep(this.stepId);
                    if (result.success) {
                        this.notificationState.showInfoNotification(`Entry point moved to "${result.name}"`);
                    }
                }
                state.removeStep(this.stepId);
                this.notificationState.showSuccessNotification('Step deleted');
                if (close) close();
            }
        },
        mounted() { this.updateDisplay(); },
    }
</script>
