<template>

    <div class="code-editor-container flex flex-col rounded-lg overflow-hidden shadow-2xl">

        <div class="flex items-center justify-between px-4 py-3 bg-[#252526] border-b border-white/5">

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">LANGUAGE</span>
                <select
                    :value="language"
                    @change="$emit('update:language', $event.target.value)"
                    class="bg-slate-900/50 text-indigo-400 text-xs font-medium border border-slate-700 rounded-md px-2 py-1 focus:ring-1 focus:ring-indigo-500 outline-none cursor-pointer hover:bg-slate-800 transition-colors">
                    <option value="javascript">Javascript</option>
                    <option value="python">Python</option>
                    <option value="php">PHP</option>
                </select>
            </div>

            <div class="flex items-center gap-4">
                <div v-if="title" class="flex items-center gap-2 text-slate-500">
                    <span class="text-[10px] font-mono">{{ title }}</span>
                </div>
                <div
                    @click="close"
                    v-if="close != null"
                    class="w-6 h-6 rounded-full bg-indigo-500/20 border border-indigo-500/40 flex items-center justify-center hover:scale-110 transition-all duration-300 cursor-pointer">
                    <X class="text-indigo-500" size="16"></X>
                </div>
            </div>
        </div>

        <div class="flex-1 relative">
            <CodeEditor
                :theme="theme"
                :language="language"
                :model-value="modelValue"
                @update:modelValue="$emit('update:modelValue', $event)"
            />
        </div>

        <!-- Guidance -->
        <div class="bg-[#1e1e1e] border-t border-white/5 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Guide</div>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-indigo-500/15 text-indigo-200 border border-indigo-500/30 hover:bg-indigo-500/25 hover:border-indigo-400/50 transition-colors"
                    @click="showGuide = !showGuide"
                >
                    <ChevronUp v-if="showGuide" size="14" />
                    <ChevronDown v-else size="14" />
                    <span class="text-[11px] font-semibold">
                        {{ showGuide ? 'Hide guide' : 'Show guide' }}
                    </span>
                </button>
            </div>

            <div v-if="showGuide">
                <div class="mt-2 text-[11px] text-slate-300 leading-relaxed">
                    <div class="font-semibold text-slate-200 mb-1">What should this code do?</div>
                    <div class="text-slate-400">
                        Return a <span class="text-slate-200 font-medium">string</span> to display to the user.
                        You can include variables like <span class="text-slate-200 font-medium">@firstName</span>.
                    </div>
                </div>

                <div class="mt-3">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Example</div>
                        <button
                            type="button"
                            class="text-[11px] font-medium text-slate-300 hover:text-white underline underline-offset-2 hover:scale-105 transition-all duration-300 cursor-pointer"
                            @click="useTemplate"
                        >
                            Use template
                        </button>
                    </div>

                    <pre class="mt-2 text-[11px] leading-5 font-mono text-slate-200 bg-black/30 border border-white/10 rounded-md p-3 overflow-auto"><code>{{ template }}</code></pre>

                    <div class="mt-2 text-[11px] text-slate-400">
                        Tip: keep it clean and simple.
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>

    import { X, ChevronDown, ChevronUp } from 'lucide-vue-next';
    import CodeEditor from '@Partials/CodeEditor.vue';

    export default {
        name: 'CodeEditorWrapper',
        props: {
            modelValue: String,
            language: String,
            close: Function,
            title: String,
            theme: String
        },
        components: { X, ChevronDown, ChevronUp, CodeEditor },
        data() {
            return {
                showGuide: true,
            };
        },
        computed: {
            template() {
                const lang = (this.language || 'php').toLowerCase();

                if (lang === 'javascript') {
                    return `// Return a string to display to the user\n// Variables like @firstName can be included in the returned text\n\nreturn 'Hi @firstName, welcome back.';`;
                }

                if (lang === 'python') {
                    return `# Return a string to display to the user\n# Variables like @firstName can be included in the returned text\n\nreturn 'Hi @firstName, welcome back.'`;
                }

                // Default: PHP
                return `<?php\n\n// Return a string to display to the user\n// Variables like @firstName can be included in the returned text\n\nreturn 'Hi @firstName, welcome back.';\n\n?>`;
            },
        },
        methods: {
            useTemplate() {
                this.$emit('update:modelValue', this.template);
            },
        },
    };
</script>
