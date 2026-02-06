<template>

    <div class="min-w-60 border border-slate-200 rounded-lg px-3 py-2 bg-white relative group/feature mb-2">

        <div class="absolute top-2 right-2 opacity-0 group-hover/feature:opacity-100 transition-opacity">
            <Dropdown position="left" dropdownClasses="w-44">
                <template #trigger="{ toggleDropdown }">
                    <button @click="toggleDropdown" class="w-7 h-7 rounded-md border border-slate-200 bg-white flex items-center justify-center">
                        <EllipsisVertical size="14" />
                    </button>
                </template>
                <template #content="{ toggleDropdown }">
                    <div class="py-1">
                        <div @click="(e) => edit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                            <SquarePen size="14" /> Edit
                        </div>
                        <div @click="(e) => remove(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2">
                            <Trash size="14" /> Delete
                        </div>
                    </div>
                </template>
            </Dropdown>
        </div>

        <button type="button" class="w-full text-left cursor-pointer" @click="edit()">
            <div class="flex items-center gap-2 min-w-0">
                <TextCursorInput size="14" class="text-slate-600 shrink-0" />
                <span class="text-xs font-medium text-slate-600 capitalize">{{ feature.type }}</span>
                <span class="text-xs text-slate-500 truncate">@{{ feature.variable || 'value' }}</span>
                <span v-if="feature.validation_rule_ids?.length" class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    {{ feature.validation_rule_ids.length }} {{ feature.validation_rule_ids.length == 1 ? 'rule' : 'rules' }}
                </span>
            </div>
        </button>

        <button v-if="!feature.next_step_id" @click="linkNextStep" class="mt-2 w-full flex items-center justify-center gap-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 border border-indigo-200 rounded-lg py-1.5 transition-colors">
            <Plus size="14" /> Link to next step
        </button>

        <Handle :id="`input#${feature.id}`" type="source" position="right" class="block nodrag nopan!" />

    </div>

</template>

<script>
    import { Handle } from '@vue-flow/core';
    import Dropdown from '@Partials/Dropdown.vue';
    import { TextCursorInput, EllipsisVertical, SquarePen, Trash, Plus } from 'lucide-vue-next';

    export default {
        inject: ['versionState'],
        components: { TextCursorInput, Handle, Dropdown, EllipsisVertical, SquarePen, Trash, Plus },
        props: ['stepId', 'feature'],
        methods: {
            edit(close) {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.setCurrentFeatureId(this.feature.id);
                this.versionState.inputFeatureModal?.showModal();
                if (close) close();
            },
            remove(close) {
                this.versionState.removeFeature(this.stepId, this.feature.id);
                if (close) close();
            },
            linkNextStep() {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.setCurrentFeatureId(this.feature.id);
                this.versionState.inputFeatureModal?.showModal();
            }
        }
    }
</script>
