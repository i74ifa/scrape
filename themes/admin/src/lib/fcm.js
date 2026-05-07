import axios from "axios";
import { initializeApp, getApps } from "firebase/app";
import {
    getMessaging,
    getToken,
    onMessage,
    isSupported,
} from "firebase/messaging";

const firebaseConfig = {
    apiKey: "AIzaSyDmz49YjjLWe7edNZtc2BiHv9y2Eka_Oek",
    authDomain: "menuiq-72a80.firebaseapp.com",
    projectId: "menuiq-72a80",
    storageBucket: "menuiq-72a80.firebasestorage.app",
    messagingSenderId: "1075004606116",
    appId: "1:1075004606116:web:9b8a20a3d4641019a28831",
    measurementId: "G-Z51GRTB7XN",
};

let messagingInstance = null;
let onMessageBound = false;

function getMessagingInstance() {
    if (messagingInstance) return messagingInstance;
    const app = getApps().length ? getApps()[0] : initializeApp(firebaseConfig);
    messagingInstance = getMessaging(app);
    return messagingInstance;
}

async function registerServiceWorker() {
    if (!("serviceWorker" in navigator)) return null;
    try {
        const existing = await navigator.serviceWorker.getRegistration(
            "/firebase-messaging-sw.js",
        );
        if (existing) return existing;
        return await navigator.serviceWorker.register(
            "/firebase-messaging-sw.js",
        );
    } catch {
        return null;
    }
}

async function sendTokenToServer(token) {
    try {
        await axios.post(route("admin.fcm.token"), { device_token: token });
    } catch {
        // non-fatal — token will retry on next mount
    }
}

function showForegroundNotification(payload) {
    if (!("Notification" in window)) return;
    if (Notification.permission !== "granted") return;
    const title = payload?.notification?.title || "";
    const body = payload?.notification?.body || "";
    if (!title && !body) return;
    new Notification(title, {
        body,
        icon: "/logo.jpg",
        data: payload?.data,
    });
}

export async function initAdminFcm({ vapidKey } = {}) {
    try {
        if (!vapidKey) return;
        if (!(await isSupported())) return;
        if (!("Notification" in window)) return;

        let permission = Notification.permission;
        if (permission === "default") {
            permission = await Notification.requestPermission();
        }
        if (permission !== "granted") return;

        const registration = await registerServiceWorker();
        if (!registration) return;

        const messaging = getMessagingInstance();
        const token = await getToken(messaging, {
            vapidKey,
            serviceWorkerRegistration: registration,
        });

        if (!token) return;
        await sendTokenToServer(token);

        if (!onMessageBound) {
            onMessageBound = true;
            onMessage(messaging, showForegroundNotification);
        }
    } catch {
        // silent — FCM is best-effort
    }
}
