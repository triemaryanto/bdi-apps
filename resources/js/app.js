import './bootstrap';
import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup, signOut, onAuthStateChanged } from 'firebase/auth';
import { getFirestore } from 'firebase/firestore';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

const firebaseConfig = {
    apiKey:            window._firebase?.apiKey,
    authDomain:        window._firebase?.authDomain,
    projectId:         window._firebase?.projectId,
    storageBucket:     window._firebase?.storageBucket,
    messagingSenderId: window._firebase?.messagingSenderId,
    appId:             window._firebase?.appId,
};

const app      = initializeApp(firebaseConfig);
const auth     = getAuth(app);
const db       = getFirestore(app);

// Google Sign-In
window.googleSignIn = async () => {
    const provider = new GoogleAuthProvider();
    try {
        const result = await signInWithPopup(auth, provider);
        const idToken = await result.user.getIdToken();

        const res = await fetch('/auth/firebase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ id_token: idToken }),
        });

        const data = await res.json();
        if (data.redirect) window.location.href = data.redirect;
    } catch (err) {
        console.error('Login gagal:', err.message);
        alert('Login gagal: ' + err.message);
    }
};

window.firebaseSignOut = async () => {
    await signOut(auth);
    window.location.href = '/logout';
};

// FCM Push Notification
const initFCM = async () => {
    if (!('Notification' in window) || !window._firebase?.vapidKey) return;
    try {
        const messaging = getMessaging(app);
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        const token = await getToken(messaging, { vapidKey: window._firebase.vapidKey });
        if (token) {
            fetch('/fcm/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ token }),
            });
        }

        onMessage(messaging, payload => {
            const { title, body } = payload.notification ?? {};
            if (title) new Notification(title, { body, icon: '/icons/icon-192.png' });
        });
    } catch (e) {
        console.warn('FCM init failed:', e.message);
    }
};

document.addEventListener('DOMContentLoaded', initFCM);

export { auth, db };
