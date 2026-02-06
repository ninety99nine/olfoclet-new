<template>
    <div>
        <slot
            name="trigger"
            :showModal="showModal">
            <Button
                v-if="triggerText"
                :size="triggerSize"
                :type="triggerType"
                :action="showModal"
                :skeleton="triggerLoading"
                :leftIcon="leftTriggerIcon"
                :rightIcon="rightTriggerIcon"
                :leftIconSize="leftTriggerIconSize"
                :rightIconSize="rightTriggerIconSize">
                <span>{{ triggerText }}</span>
            </Button>
        </slot>

        <Teleport :to="backdropTarget">
            <div
                v-if="visible"
                @click.stop="dismissable ? dismissModal() : null"
                :class="[
                    'fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center',
                    /* Fullscreen or scrollOnContent both require center alignment and hidden overflow */
                    { 'items-center overflow-hidden': scrollOnContent || size === 'fullscreen' },
                    { 'items-start overflow-y-auto p-4 sm:p-6': !scrollOnContent && size !== 'fullscreen' }
                ]">

                <div
                    @click.stop
                    :class="[
                        'relative transform transition-all duration-300 w-full flex flex-col',
                        { 'scale-100 opacity-100': isOpen, 'scale-95 opacity-0': !isOpen },

                        /* Layout Logic based on Size prop */
                        size === 'fullscreen'
                            ? 'h-[calc(100vh-2rem)] md:h-[calc(100vh-5rem)] m-4 md:m-10 max-w-none'
                            : {
                                'max-w-sm': size === 'sm',
                                'max-w-xl': size === 'md',
                                'max-w-4xl': size === 'lg',
                                'my-4 sm:my-12': !scrollOnContent
                              }
                    ]">

                    <div :class="[
                            { 'bg-white border border-gray-200 shadow-lg' : !bareMode },
                            'relative rounded-xl dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70 flex flex-col h-full overflow-hidden'
                        ]">

                        <button
                            type="button"
                            v-if="!bareMode"
                            aria-label="Close"
                            @click.stop="hideModal"
                            class="absolute z-10 top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full border border-transparent bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 cursor-pointer">
                            <X size="20" />
                        </button>

                        <div
                            v-if="header || $slots.header"
                            class="p-4 border-b border-gray-200 dark:border-neutral-700 shrink-0">
                            <slot name="header" :hideModal="hideModal">
                                <h3 v-if="header" class="font-bold dark:text-white">
                                    {{ header }}
                                </h3>
                            </slot>
                            <slot name="subheader" :hideModal="hideModal">
                                <h3 v-if="subheader" class="text-sm text-gray-500 dark:text-white">
                                    {{ subheader }}
                                </h3>
                            </slot>
                        </div>

                        <div
                            v-if="content || $slots.content"
                            :class="[
                                'flex-1 flex flex-col min-h-0',
                                { 'p-4' : contentClass == null && !bareMode },
                                { 'overflow-y-auto': scrollOnContent && size !== 'fullscreen' },
                                { 'overflow-hidden': size === 'fullscreen' }
                            ]">
                            <slot name="content" :hideModal="hideModal">
                                <p class="text-sm text-gray-700 dark:text-neutral-400">
                                    {{ content }}
                                </p>
                            </slot>
                        </div>

                        <slot name="footer" :hideModal="hideModal">
                            <div
                                v-if="showFooter && (showDelineButton || showApproveButton)"
                                class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700 shrink-0">

                                <Button
                                    :size="declineSize"
                                    :type="declineType"
                                    v-if="showDelineButton"
                                    buttonClass="rounded-lg"
                                    :loading="declineLoading"
                                    :leftIcon="leftDeclineIcon"
                                    :disabled="declineDisabled"
                                    :rightIcon="rightDeclineIcon"
                                    :action="declineAction ? () => declineAction(hideModal) : hideModal">
                                    <span>{{ declineText }}</span>
                                </Button>

                                <Button
                                    :size="approveSize"
                                    :type="approveType"
                                    buttonClass="rounded-lg"
                                    v-if="showApproveButton"
                                    :loading="approveLoading"
                                    :leftIcon="leftApproveIcon"
                                    :disabled="approveDisabled"
                                    :rightIcon="rightApproveIcon"
                                    :action="approveAction ? () => approveAction(hideModal) : hideModal">
                                    <span>{{ approveText }}</span>
                                </Button>
                            </div>
                        </slot>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
    import { X } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';
    import { generateUniqueId } from '@Utils/generalUtils.js';

    export default {
        name: 'Modal',
        components: { X, Button },
        props: {
            bareMode: { type: Boolean, default: false },
            triggerLoading: { type: Boolean, default: false },
            triggerText: { type: [String, null], default: null },
            triggerSize: { type: String, default: 'sm' },
            triggerType: { type: String, default: 'light' },
            leftTriggerIcon: { type: [Object, Function, null], default: null },
            rightTriggerIcon: { type: [Object, Function, null], default: null },
            leftTriggerIconSize: { type: String, default: '16' },
            rightTriggerIconSize: { type: String, default: '16' },
            size: {
                type: String,
                default: "sm",
                validator: (value) => ["sm", "md", "lg", "fullscreen"].includes(value),
            },
            header: { type: [String, null], default: null },
            subheader: { type: [String, null], default: null },
            content: { type: [String, null], default: null },
            contentClass: { type: String, default: null },
            showFooter: { type: Boolean, default: true },
            showDelineButton: { type: Boolean, default: true },
            declineText: { type: String, default: 'Cancel' },
            declineType: { type: String, default: 'light' },
            declineSize: { type: String, default: 'sm' },
            leftDeclineIcon: { type: [Object, Function, null], default: null },
            rightDeclineIcon: { type: [Object, Function, null], default: null },
            declineAction: { type: [Function, null], default: null },
            declineDisabled: { type: Boolean, default: false },
            declineLoading: { type: Boolean, default: false },
            showApproveButton: { type: Boolean, default: true },
            approveText: { type: String, default: 'Yes' },
            approveSize: { type: String, default: 'sm' },
            approveType: { type: String, default: 'primary' },
            leftApproveIcon: { type: [Object, Function, null], default: null },
            rightApproveIcon: { type: [Object, Function, null], default: null },
            approveAction: { type: [Function, null], default: null },
            approveLoading: { type: Boolean, default: false },
            approveDisabled: { type: Boolean, default: false },
            scrollOnContent: { type: Boolean, default: true },
            dismissable: { type: Boolean, default: true },
            onShow: { type: [Function, null], default: null },
            onHide: { type: [Function, null], default: null },
            onDismiss: { type: [Function, null], default: null },
            targetClass: { type: String, default: 'body' },
        },
        data() {
            return {
                isOpen: false,
                visible: false,
                backdropTarget: 'body',
                uniqueId: generateUniqueId('modal'),
            };
        },
        watch: {
            targetClass() {
                this.updateBackdropTarget();
            },
        },
        methods: {
            updateBackdropTarget() {
                if (!this.$el) {
                    this.backdropTarget = 'body';
                    return;
                }

                let parent = this.$el.parentElement;
                const targetClasses = this.targetClass.split(',').map(cls => cls.trim());

                while (parent && parent !== document.body) {
                    if (targetClasses.some(cls => parent.classList.contains(cls))) {
                        this.backdropTarget = parent;
                        return;
                    }
                    parent = parent.parentElement;
                }

                this.backdropTarget = 'body';
            },
            showModal() {
                this.visible = true;
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';

                setTimeout(() => {
                    this.isOpen = true;
                }, 100);
                if (this.onShow) this.onShow();
            },
            hideModal() {
                this.isOpen = false;
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';

                setTimeout(() => {
                    this.visible = false;
                }, 250);

                if (this.onHide) this.onHide();
            },
            dismissModal() {
                if(this.dismissable) {
                    if (this.onDismiss) {
                        this.onDismiss();
                    }else{
                        this.hideModal();
                    }
                }
            }
        },
        mounted() {
            this.updateBackdropTarget();
        },
        beforeUnmount() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }
    };
</script>
