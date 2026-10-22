<!-- Notification Container for Toast Messages -->
<div 
    x-data="notificationManager()" 
    @notify.window="addNotification($event.detail)"
    class="fixed top-4 right-4 z-50 space-y-2"
    style="max-width: 24rem;"
>
    <template x-for="(notification, index) in notifications" :key="notification.id">
        <div 
            x-show="notification.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            :class="{
                'bg-green-50 border-green-200': notification.type === 'success',
                'bg-red-50 border-red-200': notification.type === 'error',
                'bg-yellow-50 border-yellow-200': notification.type === 'warning',
                'bg-blue-50 border-blue-200': notification.type === 'info'
            }"
            class="border rounded-lg shadow-lg p-4 w-full"
        >
            <div class="flex items-start">
                <!-- Icon -->
                <svg 
                    :class="{
                        'text-green-600': notification.type === 'success',
                        'text-red-600': notification.type === 'error',
                        'text-yellow-600': notification.type === 'warning',
                        'text-blue-600': notification.type === 'info'
                    }"
                    class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <template x-if="notification.type === 'success'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </template>
                    <template x-if="notification.type === 'warning'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </template>
                    <template x-if="notification.type === 'info'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </template>
                </svg>
                
                <!-- Message -->
                <div class="flex-1">
                    <p 
                        :class="{
                            'text-green-800': notification.type === 'success',
                            'text-red-800': notification.type === 'error',
                            'text-yellow-800': notification.type === 'warning',
                            'text-blue-800': notification.type === 'info'
                        }"
                        class="text-sm"
                        x-html="notification.message"
                    ></p>
                </div>
                
                <!-- Close Button -->
                <button 
                    @click="removeNotification(notification.id)"
                    :class="{
                        'text-green-600 hover:text-green-800': notification.type === 'success',
                        'text-red-600 hover:text-red-800': notification.type === 'error',
                        'text-yellow-600 hover:text-yellow-800': notification.type === 'warning',
                        'text-blue-600 hover:text-blue-800': notification.type === 'info'
                    }"
                    class="ml-3 flex-shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
function notificationManager() {
    return {
        notifications: [],
        nextId: 1,
        
        init() {
            // Check for session notifications on page load
            @if(session('notification'))
                this.addNotification({
                    type: '{{ session('notification.type') }}',
                    message: {!! json_encode(session('notification.message')) !!}
                });
            @endif
            
            @if(session('notifications'))
                @foreach(session('notifications') as $notification)
                    this.addNotification({
                        type: '{{ $notification['type'] }}',
                        message: {!! json_encode($notification['message']) !!}
                    });
                @endforeach
            @endif
            
            // Legacy support for old session keys
            @if(session('success'))
                this.addNotification({
                    type: 'success',
                    message: {!! json_encode(session('success')) !!}
                });
            @endif
            
            @if(session('error'))
                this.addNotification({
                    type: 'error',
                    message: {!! json_encode(session('error')) !!}
                });
            @endif
        },
        
        addNotification(data) {
            const notification = {
                id: this.nextId++,
                type: data.type || 'info',
                message: data.message,
                show: true
            };
            
            this.notifications.push(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.removeNotification(notification.id);
            }, 5000);
        },
        
        removeNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.show = false;
                // Remove from array after animation completes
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        }
    }
}

// Global function to trigger notifications from anywhere
window.notify = function(type, message) {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { type, message }
    }));
}
</script>
