<template>
    <img
        :class="height"
        :src="logoPath"
        :alt="`${appName} Logo`"
        class="transition-all duration-300"
    >
</template>

<script>
export default {
    name: 'Logo',
    props: {
        height: {
            type: String,
            default: 'h-16'
        },
        type: {
            type: String,
            default: 'text',
            validator: (value) => ['text', 'swirl'].includes(value)
        },
        white: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            appName: import.meta.env.VITE_APP_NAME || 'Telcoflo'
        }
    },
    computed: {
        logoPath() {
            // Determine if we want the full text logo or just the swirl icon
            const variant = this.type === 'text' ? 'swirl-text' : 'swirl';

            // Determine if we need the white suffix
            const colorSuffix = this.white ? '-white' : '';

            return `/logo/${variant}${colorSuffix}.png`;
        }
    }
};
</script>
