<template>
    <div
        @keydown.stop
        ref="editorContainer"
        class="monaco-container">
    </div>
</template>

<script>
    import * as monaco from 'monaco-editor';
    import OceanicNext from 'monaco-themes/themes/Oceanic Next.json';
    import NightOwl from 'monaco-themes/themes/Night Owl.json';

    export default {
        name: 'CodeEditor',
        props: {
            modelValue: { type: String, default: '' },
            language: { type: String, default: 'php' },
            theme: { type: String, default: 'tokyo-night' },
            options: { type: Object, default: () => ({ fontSize: 12 }) }
        },
        emits: ['update:modelValue'],
        watch: {
            modelValue(newVal) {
                if (this.editor && newVal !== this.editor.getValue()) {
                    this.editor.setValue(newVal || '');
                }
            },
            language(newLang) {
                if (this.editor) {
                    const model = this.editor.getModel();
                    if (model) monaco.editor.setModelLanguage(model, newLang);
                }
            },
            theme(newTheme) {
                if (this.editor) monaco.editor.setTheme(newTheme);
            },
            options: {
                deep: true,
                handler(newOptions) {
                    if (this.editor) this.editor.updateOptions(newOptions);
                }
            }
        },
        async mounted() {
            await this.$nextTick();
            if (!this.$refs.editorContainer) return;

            // Define themes with 'vs-dark' as base to prevent white background flash
            monaco.editor.defineTheme('oceanic-next', { ...OceanicNext, base: 'vs-dark' });
            monaco.editor.defineTheme('tokyo-night', { ...NightOwl, base: 'vs-dark' });

            this.editor = monaco.editor.create(this.$refs.editorContainer, {
                value: this.modelValue,
                language: this.language,
                theme: this.theme,
                automaticLayout: true,
                minimap: { enabled: false },
                scrollBeyondLastLine: false,
                padding: { top: 16, bottom: 16 },
                renderLineHighlight: 'all',
                fontFamily: "'Fira Code', 'Cascadia Code', monospace",
                fontLigatures: true,
                ...this.options
            });

            // Ensure editor layouts correctly when container size changes (fullscreen)
            this.resizeObserver = new ResizeObserver(() => {
                if (this.editor) this.editor.layout();
            });
            this.resizeObserver.observe(this.$refs.editorContainer);

            this.editor.onDidChangeModelContent(() => {
                const value = this.editor.getValue();
                if (value !== this.modelValue) {
                    this.$emit('update:modelValue', value);
                }
            });
        },
        beforeUnmount() {
            if (this.resizeObserver) this.resizeObserver.disconnect();
            if (this.editor) this.editor.dispose();
        }
    };
</script>

<style scoped>
    .monaco-container {
        width: 100%;
        height: 100%;
        text-align: left;
    }
</style>
