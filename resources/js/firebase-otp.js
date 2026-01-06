
// Check if Firebase is initialized
if (typeof firebase === 'undefined') {
    alert('Firebase is not loaded. Please check your script tags.');
}

// Get the Firebase configuration from the window object
const firebaseConfig = window.firebaseConfig;

if (!firebaseConfig) {
    alert('Firebase configuration is not available. Please check your blade file.');
}

// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();

// Function to send OTP
window.sendOtp = function() {
    const phoneNumber = document.getElementById('phone').value;

    if (!phoneNumber) {
        alert('Please enter a phone number.');
        return;
    }

    const appVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible',
        'callback': (response) => {
            // reCAPTCHA solved, allow signInWithPhoneNumber.
        }
    });

    auth.signInWithPhoneNumber(phoneNumber, appVerifier)
        .then((confirmationResult) => {
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
            // Show the OTP modal
            $('#otp-modal').modal('show');
        }).catch((error) => {
            // Error; SMS not sent
            console.error('Error sending OTP:', error);
            let errorMessage = 'Error sending OTP. Please try again.';
            if (error.code === 'auth/invalid-phone-number') {
                errorMessage = 'Invalid phone number. Please enter a valid phone number in E.164 format (e.g., +15551234567).';
            } else if (error.code === 'auth/too-many-requests') {
                errorMessage = 'Too many requests. Please try again later.';
            }
            alert(errorMessage);
        });
}

// Function to verify OTP
window.verifyOtp = function() {
    const code = document.getElementById('otp').value;

    if (!code) {
        alert('Please enter the OTP code.');
        return;
    }

    window.confirmationResult.confirm(code).then((result) => {
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
            id_token: firebaseToken
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
            response.json().then(data => {
                alert('Error linking phone number: ' + data.message);
            });
        }
    }).catch(error => {
        console.error('Error linking phone number:', error);
        alert('Error linking phone number. Please try again.');
    });
}
