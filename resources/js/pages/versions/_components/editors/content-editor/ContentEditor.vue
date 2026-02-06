<template>
    <div
        class="space-y-2 relative group/editor"
        :style="{ '--dynamic-placeholder': `'${placeholderText}'` }"
    >

        <div v-if="showHeader" class="flex items-center justify-between">
            <slot name="title">
                <label class="text-xs font-medium text-slate-600">Content</label>
            </slot>

            <div
                v-if="showActions"
                class="flex items-center gap-2 opacity-0 group-hover/editor:opacity-100 transition-opacity">

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
                                v-for="(variable, index) in variables"
                                @mouseenter="selectedIndex = index"
                                @mousedown.prevent="insertMention(variable)"
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
                    @mouseenter="selectedIndex = i"
                    :class="['px-2 py-1.5 text-xs cursor-pointer flex justify-between items-center transition-colors', i === selectedIndex ? 'bg-indigo-600 text-indigo-100' : 'text-slate-400 hover:bg-indigo-50']">
                    <span>{{ variable }}</span>
                </div>
                <p v-if="filteredVariables.length == 0" class="text-slate-400 text-center px-3 py-2 text-xs">No results</p>
            </div>
        </Teleport>

        <div
            tabindex="0"
            ref="editorRef"
            spellcheck="false"
            contenteditable="true"
            :class="[
                { 'h-[36px]' : type === 'input' },
                fullWidth ? 'w-full min-w-0' : 'w-80',
                type === 'input' ? 'input-mode' : 'textarea-mode',
                'editor-base',
                { 'is-empty': !localContent || localContent === '' }
            ]"
            @blur="onBlur"
            @mousedown.stop
            @input="handleInput"
            @keydown="handleKeydown"
            @click.stop="focusEditor"
            @focus="isFocused = true"
        ></div>

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
            placeholder: { type: String, default: null },
            fullWidth: { type: Boolean, default: true },
            showHeader: { type: Boolean, default: true },
            showActions: { type: Boolean, default: true },
            type: { type: String, default: 'textarea' }
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
                localContent: this.modelValue || '',
                variables: ['@firstName', '@lastName', '@phoneNumber', '@balance', '@accountNumber']
            }
        },
        computed: {
            filteredVariables() {
                const q = this.query.toLowerCase().replace('@', '');
                if (!q) return this.variables;
                return this.variables.filter(v => v.toLowerCase().includes(q));
            },
            placeholderText() {
                if (this.placeholder) return this.placeholder;
                return this.type === 'input'
                    ? "Enter text..."
                    : "Start typing... Use @ to insert variables";
            }
        },
        watch: {
            modelValue: {
                immediate: true,
                handler(newVal) {
                    if (this.isInternalChange) {
                        this.isInternalChange = false;
                        return;
                    }
                    this.localContent = newVal || '';

                    // Use nextTick to ensure editorRef exists if we are mounting
                    this.$nextTick(() => {
                        this.updateDom();
                    });
                }
            }
        },
        mounted() {
            this.editorRef = this.$refs.editorRef;
            this.updateDom();
        },
        methods: {
            // Centralized method to update the DOM
            updateDom() {
                if (!this.editorRef) return;

                // Use textContent instead of innerText to ensure we get data even if element is hidden/transitioning
                if (this.editorRef.textContent !== this.localContent) {
                    this.editorRef.textContent = this.localContent;
                }

                // Re-apply highlight to convert text to HTML tags
                this.applyHighlighting(true);
            },
            focusEditor() {
                if (!this.editorRef) return;
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
                if (!this.editorRef) return;
                // Use textContent for raw data extraction
                let content = this.editorRef.textContent;

                if (this.type === 'input') {
                    content = content.replace(/[\r\n]+/gm, "");
                }

                if (content === '\n' || content === '') {
                    content = '';
                    this.editorRef.innerHTML = '';
                }

                this.localContent = content;
                this.isInternalChange = true;
                this.$emit('update:modelValue', this.localContent);

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

                let textContent = textNode.nodeType === Node.TEXT_NODE
                    ? textNode.textContent.substring(0, range.startOffset)
                    : textNode.innerText || "";

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
                        top: rect.bottom + window.scrollY + (this.type === 'input' ? 2 : 5),
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
                if (!this.editorRef) return;
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
                        return;
                    }

                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex - 1 + this.filteredVariables.length) % this.filteredVariables.length;
                        return;
                    }

                    if (e.key === 'Enter' || e.key === 'Tab') {
                        e.preventDefault();
                        if (this.filteredVariables[this.selectedIndex]) {
                            this.insertMention(this.filteredVariables[this.selectedIndex]);
                        }
                        return;
                    }

                    if (e.key === 'Escape') {
                        this.showMentions = false;
                        return;
                    }
                }

                if (e.key === 'Enter') {
                    if (this.type === 'input') {
                        e.preventDefault();
                    }
                } else if (e.key === 'Tab') {
                    e.preventDefault();
                    document.execCommand('insertText', false, '  ');
                }
            },
            applyHighlighting(force = false) {
                if (!this.editorRef) return;

                // Use textContent for the base value to avoid formatting issues
                const text = this.editorRef.textContent;
                const regex = /(@[a-zA-Z0-9_]+)/g;
                let escaped = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                const newHtml = escaped.replace(regex, '<span class="variable-tag">$1</span>');

                // Compare against innerHTML to see if update is needed
                if (force || this.editorRef.innerHTML !== newHtml) {
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
    .editor-base {
        position: relative;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        line-height: 1.5rem;
        font-weight: 500;
        color: #374151;
        background-color: white;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .input-mode {
        padding: 0.3rem 0.85rem;
        white-space: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        display: block;
        scroll-padding-right: 0.85rem;
        -webkit-user-modify: read-write-plaintext-only;
    }

    .input-mode::-webkit-scrollbar { display: none; }

    .textarea-mode {
        min-height: 7.5rem;
        padding: 1rem;
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-y: auto;
    }

    :deep(.variable-tag) {
        background-color: #e0e7ff;
        color: #4338ca;
        padding: 0 4px;
        margin: 0 1px;
        border-radius: 4px;
        font-weight: 600;
        display: inline;
        line-height: inherit;
        pointer-events: none;
        white-space: nowrap;
    }

    .is-empty:before {
        color: #9ca3af;
        position: absolute;
        pointer-events: none;
        font-weight: 400;
        z-index: 1;
        /* We use the CSS variable here */
        content: var(--dynamic-placeholder);
    }

    .input-mode.is-empty:before {
        top: 0.3rem;
        left: 0.85rem;
        white-space: nowrap;
    }

    .textarea-mode.is-empty:before {
        top: 1rem;
        left: 1rem;
        white-space: pre-wrap;
    }

    .editor-base:empty:after {
        content: "\200B";
        display: inline-block;
        width: 0;
    }
</style>
