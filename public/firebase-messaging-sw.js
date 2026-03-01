importScripts('https://www.gstatic.com/firebasejs/10.7.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyCk_AOW1gaki_wlC-Ubh10j92v6mE-XoX4",
  authDomain: "gold-security-695e8.firebaseapp.com",
  projectId: "gold-security-695e8",
  storageBucket: "gold-security-695e8.firebasestorage.app",
  messagingSenderId: "420165823572",
  appId: "1:420165823572:web:4fadb244cb3d69e04751c1"
});

const messaging = firebase.messaging();
messaging.onBackgroundMessage(function(payload) {
  var title = (payload.notification && payload.notification.title) ? payload.notification.title : 'Notification';
  var options = { body: (payload.notification && payload.notification.body) ? payload.notification.body : '' };
  self.registration.showNotification(title, options);
});
