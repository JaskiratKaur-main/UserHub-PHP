{/* <script> */}
        $(document).ready(function() {
            // Validation rules for the login form
            $('#loginForm').validate({
                rules: {
                    emailLogin: {
                        required: true,
                        email: true
                    },
                    pwdLogin: {
                        required: true,
                    },
                    checkboxLogin: {
                        required: true
                    }
                },
                messages: {
                    emailLogin: "<span class='text-danger'>Please enter a valid email address</span>",
                    pwdLogin: {
                        required: "<span class='text-danger'>Please enter a password</span>",
                    },
                    checkboxLogin: "<span class='text-danger'>You must agree to the Terms and Conditions</span>"
                }
            });
        });
    {/* </script> */}

    // <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's an error message or success message
        // Display the error message or success message
        var errorElement = document.getElementById('errorMessage');
        var successElement = document.getElementById('successMessage');
        if(errorElement !== "" || successElement !== ""){
            errorElement.style.display = 'block';
            successElement.style.display = 'block';
            // Set timeout to hide the error message or success message after 3 seconds
            setTimeout(function() {
                errorElement.style.display = 'none';
                successElement.style.display = 'none';
            }, 2000); // 2000 milliseconds = 2 seconds
        }
    });
        
    // </script>