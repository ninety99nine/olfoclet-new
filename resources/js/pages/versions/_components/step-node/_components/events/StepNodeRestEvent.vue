<template>
    <div
        @click.stop="openEditor"
        :class="[
            'flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed cursor-pointer transition-all mb-2',
            timing === 'before' ? 'bg-amber-50/50 border-amber-200 hover:bg-amber-100' : 'bg-blue-50/50 border-blue-200 hover:bg-blue-100'
        ]"
    >
        <div :class="['p-1.5 rounded-md', timing === 'before' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600']">
            <Globe size="12" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-tight text-slate-600">{{ event.method }} API</span>
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
import { Globe, Trash2 } from 'lucide-vue-next';

export default {
    name: 'StepNodeRestEvent',
    components: { Globe, Trash2 },
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
            this.versionState.restApiEventModal?.showModal();
        },
        deleteEvent() {
            this.versionState.removeEvent(this.stepId, this.eventId);
        }
    }
}
</script>
