<template>

    <div class="space-y-2 relative">

        <div v-if="showHeader" class="flex items-center justify-between">

            <label class="text-xs font-medium text-slate-600">Content</label>

            <div
                v-if="showActions"
                class="flex items-center gap-2 opacity-0 group-hover/feature:opacity-100 transition-opacity">

                <Dropdown position="left" dropdownClasses="w-44">
                    <template #trigger="props">
                        <Button size="xs" type="primary" mode="outline" :leftIcon="Plus" buttonClass="rounded-lg" :action="props.toggleDropdown">
                            <span>Variable</span>
                        </Button>
                    </template>
                    <template #content="props">
                        <div class="py-1">
                            <div
                                :key="variable"
                                v-for="variable in variables"
                                @click="(event) => addVariable(variable, () => props.toggleDropdown(event))"
                                class="px-4 py-2.5 text-xs text-slate-400 hover:bg-slate-50 cursor-pointer">
                                {{ variable }}
                            </div>
                        </div>
                    </template>
                </Dropdown>

                <slot name="headerActions"></slot>

            </div>

        </div>

        <Teleport to="body">

            <div
                ref="mentionsMenu"
                v-if="showMentions"
                :style="{ top: menuPos.top + 'px', left: menuPos.left + 'px' }"
                class="fixed z-10000 bg-white border border-slate-200 shadow-xl rounded-lg w-56 max-h-60 overflow-y-auto">
                <div
                    :key="variable"
                    v-for="(variable, i) in filteredVariables"
                    @mousedown.prevent="insertMention(variable)"
                    :class="['px-2 py-1.5 text-xs cursor-pointer flex justify-between items-center transition-colors', i === selectedIndex ? 'bg-indigo-600 text-indigo-100' : 'text-slate-400 hover:bg-indigo-50']">
                    <span>{{ variable }}</span>
                </div>
                <p v-if="filteredVariables.length == 0" class="sm text-slate-400 text-center px-3 py-2">No results</p>
            </div>

        </Teleport>

        <div
            tabindex="0"
            ref="editorRef"
            spellcheck="false"
            contenteditable="true"
            :class="[
                fullWidth ? 'w-full min-w-0' : 'w-80',
                'min-h-30 overflow-auto p-4 border border-gray-300 rounded-lg text-sm leading-6 font-medium text-gray-700 placeholder:text-gray-400 placeholder:font-normal focus:border-indigo-800 whitespace-pre-wrap wrap-break-word bg-white cursor-text nodrag nowheel',
                { 'is-empty': !localContent || localContent === '' }
            ]"
            @blur="onBlur"
            @mousedown.stop
            @input="handleInput"
            @keydown="handleKeydown"
            @click.stop="focusEditor"
            @focus="isFocused = true">
        </div>

  </div>
</template>

<script>
    import { Plus, EllipsisVertical, SquarePen, Trash } from 'lucide-vue-next'
    import Button from '@Partials/Button.vue'
    import Dropdown from '@Partials/Dropdown.vue'

    export default {
        name: 'ContentEditor',
        components: { Button, Dropdown, EllipsisVertical, SquarePen, Trash },
        props: {
            modelValue: { type: String, default: '' },
            fullWidth: { type: Boolean, default: true },
            showHeader: { type: Boolean, default: true },
            showActions: { type: Boolean, default: true },
        },
        emits: ['update:modelValue'],
        data() {
            return {
                Plus,
                query: '',
                editorRef: null,
                selectedIndex: 0,
                isFocused: false,
                showMentions: false,
                isInternalChange: null,
                menuPos: { top: 0, left: 0 },
                localContent: this.modelValue,
                variables: [
                    '@firstName',
                    '@lastName',
                    '@phoneNumber',
                    '@balance',
                    '@accountNumber'
                ]
            }
        },
        computed: {
            filteredVariables() {
                const q = this.query.toLowerCase().replace('@', '');
                if (!q) return this.variables;
                return this.variables.filter(v => {
                    const variableName = v.toLowerCase().replace('@', '');
                    return variableName.includes(q);
                });
            }
        },
        watch: {
            modelValue(newVal) {
                // If we caused the change by typing, ignore the update from parent
                if (this.isInternalChange) {
                    this.isInternalChange = false;
                    return;
                }

                // If external (like Template click), update the DOM
                if (newVal !== this.localContent) {
                    this.localContent = newVal;
                    if (this.editorRef) {
                        this.editorRef.innerText = newVal || '';
                        this.applyHighlighting(true); // Force re-render for templates
                    }
                }
            }
        },
        mounted() {
            this.editorRef = this.$refs.editorRef;
            if (this.modelValue) {
                this.editorRef.innerText = this.modelValue;
                this.applyHighlighting();
            }
        },
        methods: {
            focusEditor() {
                this.editorRef.focus();
                this.isFocused = true;
            },
            onBlur() {
                setTimeout(() => {
                    this.showMentions = false;
                    this.isFocused = false;
                }, 150);
            },
            handleInput() {
                let content = this.editorRef.innerText;

                if (content === '\n' || content === '') {
                    content = '';
                    this.editorRef.innerHTML = '';
                }

                this.localContent = content;
                this.isInternalChange = true; // Set flag to block the watcher
                this.$emit('update:modelValue', this.localContent);

                // Dirty Check: Only run highlighting if a variable is likely being typed
                // This prevents the "jump" on every normal letter
                const hasVariables = content.includes('@');
                if (hasVariables) {
                    this.applyHighlighting(false);
                }

                this.checkMentions();
            },
            async checkMentions() {
                const selection = window.getSelection();
                if (!selection || !selection.rangeCount) return;
                const range = selection.getRangeAt(0);
                const textNode = range.startContainer;
                let textContent = "";
                if (textNode.nodeType === Node.TEXT_NODE) {
                    textContent = textNode.textContent.substring(0, range.startOffset);
                } else {
                    textContent = textNode.innerText || "";
                }
                const match = textContent.match(/@\w*$/);
                if (match) {
                    this.query = match[0];
                    this.showMentions = true;
                    this.selectedIndex = 0;
                    await this.$nextTick();
                    this.updateMenuPosition();
                } else {
                    this.showMentions = false;
                }
            },
            updateMenuPosition() {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                const range = selection.getRangeAt(0).cloneRange();
                range.collapse(false);
                const rect = range.getBoundingClientRect();
                if (rect) {
                    this.menuPos = {
                        top: rect.bottom + window.scrollY + 5,
                        left: rect.left + window.scrollX
                    };
                }
            },
            insertMention(value) {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                const range = selection.getRangeAt(0);
                const textNode = range.startContainer;
                if (textNode.nodeType !== Node.TEXT_NODE) return;
                const content = textNode.textContent;
                const offset = range.startOffset;
                const textBeforeCursor = content.substring(0, offset);
                const match = textBeforeCursor.match(/@\w*$/);
                if (match) {
                    const startPos = match.index;
                    const textAfterCursor = content.substring(offset);
                    textNode.textContent = content.substring(0, startPos) + value + ' ' + textAfterCursor;
                    const newPos = startPos + value.length + 1;
                    const newRange = document.createRange();
                    newRange.setStart(textNode, newPos);
                    newRange.setEnd(textNode, newPos);
                    selection.removeAllRanges();
                    selection.addRange(newRange);
                }
                this.showMentions = false;
                this.handleInput();
            },
            addVariable(variable, closeDropdown) {
                this.editorRef.focus();
                document.execCommand('insertText', false, variable + ' ');
                this.handleInput();
                if (closeDropdown) closeDropdown();
            },
            handleKeydown(e) {
                if (this.showMentions) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % this.filteredVariables.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex - 1 + this.filteredVariables.length) % this.filteredVariables.length;
                    } else if (e.key === 'Enter' || e.key === 'Tab') {
                        e.preventDefault();
                        if (this.filteredVariables[this.selectedIndex]) {
                            this.insertMention(this.filteredVariables[this.selectedIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        this.showMentions = false;
                    }
                } else if (e.key === 'Tab') {
                    e.preventDefault();
                    document.execCommand('insertText', false, '  ');
                }
            },
            applyHighlighting(force = false) {
                if (!this.editorRef) return;

                const text = this.editorRef.innerText;
                const regex = /(@\w+)/g;

                let escaped = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                const newHtml = escaped.replace(regex, '<span class="variable-tag">$1</span>');

                // THE "DIRTY CHECK":
                // Only overwrite innerHTML if the number of <span> tags has changed.
                // This stops the cursor from jumping during normal word typing.
                const currentSpans = this.editorRef.querySelectorAll('.variable-tag').length;
                const nextSpans = (newHtml.match(/class="variable-tag"/g) || []).length;

                if (force || currentSpans !== nextSpans) {
                    const savedOffset = this.getCaretCharacterOffsetWithin(this.editorRef);
                    this.editorRef.innerHTML = newHtml;

                    if (this.isFocused) {
                        this.$nextTick(() => this.setCurrentCursorPosition(savedOffset));
                    }
                }
            },
            getCaretCharacterOffsetWithin(element) {
                let caretOffset = 0;
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const preCaretRange = range.cloneRange();
                    preCaretRange.selectNodeContents(element);
                    preCaretRange.setEnd(range.endContainer, range.endOffset);
                    caretOffset = preCaretRange.toString().length;
                }
                return caretOffset;
            },
            setCurrentCursorPosition(chars) {
                const selection = window.getSelection();
                const range = this.createRange(this.editorRef, { count: chars });
                if (range) {
                    range.collapse(false);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            },
            createRange(node, chars, range) {
                if (!range) { range = document.createRange(); range.selectNode(node); range.setStart(node, 0); }
                if (chars.count === 0) { range.setEnd(node, chars.count); }
                else if (node && chars.count > 0) {
                    if (node.nodeType === Node.TEXT_NODE) {
                    if (node.textContent.length < chars.count) { chars.count -= node.textContent.length; }
                    else { range.setEnd(node, chars.count); chars.count = 0; }
                    } else {
                    for (let lp = 0; lp < node.childNodes.length; lp++) {
                        range = this.createRange(node.childNodes[lp], chars, range);
                        if (chars.count === 0) break;
                    }
                    }
                }
                return range;
            }
        }
    }
</script>

<style scoped>
    [contenteditable] {
        position: relative;
        caret-color: #374151;
        line-height: 1.5rem;
        min-height: 80px;
        outline: none;
    }
    .is-empty:before {
        content: "Start typing... Use @ to insert variables";
        color: #9ca3af;
        font-weight: 400;
        pointer-events: none;
        position: absolute;
        left: 1rem;
        top: 1rem;
        font-size: 0.875rem;
        line-height: 1.5rem;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    :deep(.variable-tag) {
        background-color: #e0e7ff;
        color: #4338ca;
        padding: 0 2px;
        border-radius: 4px;
        font-weight: 600;
        display: inline;
    }
</style>
