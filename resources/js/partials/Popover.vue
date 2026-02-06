<template>
    <div
        class="relative inline-block"
        :class="wrapperClasses"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <div
            ref="triggerRef"
            @click.stop="toggleClick">
            <slot name="trigger">
                <svg class="w-4 h-4 text-gray-500 hover:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </slot>
        </div>

        <transition name="graceful-fade">
            <div
                v-if="isVisible"
                ref="popoverRef"
                @click.stop
                :class="[
                    'absolute z-50 w-auto min-w-max bg-white border border-slate-200 rounded-xl shadow-xl dark:bg-neutral-800 dark:border-neutral-700',
                    positionClasses
                ]"
            >
                <div :class="['absolute w-2 h-2 bg-white border-slate-200 rotate-45 dark:bg-neutral-800 dark:border-neutral-700', arrowClasses]"></div>

                <div class="relative z-10">
                    <slot name="content">
                        <div class="max-w-80 text-xs leading-5 px-4 py-2 whitespace-normal text-slate-600 dark:text-neutral-400">
                            {{ content }}
                        </div>
                    </slot>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'Popover',
    props: {
        content: {
            type: String,
            default: ""
        },
        trigger: {
            type: String,
            default: "hover",
            validator: (value) => ["click", "hover"].includes(value),
        },
        position: {
            type: String,
            default: "bottom",
            validator: (value) => ["top", "bottom", "left", "right"].includes(value),
        },
        wrapperClasses: {
            type: String,
            default: ""
        },
    },
    data() {
        return {
            isVisible: false,
        };
    },
    computed: {
        positionClasses() {
            const map = {
                top: 'bottom-full left-1/2 -translate-x-1/2 mb-3',
                bottom: 'top-full left-1/2 -translate-x-1/2 mt-3',
                left: 'right-full top-1/2 -translate-y-1/2 mr-3',
                right: 'left-full top-1/2 -translate-y-1/2 ml-3'
            };
            return map[this.position] || map.bottom;
        },
        arrowClasses() {
            const map = {
                top: 'bottom-[-5px] left-1/2 -translate-x-1/2 border-b border-r',
                bottom: 'top-[-5px] left-1/2 -translate-x-1/2 border-t border-l',
                left: 'right-[-5px] top-1/2 -translate-y-1/2 border-t border-r',
                right: 'left-[-5px] top-1/2 -translate-y-1/2 border-b border-l'
            };
            return map[this.position] || map.bottom;
        }
    },
    methods: {
        handleMouseEnter() {
            if (this.trigger === 'hover') this.isVisible = true;
        },
        handleMouseLeave() {
            if (this.trigger === 'hover') this.isVisible = false;
        },
        toggleClick() {
            if (this.trigger === 'click') this.isVisible = !this.isVisible;
        },
        handleClickOutside(event) {
            const trigger = this.$refs.triggerRef;
            const popover = this.$refs.popoverRef;

            if (this.isVisible &&
                trigger && !trigger.contains(event.target) &&
                popover && !popover.contains(event.target)) {
                this.isVisible = false;
            }
        }
    },
    mounted() {
        document.addEventListener('mousedown', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('mousedown', this.handleClickOutside);
    }
};
</script>

<style scoped>
.graceful-fade-enter-active {
    transition: opacity 0.2s ease-out;
}

.graceful-fade-leave-active {
    transition: opacity 0.2s ease-in;
    pointer-events: none;
}

.graceful-fade-enter-from,
.graceful-fade-leave-to {
    opacity: 0;
}
</style>
