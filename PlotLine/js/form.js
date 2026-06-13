document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.wrapper');
    const loginLink = document.querySelector('.login-link');
    const registerLink = document.querySelector('.register-link');
    const btnLoginPopup = document.querySelector('.btnLogin-popup');
    const btnSignPopup = document.querySelector('.btnSign-popup');
    const btnStartPopup = document.querySelector('.btnStart-popup');
    const iconClose = document.querySelector('.icon-close');
    const alertBox = document.getElementById("custom-alert");

    //Input Selectors
    const loginEmail = document.querySelector('.login input[name="email"]');
    const loginPass = document.querySelector('.login input[name="password"]');
    const regName = document.querySelector('.register input[name="name"]');
    const regEmail = document.querySelector('.register input[name="email"]');
    const regPass = document.querySelector('.register input[name="password"]');
    const regConfirm = document.querySelector('.register input[name="confirm_password"]');

    // ===== VALIDATION FUNCTIONS =====
    function validateUsername(v){
        if(v.length < 3 || v.length > 30) return "Username must be 3–30 characters";
        if(!/^[a-zA-Z0-9_]+$/.test(v)) return "Letters, numbers, _ only";
        return "";
    }

    function validateEmail(v){
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!pattern.test(v)) return "Enter valid email format";
        return "";
    }

    function validatePassword(v){
        if(v.length < 8) return "At least 8 characters";
        return "";
    }

    // ===== UPDATE INPUT BORDER & HINT =====
    function setFieldState(input, message){
        const box = input.closest(".input-box");
        box.classList.remove("input-error","input-valid");
        if(message){
            box.classList.add("input-error");
            input.dataset.hint = message;
        } else {
            box.classList.add("input-valid");
            input.dataset.hint = "";
        }
    }

    // ===== REAL-TIME VALIDATION =====
    regName?.addEventListener("input",()=>{ setFieldState(regName,validateUsername(regName.value.trim())); });
    regEmail?.addEventListener("input",()=>{ setFieldState(regEmail,validateEmail(regEmail.value.trim())); });
    regPass?.addEventListener("input",()=>{ setFieldState(regPass,validatePassword(regPass.value)); });
    regConfirm?.addEventListener("input",()=>{
        if(regConfirm.value !== regPass.value) setFieldState(regConfirm,"Passwords do not match");
        else setFieldState(regConfirm,"");
    });

    // ===== PLACEHOLDER BEHAVIOR =====
    document.querySelectorAll(".input-box input").forEach(input=>{
        const original = input.placeholder || " ";
        input.addEventListener("focus", ()=>{
            input.placeholder = input.dataset.hint || original;
        });
        input.addEventListener("blur", ()=>{
            if(input.value === "") input.placeholder = original;
        });
    });

    //--- POP-UP TOGGLE ---
    function openPopup(isSignup = false) {
        wrapper.classList.add('active-popup');
        wrapper.classList.toggle('active', isSignup);
    }

    function togglePopup(isSignup = false) {
        const isOpen = wrapper.classList.contains('active-popup');
        const isSignupMode = wrapper.classList.contains('active');

        if (isOpen && isSignup === isSignupMode) {
            wrapper.classList.remove('active-popup');
        } else {
            openPopup(isSignup);
        }
    }

    btnLoginPopup?.addEventListener('click', () => togglePopup(false));
    btnSignPopup?.addEventListener('click', () => togglePopup(true));
    btnStartPopup?.addEventListener('click', () => togglePopup(true));

    registerLink?.addEventListener('click', () => wrapper.classList.add('active'));
    loginLink?.addEventListener('click', () => wrapper.classList.remove('active'));
    iconClose?.addEventListener('click', () => wrapper.classList.remove('active-popup'));

    // --- ALERT FUNCTION ---
    function showAlert(message, type = "info") {
        if(!alertBox) return;
        alertBox.textContent = message;
        alertBox.style.color = "#fff";
        
        if (type === "success") alertBox.style.backgroundColor = "#28a745";
        else if (type === "warning") {
            alertBox.style.backgroundColor = "#ffc107";
            alertBox.style.color = "#000";
        } else if (type === "danger") alertBox.style.backgroundColor = "#dc3545";
        
        alertBox.classList.add("show");
        setTimeout(() => alertBox.classList.remove("show"), 2500);
    }

    // --- URL PARAMETER LOGIC ---
    const params = new URLSearchParams(window.location.search);

     // --- LOGIN ---
    if (params.has("error") || params.has("suspended")) {
        wrapper.classList.add('active-popup');
        wrapper.classList.remove('active');

        if (params.get("error") === "email_not_found") {
            showAlert("Email is not registered yet", "warning");
            if(loginEmail) { loginEmail.value = ""; loginEmail.focus(); }
        } 
        else if (params.get("error") === "wrong_password") {
            showAlert("Wrong password", "warning");
            // Fill email back from URL if you updated login.php to send keep_email
            if(params.has("keep_email") && loginEmail) loginEmail.value = params.get("keep_email");
            if(loginPass) { loginPass.value = ""; loginPass.focus(); }
        }
        else if (params.has("suspended")) {
            showAlert("Account suspended", "danger");
        }
    }

// --- REGISTER ALERTS FROM URL PARAM ---
const regStatus = params.get("register");
if (regStatus) {
    wrapper.classList.add("active-popup", "active"); // show popup

    switch(regStatus){
        case "success":
            showAlert("Account created successfully", "success");
            wrapper.classList.remove("active"); // hide login form
            break;
        case "both_taken":
            showAlert("Username and Email are already taken", "warning");
            if(regName) regName.value = "";
            if(regEmail) regEmail.value = "";
            break;
        case "name_taken":
            showAlert("Username is already taken", "warning");
            if(regName) regName.value = "";
            if(params.has("email") && regEmail) regEmail.value = params.get("email");
            regName?.focus();
            break;
        case "email_taken":
            showAlert("Email is already taken", "warning");
            if(regEmail) regEmail.value = "";
            if(params.has("name") && regName) regName.value = params.get("name");
            regEmail?.focus();
            break;
        case "password_mismatch":
            showAlert("Passwords do not match", "warning"); // <-- now works
            if(regConfirm) {
                regConfirm.value = "";
                regConfirm.focus();
            }
            break;
    }

    if(regPass) regPass.value = "";
}

// Add this inside the DOMContentLoaded block in your form.js

if (btnStartPopup) {
    btnStartPopup.addEventListener('click', () => {
        // 1. Show the popup background/wrapper
        wrapper.classList.add('active-popup');
        
        // 2. Remove the 'active' class to ensure it shows the LOGIN form
        // (In your CSS, .wrapper.active usually shows the Register form)
        wrapper.classList.remove('active'); 
        
        // 3. Optional: Clear the password field and focus on email for better UX
        if (loginEmail) {
            loginEmail.focus();
        }
    });
}

    //     // --- CONFIRM PASSWORD CHECK ---
    // const registerForm = document.querySelector('.register form');

    // registerForm?.addEventListener('submit', (e) => {
    //     if (regPass && regConfirm && regPass.value !== regConfirm.value) {
    //         e.preventDefault();
    //         showAlert("Passwords do not match", "warning");
    //         regConfirm.value = "";
    //         regConfirm.focus();
    //     }
    // });

       // ===== FORM SUBMIT VALIDATION =====
    const registerForm = document.querySelector('.register form');
    registerForm?.addEventListener('submit', (e)=>{
        const nameError = validateUsername(regName.value.trim());
        const emailError = validateEmail(regEmail.value.trim());
        const passError = validatePassword(regPass.value);

        setFieldState(regName,nameError);
        setFieldState(regEmail,emailError);
        setFieldState(regPass,passError);

        if(nameError || emailError || passError){
            e.preventDefault();
            showAlert("Please fix highlighted fields","warning");
            return false;
        }

        if(regPass.value !== regConfirm.value){
            e.preventDefault();
            setFieldState(regConfirm,"Passwords do not match");
            showAlert("Passwords do not match","warning");
            regConfirm.value = "";
            regConfirm.focus();
            return false;
        }
    });


    // --- CLEAN URL ---
    if (params.toString()) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
// --- FOCUS-BASED PASSWORD TOGGLE ---
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(input => {
        const parent = input.parentElement;
        const lockIcon = parent.querySelector('.lock-icon');
        const eyeToggle = parent.querySelector('.eye-toggle');
        const eyeIcon = eyeToggle?.querySelector('ion-icon');

        input.addEventListener('focus', () => {
            lockIcon.style.display = 'none';
            eyeToggle.style.display = 'flex';
        });

        input.addEventListener('blur', () => {
            setTimeout(() => {
                if (input.value.length === 0) {
                    lockIcon.style.display = 'flex';
                    eyeToggle.style.display = 'none';
                    input.type = 'password';
                    eyeIcon.setAttribute('name', 'eye');
                }
            }, 150);
        });

        eyeToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.setAttribute('name', 'eye-off');
            } else {
                input.type = 'password';
                eyeIcon.setAttribute('name', 'eye');
            }
            input.focus();
        });
    });

    // Set the initial hints for placeholders
    regName.dataset.hint = "Username must be 3–30 characters, letters, numbers, _ only";
    regEmail.dataset.hint = "Enter a valid email address";
    regPass.dataset.hint = "Must be at least 8 characters";
    regConfirm.dataset.hint = "Must match with password";

    // ===== PLACEHOLDER BEHAVIOR =====
    document.querySelectorAll(".input-box input").forEach(input => {
        input.addEventListener("focus", () => {
            // Always show hint on focus
            input.placeholder = input.dataset.hint || "";
        });

        input.addEventListener("blur", () => {
            // Hide placeholder when leaving the field
            input.placeholder = "";
        });
    });
    
    // --- Add this at the top of your DOMContentLoaded block ---

const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

// Check for the cookie on page load
const rememberedEmail = getCookie('remembered_email');
if (rememberedEmail && loginEmail) {
    loginEmail.value = decodeURIComponent(rememberedEmail);
    
    // Also find the checkbox and check it automatically
    const rememberCheckbox = document.querySelector('input[name="remember_me"]');
    if (rememberCheckbox) rememberCheckbox.checked = true;
}
});



