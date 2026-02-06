<template>
    <Modal size="sm" ref="modal" :showFooter="false" :scrollOnContent="false">
        <template #content>
            <div>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-6">Edit Step</p>

                <div v-if="step" class="space-y-6">
                    <Input label="Step name" v-model="stepName" placeholder="e.g. Main Menu" />

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-4">
                            <label class="text-sm font-semibold text-slate-900">Set as first step</label>
                            <Switch v-model="isFirstStep" />
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            The first step is the entry point users see when they start the USSD session.
                        </p>
                    </div>

                    <div v-if="step.type == 'interactive_screen'" class="border-t border-slate-100 pt-6 space-y-2">
                        <div class="flex items-center justify-between gap-4">
                            <label class="text-sm font-semibold text-slate-900">End session at this step</label>
                            <Switch v-model="isTerminal" />
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            If enabled, the network connection will close immediately after this message is displayed to the user.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button
                        size="md"
                        type="primary"
                        mode="solid"
                        :action="() => versionState.stepEditModal?.hideModal()"
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
import Input from '@Partials/Input.vue';
import Switch from '@Partials/Switch.vue';

export default {
    name: 'StepEditModal',
    components: { Modal, Button, Input, Switch },
    inject: ['versionState', 'notificationState'],
    watch: {
        'versionState.versionForm.builder.initial_step_id': {
            handler(newId) { this.versionState.addToInitialStepHistory(newId); },
            immediate: true
        }
    },
    computed: {
        step() { return this.versionState.currentStep; },
        stepName: {
            get() { return this.step?.name || ''; },
            set(val) { if (this.step) this.step.name = val; }
        },
        isFirstStep: {
            get() { return this.versionState.versionForm?.builder?.initial_step_id === this.versionState.currentStepId; },
            set(val) {
                if (val) {
                    this.versionState.setInitialStepId(this.versionState.currentStepId);
                    this.notificationState.showSuccessNotification(`"${this.stepName}" is now the entry point.`, 5000);
                } else {
                    const result = this.versionState.reassignInitialStep(this.versionState.currentStepId);
                    if (result.success) {
                        this.notificationState.showInfoNotification(`Entry point moved to "${result.name}"`, 3000);
                    } else {
                        this.notificationState.showWarningNotification("App must have at least one starting step.", 5000);
                    }
                }
                this.versionState.saveState('Toggled first step status');
            }
        },
        isTerminal: {
            get() { return this.step?.is_terminal || false; },
            set(val) {
                if (this.step && this.step.is_terminal !== val) {
                    const status = this.versionState.toggleStepTermination(this.versionState.currentStepId);
                    this.notificationState.showInfoNotification(
                        `Step set to ${status}.`,
                        2000
                    );
                }
            }
        }
    }
};
</script>
