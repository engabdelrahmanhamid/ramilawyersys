importScripts('https://www.gstatic.com/firebasejs/10.12.4/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.4/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: 'AIzaSyDSoOb-g_hyKB6UTBMV3tD4DKxkbCVnlQo',
    authDomain: 'happytimes2-69033.firebaseapp.com',
    databaseURL: 'https://happytimes2-69033.firebaseio.com',
    projectId: 'happytimes2-69033',
    storageBucket: 'happytimes2-69033.appspot.com',
    messagingSenderId: '547470730206',
    appId: '1:547470730206:web:0070bea06350c66c9fb34f',
    measurementId: 'G-3TWXLS9BN6',
  };

  firebase.initializeApp(firebaseConfig);
  const messaging = firebase.messaging();

  messaging.onBackgroundMessage(function(payload) {
    console.log('Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
      body: payload.notification.body,
    //   icon: '/firebase-logo.png'
    };
  
    self.registration.showNotification(notificationTitle,
      notificationOptions);
  });