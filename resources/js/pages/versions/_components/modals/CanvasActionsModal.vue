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
                        Add something to your flow.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div
                        :key="action.value"
                        @click="handleAction(action.value)"
                        v-for="action in versionState.canvasActions"
                        class="group relative flex items-center gap-4 p-5 bg-white border border-slate-200 rounded-xl hover:border-indigo-400 hover:shadow-md transition-all duration-200 cursor-pointer">
                        <div class="shrink-0 w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <component :is="action.icon" class="w-6 h-6 text-indigo-600" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-slate-900 group-hover:text-indigo-700 transition-colors">
                                {{ action.label }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ action.description }}
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
                        :action="() => versionState.canvasActionsModal?.hideModal()"
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
    import { ArrowRight } from 'lucide-vue-next';

    export default {
        name: 'CanvasActionsModal',
        components: { Modal, Button, ArrowRight },
        inject: ['versionState'],
        methods: {
            handleAction(value) {
                this.versionState.canvasActionsModal?.hideModal();
                if (value === 'interactive_screen') {
                    this.versionState.addInteractiveScreenStep();
                } else if (value === 'decision_point') {
                    this.versionState.addDecisionPointStep();
                }
            },
        },
    };
</script>
