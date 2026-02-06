<template>
    <div class="space-y-2">
        <draggable
            v-model="list"
            handle=".drag-handle"
            ghost-class="opacity-50"
            item-key="index"
            class="space-y-2">

            <div
                v-for="(item, index) in modelValue"
                :key="index"
                class="flex items-stretch gap-0 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:border-slate-300 hover:shadow-md transition-all group">

                <div class="flex-1 flex flex-col md:flex-row items-stretch min-w-0">
                    <div class="flex-1 md:w-1/3 border-b md:border-b-0 md:border-r border-slate-100">
                        <input
                            v-model="item.key"
                            :placeholder="`Key ${index + 1}`"
                            class="w-full h-full px-4 py-3 text-[11px] font-medium text-slate-700 bg-transparent outline-none placeholder:text-slate-400"
                        />
                    </div>
                    <div class="flex-1 min-w-0 bg-slate-50/30">
                        <ContentEditor
                            type="input"
                            :showHeader="false"
                            v-model="item.value"
                            class="nested-editor"
                        />
                    </div>
                </div>
                <div class="flex items-stretch shrink-0 self-stretch">
                    <div class="flex items-center justify-center px-1 bg-slate-50/50 border-l border-slate-100 hover:bg-rose-50 text-slate-300 hover:text-rose-500 transition-colors">
                        <button
                            type="button"
                            @click="remove(index)"
                            class="p-2 transition-colors cursor-pointer outline-none">
                            <Trash2 size="14" />
                        </button>
                    </div>

                    <div class="drag-handle flex items-center justify-center w-10 bg-slate-50 border-l border-slate-100 text-slate-300 hover:bg-indigo-50 hover:text-indigo-500 cursor-grab active:cursor-grabbing transition-colors">
                        <GripVertical size="14" />
                    </div>
                </div>
            </div>
        </draggable>

        <div v-if="!modelValue?.length" class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-slate-300 mb-2">
                <Plus size="20" />
            </div>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ emptyText }}</p>
        </div>

        <div class="flex justify-end">
            <Button
                size="xs"
                :action="add"
                type="primary"
                mode="outline"
                buttonClass="mt-2 rounded-lg font-bold px-4">
                <Plus size="14" class="mr-1" />
                Add Parameter
            </Button>
        </div>
    </div>
</template>

<script>
import { VueDraggableNext } from 'vue-draggable-next';
import { Trash2, Plus, GripVertical } from 'lucide-vue-next';
import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';
import Button from '@Partials/Button.vue';

export default {
    name: 'KeyValueEditor',
    components: { Trash2, Plus, GripVertical, ContentEditor, Button, draggable: VueDraggableNext },
    props: {
        modelValue: { type: Array, default: () => [] },
        emptyText: { type: String, default: 'No parameters added' }
    },
    computed: {
        list: {
            get() { return this.modelValue; },
            set(value) { this.$emit('update:modelValue', value); }
        }
    },
    methods: {
        add() {
            const list = [...this.modelValue];
            list.push({ key: '', value: '' });
            this.$emit('update:modelValue', list);
        },
        remove(index) {
            const list = [...this.modelValue];
            list.splice(index, 1);
            this.$emit('update:modelValue', list);
        }
    }
}
</script>

<style scoped>

:deep(.input-mode) {
    font-size: 11px;
    color: #334155;
    border: none !important;
    background: transparent !important;
}

:deep(.input-mode::before) {
    font-weight: 500;
    color: #94a3b8 !important;
}

:deep(.input-mode.is-empty:before) {
    content: "Use @ to insert variables";
    top: 0.4rem;
    left: 0.85rem;
    white-space: nowrap;
}

:deep(.variable-tag) {
        padding: 4px 6px;
    }

</style>
