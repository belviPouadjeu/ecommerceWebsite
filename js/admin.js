// admin.js

// Wait for the DOM content to be fully loaded
document.addEventListener('DOMContentLoaded', () => {

    /* ============================  Admin and Register js starts ============================ */

    let passwordInput = document.getElementById('password');
    let progress = document.querySelector('.progress');
    let inputs = document.querySelectorAll('input');

    // Get the close-eye and open-eye icons
    let closeEyeIcon = document.getElementById('close-eye');
    let openEyeIcon = document.getElementById('open-eye');
    
    // Add click event listener to the close-eye icon
    closeEyeIcon.addEventListener('click', function() {
        // Change the input type to 'text' to show the password
        passwordInput.type = 'text';
        
        // Hide the close-eye icon
        closeEyeIcon.style.display = 'none';
        
        // Show the open-eye icon
        openEyeIcon.style.display = 'inline-block';
    });
    
    // Add click event listener to the open-eye icon
    openEyeIcon.addEventListener('click', function() {
        // Change the input type back to 'password' to hide the password
        passwordInput.type = 'password';
        
        // Hide the open-eye icon
        openEyeIcon.style.display = 'none';
        
        // Show the close-eye icon
        closeEyeIcon.style.display = 'inline-block';
    });

    passwordInput.addEventListener('focus', () => {
        progress.classList.remove('hidden');
    });

    passwordInput.addEventListener('input', checkPasswordStrength);

    function checkPasswordStrength() {
        let password = passwordInput.value;
        let progressBar = document.getElementById('password-strength-bar');
        
        // Define criteria weights
        let criteriaWeights = {
            length: 2,
            specialChars: 5,
            upperLower: 20,
            numbers: 20
        };
    
        // Initialize total criteria count and fulfilled criteria count
        let totalCriteria = Object.keys(criteriaWeights).length;
        let fulfilledCriteria = 0;
    
        // Check for length
        if (password.length >= 8) {
            fulfilledCriteria++;
        }
    
        // Check for special characters
        let specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;
        if (specialChars.test(password)) {
            fulfilledCriteria++;
        }
    
        // Check for uppercase and lowercase letters
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
            fulfilledCriteria++;
        }
    
        // Check for numbers
        if (/\d/.test(password)) {
            fulfilledCriteria++;
        }
    
        // Calculate strength percentage based on fulfilled criteria
        let strengthPercentage = (fulfilledCriteria / totalCriteria) * 100;
    
        // Set progress bar width and color based on strength percentage
        progressBar.style.width = strengthPercentage + '%';
        if (strengthPercentage >= 100) {
            progressBar.classList.remove('bg-danger', 'bg-warning');
            progressBar.classList.add('bg-success');
            
        } else if (strengthPercentage >= 60) {
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.remove('bg-warning', 'bg-success');
            progressBar.classList.add('bg-danger');
        }
    }
    
    // Give boder color to all the inputs fields when they are focus
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            // Set the border color when the input is focused
            input.style.borderColor = 'blue'; // You can change 'blue' to any color you prefer
        });

        // Reset the border color when the input loses focus
        input.addEventListener('blur', () => {
            input.style.borderColor = ''; // Reset to default or remove inline border color
        });
    });

    /* ============================  Admin and Register js ends ============================ */

    /* ============================  Header js starts ============================ */
    const userBtn = document.querySelector('#user-btn');
    const profile = document.querySelector('.profile');
    const navbar = document.querySelector('.navbar');
    const menuBtn = document.querySelector('#menu-btn');

    menuBtn.onclick = () => {
        navbar.classList.toggle('active');
        profile.classList.remove('active');
        toggleScroll();
    };

    userBtn.onclick = () => {
        profile.classList.toggle('active');
        navbar.classList.remove('active');
        // toggleScroll();
    };

    // Function to toggle scroll
    /*function toggleScroll() {
        document.body.classList.toggle('no-scroll');
    }*/


    // Close navbar when a navigation link is clicked
    const navLinks = document.querySelectorAll('.navbar a');
    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            navbar.classList.remove('active');
            // toggleScroll();
        });
    });

    // Close navbar when scrolling
    window.onscroll = () => {
        profile.classList.remove('active');
        navbar.classList.remove('active');
        // toggleScroll()
    }
    /* ============================  Header js ends ============================ */
});

