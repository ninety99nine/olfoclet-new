<template>

    <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">

        <div class="min-w-0 flex-1 flex items-center gap-2">
            <h3
                ref="stepNameInput"
                spellcheck="false"
                contenteditable="true"
                @keydown.enter.prevent="$event.target.blur()"
                @click.stop @mousedown.stop @input="handleNameInput" @blur="handleNameBlur"
                class="focus:underline max-w-45 font-semibold text-slate-800 text-sm truncate bg-transparent outline-none border-none cursor-text">
            </h3>

            <span v-if="isFirstStep" class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Start</span>

        </div>

        <Dropdown position="left" dropdownClasses="w-48">
            <template #trigger="{ toggleDropdown }">
                <button type="button" @click="toggleDropdown" class="hover:text-indigo-500 transition-all opacity-0 group-hover:opacity-100 p-1">
                    <EllipsisVertical size="20" />
                </button>
            </template>
            <template #content="{ toggleDropdown }">
                <div class="py-1">
                    <div @click="(e) => openStepEdit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2"><SquarePen size="14" /> Edit</div>
                    <div @click="(e) => copyStepId(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2"><Copy size="14" /> Copy ID</div>
                    <div @click="(e) => setAsStartStep(() => toggleDropdown(e))" :class="['px-4 py-2.5 text-sm cursor-pointer flex items-center gap-2', isFirstStep ? 'text-emerald-700 hover:bg-emerald-50' : 'text-slate-700 hover:bg-slate-50']">
                        <Sparkles size="14" /> Set as start step
                    </div>
                    <div v-if="canDeleteStep" class="my-1 border-t border-slate-200"></div>
                    <div v-if="canDeleteStep" @click="(e) => deleteStep(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2"><Trash size="14" /> Delete step</div>
                </div>
            </template>
        </Dropdown>

    </div>

</template>

<script>
    import Dropdown from '@Partials/Dropdown.vue';
    import { EllipsisVertical, SquarePen, Copy, Sparkles, Trash } from 'lucide-vue-next';

    export default {
        inject: ['versionState', 'notificationState'],
        components: { Dropdown, EllipsisVertical, SquarePen, Copy, Sparkles, Trash },
        props: { isFirstStep: Boolean, canDeleteStep: Boolean, stepId: { required: true } },
        watch: {
            'step.name'() { if (document.activeElement !== this.$refs.stepNameInput) this.updateDisplay(); },
            'versionState.versionForm.builder.initial_step_id': {
                handler(newId) { this.versionState.addToInitialStepHistory(newId); },
                immediate: true
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
                // Guard: if step is missing (being deleted), stop here
                if (!el || !this.step) return;

                const currentName = this.step.name || 'Unnamed';
                if (el.textContent !== currentName) {
                    el.textContent = currentName;
                }
            },
            openStepEdit(close) {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.stepEditModal?.showModal();
                close();
            },
            copyStepId(close) {
                navigator.clipboard.writeText(this.stepId);
                this.notificationState?.showSuccessNotification('Step ID copied');
                close();
            },
            setAsStartStep(close) {
                if (this.isFirstStep) {
                    // Toggling OFF
                    const result = this.versionState.reassignInitialStep(this.stepId);

                    if (result.success) {
                        this.notificationState.showInfoNotification(`Entry point moved to "${result.name}"`, 3000);
                    } else {
                        this.notificationState.showWarningNotification("App must have at least one starting step.", 5000);
                    }
                } else {
                    // Toggling ON
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
                this.close();
            }
        },
        mounted() { this.updateDisplay(); },
    }
</script>
