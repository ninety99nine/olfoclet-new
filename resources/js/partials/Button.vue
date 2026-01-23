<template>

    <component :is="skeleton ? 'ShineEffect' : 'div'" :class="wrapperClass">

        <button
            @click.prevent.stop="onClick"
            :class="[buttonClasses, buttonClass]">

            <!-- Spinning Loader -->
            <Loader v-if="loading" :type="loaderType"></Loader>

            <div v-else class="flex justify-center items-center space-x-1 whitespace-nowrap">

                <!-- Left Icon -->
                <component v-if="leftIcon" :is="leftIcon" :size="leftIconSize" />

                <!-- Content -->
                <span v-if="showCopiedText">{{ copyStatus }}</span>
                <slot v-else></slot>

                <!-- Right Icon -->
                <component v-if="rightIcon" :is="rightIcon" :size="rightIconSize" />

            </div>

        </button>

    </component>

</template>

<script>

    import Loader from "@Partials/Loader.vue";
    import ShineEffect from '@Partials/ShineEffect.vue';

    export default {
        components: { ShineEffect, Loader },
        props: {
            action: {
                type: [Function, null],
                default: null
            },
            mode: {
                type: String,
                default: 'solid',
                validator: (value) => [
                    'solid', 'outline'
                ].includes(value)
            },
            type: {
                type: String,
                default: 'primary',
                validator: (value) => [
                    'primary', 'success', 'warning', 'danger', 'light', 'outline', 'gradient',
                    'basic'


                /*
                    'light', 'primary', 'success', 'warning', 'danger', 'ghost', 'dark',
                    'outline', 'outlineDanger', 'bare', 'bareDanger'
                */
                ].includes(value)
            },
            disabled: {
                type: Boolean,
                default: false
            },
            skeleton: {
                type: Boolean,
                default: false
            },
            loading: {
                type: Boolean,
                default: false
            },
            size: {
                type: String,
                default: 'md',
                validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
            },
            leftIcon: {
                type: [Object, Function, null],
                default: null
            },
            leftIconSize: {
                type: String,
                default: '16',
            },
            rightIcon: {
                type: [Object, Function, null],
                default: null
            },
            rightIconSize: {
                type: String,
                default: '16',
            },
            wrapperClass: {
                type: String,
                default: null,
            },
            buttonClass: {
                type: String,
                default: null,
            },
            copyContent: {
                type: [String, null],
                default: null
            },
            loaderType: {
                type: String,
                default: 'light'
            }
        },
        data() {
            return {
                copyStatus: null,
                copyTimeout: null,
                showCopiedText: false
            }
        },
        computed: {
            buttonClasses() {

                var classes = [];
                const baseClasses = ['group flex justify-center items-center font-medium'];

                const paddingClasses = {
                    xs: "py-1.5 px-3",
                    sm: "py-1.5 px-4",
                    md: "px-5 py-3",
                    lg: "px-6 py-4"
                };

                const fontSizeClasses = {
                    xs: "text-xs",
                    sm: "text-xs md:text-sm",
                    md: "text-xs md:text-sm",
                    lg: "text-base md:text-lg"
                };

                const typeClasses = {

                    gradient: 'bg-linear-to-r from-indigo-600 to-purple-600 text-white',
                    danger: (this.mode == 'solid' ? 'bg-red-600 hover:bg-red-700 text-white' : 'border border-red-300 bg-red-50 text-red-600 hover:bg-red-100'),
                    light: (this.mode == 'solid' ? 'bg-white text-slate-700 border border-slate-100' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'),
                    white: (this.mode == 'solid' ? 'bg-white text-indigo-700 border border-slate-100' : 'border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50'),
                    success: (this.mode == 'solid' ? 'bg-green-600 hover:bg-green-700 text-white' : 'border border-green-300 bg-green-50 text-green-600 hover:bg-green-100'),
                    warning: (this.mode == 'solid' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'border border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100'),
                    primary: (this.mode == 'solid' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'border border-indigo-300 bg-indigo-50 text-indigo-600 hover:bg-indigo-100'),

                    disabled: "bg-gray-100 text-gray-300",
                    skeleton: "bg-gray-100 text-gray-300",
                    basic: "hover:bg-slate-100",
                };

                classes.push(baseClasses);
                classes.push(fontSizeClasses[this.size]);

                if (!['ghost', 'bare', 'bareDanger'].includes(this.type)) {
                    classes.push(paddingClasses[this.size]);
                }

                if (this.skeleton) {
                    classes.push(typeClasses['skeleton'], 'cursor-not-allowed');
                } else if (this.disabled) {
                    classes.push(typeClasses[this.type], 'opacity-50 cursor-not-allowed');
                } else {
                    classes.push(typeClasses[this.type], 'cursor-pointer hover:scale-105 active:scale-100 transition-all duration-300');
                }

                return classes;
            }
        },
        methods: {
            onClick(event) {
                if(this.disabled) return;
                if(this.copyContent) this.copyToClipboard();
                if(this.action) this.action(event);
            },
            copyToClipboard() {
                if (!this.copyContent) return;

                navigator.clipboard.writeText(this.copyContent)
                    .then(() => {
                        this.copyStatus = 'Copied!';
                        this.showCopiedText = true;

                        // Clear any existing timeout
                        if (this.copyTimeout) {
                            clearTimeout(this.copyTimeout);
                        }

                        // Hide "Copied!" after 2 seconds
                        this.copyTimeout = setTimeout(() => {
                            this.showCopiedText = false;
                        }, 2000);
                    })
                    .catch(err => {
                        this.copyStatus = 'Failed to copy!';
                        console.error('Failed to copy:', err);
                    });
            },
        }
    };

</script>
