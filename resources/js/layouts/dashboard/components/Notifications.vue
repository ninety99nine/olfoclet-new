<template>

</template>

<script>
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

export default {
  inject: ['notificationState'],

  computed: {
    notifications() {
      return this.notificationState.notifications
    }
  },

  watch: {
    'notifications.length'(newLength, oldLength) {
      if (newLength > oldLength) {

        let icon;
        const notification = this.notifications[this.notifications.length - 1];

        if(notification.type == 'success') {
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>`;
        }else if(notification.type == 'warning') {
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-triangle-alert-icon lucide-triangle-alert"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`;
        }else if(notification.type == 'info') {
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`;
        }else{
            icon = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`;
        }

        const text = `
            <div class="flex items-center space-x-2 p-4">
                ${icon}
                <p class="text-sm" style="font-family: var(--default-font-family);">${notification.message}</p>
            </div>`;

        toast(text, {
            limit: 5,
            newestOnTop: true,
            pauseOnHover: true,
            hideProgressBar: true,
            dangerouslyHTMLString: true,
            toastClassName: notification.type,
            autoClose: notification.duration ?? 3000,
        })
      }
    }
  }
}
</script>
