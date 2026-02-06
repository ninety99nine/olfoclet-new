<template>
    <Modal size="sm" ref="modal" :showFooter="false" :scrollOnContent="false">
        <template #content>
            <div class="space-y-6 pt-2">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Add Event</h3>
                    <p class="text-sm text-slate-500">Choose when this event should trigger.</p>
                </div>

                <div class="space-y-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Timing</p>
                    <Tabs
                        v-model="timing"
                        design="1"
                        :tabs="[
                            {label: 'Before Step', value: 'before'},
                            {label: 'After Response', value: 'after'}
                        ]"
                    />
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div
                        v-for="type in eventTypes"
                        :key="type.value"
                        @click="createEvent(type.value)"
                        class="group relative flex items-center gap-4 p-5 bg-white border rounded-xl transition-all duration-200 border-slate-200 hover:border-indigo-400 hover:shadow-md cursor-pointer"
                    >
                        <div class="shrink-0 w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <component :is="type.icon" size="20" class="w-6 h-6 text-indigo-600" />
                        </div>

                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-slate-900 group-hover:text-indigo-700 transition-colors">
                                {{ type.label }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ type.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import Modal from '@Partials/Modal.vue';
import Tabs from '@Partials/Tabs.vue';
import { Globe, FileCode } from 'lucide-vue-next';

export default {
    name: 'EventPickerModal',
    components: { Modal, Tabs, Globe, FileCode },
    inject: ['versionState'],
    data() {
        return {
            timing: 'before'
        };
    },
    computed: {
        eventTypes() {
            return this.versionState.eventTypes;
        }
    },
    methods: {
        createEvent(type) {
            const stepId = this.versionState.currentStepId;

            // 1. Create the event in Pinia Store
            const eid = this.versionState.addEvent(stepId, this.timing, type);

            if (eid) {
                // 2. Hide the picker
                this.$refs.modal.hideModal();

                // 3. Open the specific editor modal based on type
                // Timeout allows the picker modal backdrop to clear before opening the next
                setTimeout(() => {
                    this.versionState.currentEventId = eid;

                    if (type === 'rest api') {
                        this.versionState.restApiEventModal?.showModal();
                    } else {
                        this.versionState.soapApiEventModal?.showModal();
                    }
                }, 300);
            }
        }
    }
}
</script>
