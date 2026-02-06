<template>

    <div class="min-w-60 relative group/feature mb-2">

        <div
            @click="isCollapsed = !isCollapsed"
            :class="[
                isCollapsed ? 'rounded-lg' : 'rounded-t-lg border-b-transparent',
                'flex items-center justify-between px-3 py-2 bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-100 transition-all duration-200',
            ]">

            <div class="flex items-center gap-2 min-w-0">
                <ListFilter v-if="feature.source !== 'code'" size="14" class="text-indigo-600 shrink-0" />
                <Code2 v-else size="14" class="text-amber-600 shrink-0" />
                <span class="text-xs font-bold text-slate-700 capitalize leading-tight">
                    {{ feature.source === 'code' ? 'Dynamic Menu' : 'Menu' }}
                </span>
                <span v-if="isCollapsed" class="text-[9px] text-slate-500 truncate">
                    {{ feature.options?.length || 0 }} options • @{{ feature.variable || 'choice' }}
                </span>
                <span v-else class="text-[10px] text-slate-500 truncate">@{{ feature.variable || 'choice' }}</span>
            </div>

            <div class="flex items-center gap-1">

                <div @click.stop class="opacity-0 group-hover/feature:opacity-100 transition-opacity">
                    <Dropdown position="left" dropdownClasses="w-44">
                        <template #trigger="{ toggleDropdown }">
                            <button @click="toggleDropdown" class="w-6 h-6 rounded-md border border-slate-200 bg-white flex items-center justify-center hover:bg-slate-50 cursor-pointer">
                                <EllipsisVertical size="12" />
                            </button>
                        </template>
                        <template #content="{ toggleDropdown }">
                            <div class="py-1">
                                <div @click="(e) => edit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                                    <SquarePen size="14" /> Edit Menu
                                </div>
                                <div @click="(e) => removeFeature(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2">
                                    <Trash size="14" /> Delete Entire Menu
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <ChevronDown size="14" :class="['text-slate-400 transition-transform duration-200', { '-rotate-90': isCollapsed }]" />

            </div>

        </div>

        <div
            v-show="!isCollapsed"
            class="flex flex-col bg-white border border-slate-200 rounded-b-lg overflow-hidden">

            <template v-if="feature.source === 'list'">

                <draggable
                    item-key="label"
                    class="flex flex-col"
                    ghost-class="bg-indigo-50"
                    handle=".option-drag-handle"
                    v-model="versionState.builder.features[feature.id].options">

                    <div
                        :key="`opt-${feature.id}-${idx}`"
                        v-for="(option, idx) in versionState.builder.features[feature.id].options"
                        class="relative group/option flex items-center justify-between px-3 py-2 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">

                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 w-3 shrink-0">{{ idx + 1 }}.</span>
                            <span class="text-xs text-slate-600 truncate">{{ option.label || 'Empty option' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5 pl-2"
                            v-if="versionState.builder.features[feature.id].options.length > 1">

                            <button
                                @click.stop="removeOption(idx)"
                                class="opacity-0 group-hover/option:opacity-100 p-1 text-slate-300 hover:text-rose-500 transition-all nodrag cursor-pointer">
                                <Trash size="12" />
                            </button>

                            <div class="flex flex-col items-center justify-center opacity-0 group-hover/option:opacity-100 transition-all">
                                <button
                                    v-if="idx != 0"
                                    @click.stop="moveOption(idx, -1)"
                                    class="text-slate-300 hover:text-indigo-500 cursor-pointer p-0.5 nodrag">
                                    <ChevronUp size="12" />
                                </button>
                                <button
                                    v-if="idx != versionState.builder.features[feature.id].options.length - 1"
                                    @click.stop="moveOption(idx, 1)"
                                    class="text-slate-300 hover:text-indigo-500 cursor-pointer p-0.5 nodrag">
                                    <ChevronDown size="12" />
                                </button>
                            </div>

                            <div class="option-drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-indigo-400 transition-colors nodrag p-1">
                                <GripVertical size="12" />
                            </div>

                            <div class="relative w-2 h-4 flex items-center">
                                <Handle
                                    type="source"
                                    position="right"
                                    :id="`select-${feature.id}-opt-${idx}`"
                                    class="static! translate-y-0! nodrag nopan"
                                />
                            </div>

                        </div>

                    </div>

                </draggable>

            </template>

            <template v-else>

                <div class="relative flex flex-col items-center justify-center p-6 bg-slate-50/50 group/code">
                    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center mb-2 border border-amber-100">
                        <Terminal size="18" class="text-amber-600" />
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Code Driven</span>
                    <span class="text-[9px] text-slate-400 mt-1 capitalize">{{ feature.language || 'Logic' }} Script</span>

                    <Handle
                        type="source"
                        position="right"
                        class="block nodrag nopan!"
                        :id="`select-${feature.id}-dynamic`"
                        :style="{ top: '50%', transform: 'translateY(-50%)' }"
                    />
                </div>

            </template>

            <button
                @click="edit()"
                class="w-full py-2 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50 flex items-center justify-center gap-1 transition-colors border-t border-slate-100 cursor-pointer">
                <Plus size="12" /> {{ feature.source === 'code' ? 'Edit Script' : 'Manage Options' }}
            </button>

        </div>

    </div>

</template>

<script>
    import { Handle } from '@vue-flow/core';
    import Dropdown from '@Partials/Dropdown.vue';
    import { VueDraggableNext } from 'vue-draggable-next';
    import { ListFilter,EllipsisVertical,SquarePen,Trash,Plus,Code2,Terminal,GripVertical,ChevronDown, ChevronUp } from 'lucide-vue-next';

    export default {
        name: 'StepNodeSelect',
        inject: ['versionState', 'notificationState'],
        components: {
            Handle, Dropdown, ListFilter, EllipsisVertical, SquarePen, Trash, Plus, Code2,
            Terminal, GripVertical, ChevronDown, ChevronUp, draggable: VueDraggableNext
        },
        props: {
            stepId: { required: true },
            feature: { type: Object, required: true }
        },
        data() {
            return {
                isCollapsed: false
            }
        },
        methods: {
            edit(close) {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.setCurrentFeatureId(this.feature.id);
                this.versionState.selectFeatureModal?.showModal();
                if (close) close();
            },
            removeFeature(close) {
                this.versionState.removeFeature(this.stepId, this.feature.id);
                if (close) close();
            },
            removeOption(index) {
                this.versionState.builder.features[this.feature.id].options.splice(index, 1);
            },
            moveOption(index, direction) {
                const options = this.versionState.builder.features[this.feature.id].options;
                const newIndex = index + direction;

                // Boundary check
                if (newIndex < 0 || newIndex >= options.length) return;

                // Perform the swap
                const element = options.splice(index, 1)[0];
                options.splice(newIndex, 0, element);
            }
        }
    }
</script>

<style scoped>
:deep(.vue-flow__handle) {
    width: 8px;
    height: 8px;
    background-color: #4f46e5;
    border: 2px solid white;
    right: -4px !important;
    top: auto !important;
    bottom: auto !important;
    transform: none !important;
    position: absolute;
    transition: transform 0.2s, background-color 0.2s;
}

:deep(.vue-flow__handle:hover) {
    transform: scale(1.3) !important;
    background-color: #3730a3;
}

.option-drag-handle {
    touch-action: none;
}
</style>
