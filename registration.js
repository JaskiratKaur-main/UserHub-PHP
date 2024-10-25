
{/* <script> */}
    $(document).ready(function() {
        $('#registrationForm').validate({
            rules: {
                full_name: {
                    required: true
                },
                // last_name: {
                //     required: true
                // },
                email: {
                    required: true,
                    email: true
                },
                Password: {
                    required: true
                },
                Confirm_password: {
                    required: true,
                    equalTo: '#Password' // Matches the "Password" field
                },
                Checkbox_terms: {
                    required: true
                }
            },
            messages: {
                full_name: "<span class='text-danger'>Please enter your first name</span>",
                // last_name: "<span class='text-danger'>Please enter your last name</span>",
                email: "<span class='text-danger'>Please enter a valid email address</span>",
                Password: {
                    required: "<span class='text-danger'>Please enter a password</span>",
                },
                Confirm_password: {
                    required: "<span class='text-danger'>Please confirm your password</span>",
                    equalTo: "<span class='text-danger'>Passwords do not match</span>",
                },
                Checkbox_terms: "<span class='text-danger'>You must agree to the Terms and Conditions</span>"
            }
        });
    });
{/* </script> */}


{/* <script> */}
document.addEventListener("DOMContentLoaded", function() {
    // to see errors
    errorElement = document.getElementById('error-message');
    if(errorElement){
        errorMessage = errorElement.getAttribute('data-error');
        if(errorMessage){
            console.log(errorMessage);
        }
    }
    if (window.location.href.includes("verify-otp")) {
        const inputs = document.querySelectorAll(".otp-field > input");
        const button = document.querySelector(".btn");

        window.addEventListener("load", () => inputs[0].focus());
        button.setAttribute("disabled", "disabled");

        inputs[0].addEventListener("paste", function(event) {
            event.preventDefault();

            const pastedValue = (event.clipboardData || window.clipboardData).getData(
                "text"
            );
            const otpLength = inputs.length;

            for (let i = 0; i < otpLength; i++) {
                if (i < pastedValue.length) {
                    inputs[i].value = pastedValue[i];
                    inputs[i].removeAttribute("disabled");
                    inputs[i].focus();
                } else {
                    inputs[i].value = ""; // Clear any remaining inputs if the pasted values are less than input fields 
                    inputs[i].focus();
                }
            }
        });

        inputs.forEach((input, index1) => {
            input.addEventListener("keyup", (e) => {
                const currentInput = input;
                const nextInput = input.nextElementSibling;
                const prevInput = input.previousElementSibling;

                if (currentInput.value.length > 1) {
                    currentInput.value = "";
                    return;
                }

                if (
                    nextInput &&
                    nextInput.hasAttribute("disabled") &&
                    currentInput.value !== ""
                ) {
                    nextInput.removeAttribute("disabled");
                    nextInput.focus();
                }

                if (e.key === "Backspace") {
                    inputs.forEach((input, index2) => {
                        if (index1 <= index2 && prevInput) {
                            input.setAttribute("disabled", true);
                            input.value = "";
                            prevInput.focus();
                        }
                    });
                }

                button.classList.remove("active");
                button.setAttribute("disabled", "disabled");

                const inputsNo = inputs.length;
                if (!inputs[inputsNo - 1].disabled && inputs[inputsNo - 1].value !== "") {
                    button.classList.add("active");
                    button.removeAttribute("disabled");

                    return;
                }
            });
        });
    }
    else{
        console.log("Verify otp parameter not found in url");
    }
});
{/* </script> */}
