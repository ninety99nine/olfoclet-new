<template>

    <div class="border border-slate-200 rounded-lg relative group/feature transition-all bg-slate-50 p-3 text-sm mb-4">

        <ContentEditor
            :key="feature.id"
            :fullWidth="true"
            v-model="versionForm.builder.features[feature.id].content">

            <template #title>
                <div class="flex items-center gap-2 min-w-0">
                    <Sparkle size="14" class="text-indigo-600 shrink-0" />
                    <span class="text-xs font-bold text-slate-700 capitalize leading-tight">
                        Content
                    </span>
                </div>
            </template>

            <template #headerActions>
                <Dropdown position="left" dropdownClasses="w-56">
                    <template #trigger="{ toggleDropdown }">
                        <button @click="toggleDropdown" class="w-7 h-8 rounded-md border border-slate-200 bg-white flex items-center justify-center hover:bg-slate-50 cursor-pointer">
                            <EllipsisVertical size="14" />
                        </button>
                    </template>
                    <template #content="{ toggleDropdown }">
                        <div class="py-1">
                            <div @click="(e) => handleEdit(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2">
                                <SquarePen size="14" /> Edit
                            </div>
                            <div @click="(e) => switchType(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm hover:bg-slate-50 cursor-pointer flex items-center gap-2 whitespace-nowrap">
                                <ArrowLeftRight size="14" /> Switch to code content
                            </div>
                            <div @click="(e) => handleDelete(() => toggleDropdown(e))" class="px-4 py-2.5 text-sm text-rose-700 hover:bg-rose-50 cursor-pointer flex items-center gap-2">
                                <Trash size="14" /> Delete feature
                            </div>
                        </div>
                    </template>
                </Dropdown>
            </template>

        </ContentEditor>

    </div>

</template>

<script>
    import Dropdown from '@Partials/Dropdown.vue';
    import { EllipsisVertical, Sparkle, SquarePen, ArrowLeftRight, Trash } from 'lucide-vue-next';
    import ContentEditor from '@Pages/versions/_components/editors/content-editor/ContentEditor.vue';

    export default {
        name: 'StepNodeBasicContent',
        inject: ['versionState'],
        components: { Dropdown, ContentEditor, EllipsisVertical, Sparkle, SquarePen, ArrowLeftRight, Trash },
        props: {
            stepId: { required: true },
            feature: { type: Object, required: true }
        },
        computed: {
            versionForm() {
                return this.versionState.versionForm;
            }
        },
        methods: {
            handleEdit(close) {
                this.versionState.setCurrentStepId(this.stepId);
                this.versionState.setCurrentFeatureId(this.feature.id);
                this.versionState.basicContentEditorModal?.showModal();
                close();
            },
            switchType(close) {
                this.versionState.switchFeatureContentType(this.feature.id, 'code content');
                close();
            },
            handleDelete(close) {
                this.versionState.removeFeature(this.stepId, this.feature.id);
                close();
            }
        }
    }
</script>
