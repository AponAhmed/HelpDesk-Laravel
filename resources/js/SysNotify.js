export default class SysNotify {
    constructor(options = {}) {
        this.title = options.title || 'Notification';
        this.body = options.body || '';
        this.icon = options.icon || '';
        this.appName = options.appName || 'Helpdesk';
        this.link = options.link || null;
        this.requireInteraction = options.requireInteraction || false;
        this.silent = options.silent || false;

        // Request permission when the class is instantiated
        if (!window.isSecureContext) {
            console.warn('Notifications require a secure context. Please use HTTPS.');
        } else {
            if (Notification.permission !== "granted") {
                Notification.requestPermission().then(permission => {
                    if (permission !== "granted") {
                        console.warn('Notification permission denied');
                    }
                });
            }
        }

    }



    // Method to show the notification
    showNotification() {
        if (Notification.permission === "granted") {
            const notification = new Notification(this.title, {
                body: this.body,
                icon: this.icon,
                requireInteraction: this.requireInteraction,
                silent: this.silent,
                data: {
                    appName: this.appName,
                    link: this.link
                }
            });

            notification.onclick = this.handleNotificationClick.bind(this);
        } else {
            console.warn('Notification permission is not granted.');
        }
    }

    // Handle notification click event
    handleNotificationClick(event) {
        event.preventDefault();

        // If a custom link is provided, open it in a new tab
        if (this.link) {
            window.open(this.link, '_blank');
        } else {
            console.log(`${this.appName} notification clicked.`);
        }

        // Close the notification
        event.target.close();
    }

    // Static method to check if notifications are supported
    static isSupported() {
        return "Notification" in window;
    }

    // Static method to request notification permission
    static requestPermission() {
        return Notification.requestPermission();
    }
}

// Example usage:
// const notification = new SysNotify({
//     title: 'New Mail Arrived',
//     body: 'You have received a new email from John Doe.',
//     icon: 'path/to/icon.png',
//     appName: 'MailApp',
//     link: 'https://mailapp.example.com/inbox',
//     requireInteraction: true,
//     silent: false
// });

// // Show the notification
// notification.showNotification();
