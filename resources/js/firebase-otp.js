
// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from "firebase/auth";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: process.env.MIX_FIREBASE_API_KEY,
  authDomain: process.env.MIX_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.MIX_FIREBASE_PROJECT_ID,
  storageBucket: process.env.MIX_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.MIX_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.MIX_FIREBASE_APP_ID
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Function to send OTP
window.sendOtp = function() {
  const phoneNumber = document.getElementById('phone').value;
  const appVerifier = new RecaptchaVerifier('recaptcha-container', {}, auth);

  signInWithPhoneNumber(auth, phoneNumber, appVerifier)
    .then((confirmationResult) => {
      // SMS sent. Prompt user to type the code from the message, then sign the
      // user in with confirmationResult.confirm(code).
      window.confirmationResult = confirmationResult;
      // Show the OTP modal
      $('#otp-modal').modal('show');
    }).catch((error) => {
      // Error; SMS not sent
      console.error('Error sending OTP:', error);
      alert('Error sending OTP. Please try again.');
    });
}

// Function to verify OTP
window.verifyOtp = function() {
  const code = document.getElementById('otp').value;
  confirmationResult.confirm(code).then((result) => {
    // User signed in successfully.
    const user = result.user;
    // Link the phone number to the user's account on your server
    linkPhoneNumber(user.stsTokenManager.accessToken);
  }).catch((error) => {
    // User couldn't sign in (bad verification code?)
    console.error('Error verifying OTP:', error);
    alert('Error verifying OTP. Please try again.');
  });
}

// Function to link phone number to user's account
function linkPhoneNumber(firebaseToken) {
  // Send the Firebase token to your server to link the phone number
  fetch('/firebase/link-phone', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      firebase_token: firebaseToken
    })
  }).then(response => {
    if (response.ok) {
      // Phone number linked successfully
      alert('Phone number verified successfully!');
      // Close the OTP modal
      $('#otp-modal').modal('hide');
      // Refresh the page to show the updated profile
      location.reload();
    } else {
      // Error linking phone number
      alert('Error linking phone number. Please try again.');
    }
  }).catch(error => {
    console.error('Error linking phone number:', error);
    alert('Error linking phone number. Please try again.');
  });
}
