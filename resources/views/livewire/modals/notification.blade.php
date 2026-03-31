<div
    x-data="{
        notifications: [],
        maxVisible: 3,
        counter: 0,

        durations: {
            success: 4000,
            info: 4000,
            warning: 6000,
            error: 8000,
        },


        add(message, type = 'success') {
            const id = ++this.counter;
            const duration = this.durations[type] || 4000;

            const notification = {
                id,
                message,
                type,
                duration,
                removing: false,
            };

            this.notifications.push(notification);

            // Remove oldest if exceeding max
            while (this.notifications.length > this.maxVisible) {
                this.dismiss(this.notifications[0].id);
            }

            // Auto-dismiss after duration
            setTimeout(() => {
                this.dismiss(id);
            }, duration);
        },

        dismiss(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n && !n.removing) {
                n.removing = true;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },
    }"
    x-on:toast.window="add($event.detail.message, $event.detail.type)"
    class="fixed top-4 right-4 z-50 flex flex-col gap-3 pointer-events-none"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="!notification.removing"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="pointer-events-auto w-80 max-w-sm overflow-hidden rounded-lg bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-lg ring-1 ring-black/5 dark:ring-white/10"
        >
            <div class="p-4">
                <div class="flex items-start">
                    {{-- Icon --}}
                    <div class="shrink-0">
                        {{-- Success icon --}}
                        <template x-if="notification.type === 'success'">
                            <svg class="size-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                            </svg>
                        </template>

                        {{-- Error icon --}}
                        <template x-if="notification.type === 'error'">
                            <svg class="size-6 text-rose-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                            </svg>
                        </template>

                        {{-- Warning icon --}}
                        <template x-if="notification.type === 'warning'">
                            <svg class="size-6 text-amber-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </template>

                        {{-- Info icon --}}
                        <template x-if="notification.type === 'info'">
                            <svg class="size-6 text-blue-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v.008M12 12v3.75" />
                            </svg>
                        </template>
                    </div>

                    {{-- Message --}}
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.message"></p>
                    </div>

                    {{-- Close button --}}
                    <div class="ml-4 flex shrink-0">
                        <button
                            type="button"
                            @click="dismiss(notification.id)"
                            class="inline-flex rounded-md text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            <span class="sr-only">Cerrar</span>
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </template>
</div>
