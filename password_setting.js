// <!-- To display error or success message if profile information is updated in the database -->
    // <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the message elements
            var errorElement = document.getElementById('update-message-error');
            var successElement = document.getElementById('update-message-success');

            // Check if the error message element exists
            if (errorElement) {
                // Hide the error message after 3 seconds
                setTimeout(function() {
                    errorElement.style.display = 'none';
                }, 3000); // 3000 milliseconds = 3 seconds
            }

            // Check if the success message element exists
            if (successElement) {
                // Hide the success message after 3 seconds
                setTimeout(function() {
                    successElement.style.display = 'none';
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        });
    // </script>



    // <script>
        //client side validations
        $(document).ready(function (){
            
            $('#passwordUpdate').validate({
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                },
                rules: {
                    inputPasswordCurrent : {
                        required: true,
                    },
                    inputPasswordNew : {
                        required: true,
                    },
                    inputPasswordNew2 : {
                        required: true,
                        equalTo: '#inputPasswordNew',
                    },
                },
                messages: {
                    inputPasswordCurrent : {
                        required: "Please enter the current password",
                    },
                    inputPasswordNew : {
                        required: "Please enter your new password",
                    },
                    inputPasswordNew2 : {
                        required: "Please enter confirm password",
                        equalTo: "Passwords do not match",
                    },
                },

            });

            $('input').on('focusout keyup', function () {
                $(this).valid(); // Trigger validation for the input on focusout
            });
        });

    {/* </script> */}