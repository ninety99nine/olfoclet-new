<template>
    <div
        class="relative inline-block"
        @mouseenter="showTooltip"
        @mouseleave="hideTooltip">

        <div ref="triggerRef" class="cursor-help">
            <slot name="trigger">
                <svg :class="triggerClass" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 0 1-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 0 1-.837.552c-.676.328-1.028.774-1.028 1.152v.75a.75.75 0 0 1-1.5 0v-.75c0-1.279 1.06-2.107 1.875-2.502.182-.088.351-.199.503-.331.83-.727.83-1.857 0-2.584ZM12 18a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                </svg>
            </slot>
        </div>

        <transition name="graceful-fade">
            <div
                v-if="isVisible"
                role="tooltip"
                :class="[
                    'absolute z-50 py-1.5 px-3 bg-gray-900 text-xs font-medium text-white rounded-md shadow-lg pointer-events-none dark:bg-neutral-800 border border-white/10',
                    width,
                    width === 'w-auto' ? 'whitespace-nowrap' : 'whitespace-normal leading-relaxed text-center',
                    positionClasses
                ]"
            >
                <slot name="content">
                    {{ content }}
                </slot>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'Tooltip',
    props: {
        position: {
            type: String,
            default: "top",
            validator: (value) => ["top", "bottom", "left", "right"].includes(value)
        },
        content: {
            type: String,
            default: "This is a tooltip.",
        },
        triggerClass: {
            type: [String, Array, Object],
            default: "w-4 h-4 text-gray-300 hover:text-gray-400",
        },
        width: {
            type: String,
            default: "w-60"
        }
    },
    data() {
        return {
            isVisible: false
        };
    },
    computed: {
        positionClasses() {
            const map = {
                top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
                bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
                left: 'right-full top-1/2 -translate-y-1/2 mr-2',
                right: 'left-full top-1/2 -translate-y-1/2 ml-2'
            };
            return map[this.position] || map.top;
        }
    },
    methods: {
        showTooltip() {
            this.isVisible = true;
        },
        hideTooltip() {
            this.isVisible = false;
        }
    }
};
</script>

<style scoped>
.graceful-fade-enter-active {
    transition: all 0.2s ease-out;
}

.graceful-fade-leave-active {
    transition: all 0.15s ease-in;
}

.graceful-fade-enter-from,
.graceful-fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
