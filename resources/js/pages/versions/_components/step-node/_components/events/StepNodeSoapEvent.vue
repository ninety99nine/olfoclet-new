<template>
    <div
        @click.stop="openEditor"
        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100 cursor-pointer transition-all mb-2"
    >
        <div class="p-1.5 rounded-md bg-emerald-100 text-emerald-600">
            <FileCode size="12" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-tight text-slate-600">SOAP XML</span>
            </div>
            <p class="text-[10px] text-slate-500 truncate">@{{ event.response_variable }}</p>
        </div>

        <div class="flex items-center gap-0">
            <span class="text-[9px] font-black px-1.5 rounded bg-white border border-slate-200 text-slate-400">{{ timing }}</span>
            <button @click.stop="deleteEvent" class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-rose-500 transition-opacity">
                <Trash2 size="12" />
            </button>
        </div>
    </div>
</template>

<script>
import { FileCode, Trash2 } from 'lucide-vue-next';

export default {
    name: 'StepNodeSoapEvent',
    components: { FileCode, Trash2 },
    inject: ['versionState'],
    props: {
        stepId: { required: true },
        eventId: { required: true },
        timing: { type: String, required: true }
    },
    computed: {
        event() { return this.versionState.builder.events[this.eventId]; }
    },
    methods: {
        openEditor() {
            this.versionState.currentEventId = this.eventId;
            this.versionState.soapApiEventModal?.showModal();
        },
        deleteEvent() {
            this.versionState.removeEvent(this.stepId, this.eventId);
        }
    }
}
</script>
