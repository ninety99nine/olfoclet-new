<template>
    <div class="min-w-64 flex items-center justify-between border border-slate-200 rounded-lg relative group/feature transition-all px-3 py-2 bg-white mb-4">

        <button @click="openCodeEditor" class="w-full text-left cursor-pointer py-1">
            <div class="flex items-center gap-3">
                <span :class="['inline-flex items-center justify-center w-10 h-10 rounded-lg text-[10px] font-black border', langClass]">
                    {{ langText }}
                </span>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-700 capitalize">{{ feature.language || 'Logic' }} Script</span>
                    <span class="text-[10px] text-slate-400">Tap to edit code logic</span>
                </div>
            </div>
        </button>

        <div class="opacity-0 group-hover/feature:opacity-100 transition-opacity">
            <Dropdown position="left" dropdownClasses="w-56">
                <template #trigger="{ toggleDropdown }">
                    <button @click="toggleDropdown" class="w-7 h-7 rounded-md border border-slate-200 bg-white flex items-center justify-center hover:bg-slate-50 cursor-pointer">
                        <EllipsisVertical size="14" />
                    </button>
                </template>
                <template #content="{ toggleDropdown }">
                    <div class="py-1">
                        <div @click="(e) => handleEdit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                            <SquarePen size="14" /> Edit
                        </div>
                        <div @click="(e) => switchType(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2 whitespace-nowrap">
                            <ArrowLeftRight size="14" /> Switch to basic content
                        </div>
                        <div @click="(e) => handleDelete(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2">
                            <Trash size="14" /> Delete feature
                        </div>
                    </div>
                </template>
            </Dropdown>
        </div>

    </div>
</template>

<script>
    import Dropdown from '@Partials/Dropdown.vue';
    import { EllipsisVertical, ArrowLeftRight, Trash, SquarePen } from 'lucide-vue-next';

    export default {
        name: 'StepNodeCodeContent',
        inject: ['versionState'],
        components: { Dropdown, EllipsisVertical, ArrowLeftRight, Trash, SquarePen },
        props: {
            stepId: { required: true },
            feature: { type: Object, required: true }
        },
        computed: {
            langText() {
                const l = this.feature.language?.toLowerCase();
                if (l === 'javascript') return 'JS';
                if (l === 'python') return 'PY';
                return 'PHP';
            },
            langClass() {
                const l = this.feature.language?.toLowerCase();
                if (l === 'javascript') return 'bg-yellow-50 text-yellow-700 border-yellow-200';
                if (l === 'python') return 'bg-sky-50 text-sky-700 border-sky-200';
                return 'bg-purple-50 text-purple-700 border-purple-200';
            }
        },
        methods: {
            openCodeEditor() {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.setCurrentFeatureId(this.feature.id);
                this.versionState.codeContentEditorModal?.showModal();
            },
            handleEdit(close) {
                this.openCodeEditor();
                close();
            },
            switchType(close) {
                this.versionState.switchFeatureContentType(this.feature.id, 'basic content');
                close();
            },
            handleDelete(close) {
                this.versionState.removeFeature(this.stepId, this.feature.id);
                close();
            }
        }
    }
</script>
