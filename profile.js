// <!-- To display error or success message if profile information is updated in the database -->
{/* <script> */}
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

        document.getElementById('uploadbtn').addEventListener('click', function(event){
            event.preventDefault(); // Prevent any default action
            document.getElementById('upload').click();
        });
        //image preview
        document.getElementById('upload').addEventListener('change', displayImage);
        //for image preview
        function displayImage(event) {
            var selectedFile = event.target.files[0];
            var imageTag = document.getElementById("selectedImage");
            imageTag.src = URL.createObjectURL(selectedFile);
        }
    });
// </script>



{/* <script> */}


        $(document).ready(function() {

            $('#upload').on('change', function() {
                // Clear any previous error message
                // $(this).next('.error').text('');
                $('#profileImageErr').text('');

                var file = this.files[0];
                if (file) {
                    // Check file size (example: max 2MB)
                    var maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        // $(this).next('.error').text('File size must be less than 2MB');
                        $('#profileImageErr').text('File size must be less than 2MB');
                    }

                    // Check file type (example: only allow jpeg, jpg, png)
                    var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if ($.inArray(file.type, allowedTypes) === -1) {
                        // $(this).next('.error').text('Only jpeg, jpg and png files are allowed');
                        $('#profileImageErr').text('Only jpeg, jpg and png files are allowed');
                    }
                } else {
                    // $(this).next('.error').text('Please select an image');
                    $('#profileImageErr').text('Please select an image');
                }
            });

            // On form submission, ensure the hidden file input is validated
            $('#profileForm').submit(function(e) {
                var fileSelected = $('#upload')[0].files.length > 0; // Check if a file is selected
                if (!fileSelected) {
                    $('#profileImageErr').text('Please choose an image'); // Show error
                    e.preventDefault(); // Prevent form submission
                }
            });

            $('#profileForm').validate({
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "user_gender") {
                        error.insertAfter("#genderError");
                    } else if (element.attr("name").startsWith("user_skills")) {
                        error.insertAfter("#skillsError");
                    }else if (element.attr("name") == "image") {
                        error.appendTo("#profileImageErr"); // Place error next to image error span
                    } else {
                        error.insertAfter(element);
                    }
                },
                rules: {
                    //this is not being applied 
                    // image: {
                    //     required: true,
                    //     imageType: true,
                    //     imageSize: true,
                    // },
                    user_bio: {
                        required: true,
                    },
                    first_name: {
                        required: true,
                        letterswithspaces: true
                    },
                    last_name: {
                        required: true,
                        letterswithspaces: true
                    },
                    mobile_no: {
                        required: true,
                        minlength: 10,
                        digits: true
                    },
                    date_of_birth: {
                        required: true,
                        dateISO: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    country: {
                        required: true,
                        letterswithspaces: true
                    },
                    state: {
                        required: true,
                        letterswithspaces: true
                    },
                    postcode: {
                        required: true,
                        digits: true
                    },
                    address: {
                        required: true
                    },

                    user_gender: {
                        required: true
                    }
                },
                messages: {
                    // image: {
                    //     required: "Please choose an jdnckdsnk",
                    //     imageType: 'Only jpeg, jpg and png files are allowed',
                    //     imageSize: 'File size must be less than 2MB',
                    // },
                    user_bio: {
                        required: "Please enter your bio",
                    },
                    first_name: {
                        required: "Please enter your first name",
                        letterswithspaces: "Please enter only letters"
                    },
                    last_name: {
                        required: "Please enter your last name",
                        letterswithspaces: "Please enter only letters"
                    },
                    mobile_no: {
                        required: "Please enter your phone number",
                        minlength: "Phone number must be at least 10 digits long",
                        digits: "Please enter only digits"
                    },
                    date_of_birth: {
                        required: "Please enter your date of birth",
                        dateISO: "Please enter a valid date in the format YYYY-MM-DD"
                    },
                    email: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address"
                    },
                    country: {
                        required: "Please enter your country"
                    },
                    state: {
                        required: "Please enter your state/region"
                    },
                    postcode: {
                        required: "Please enter your postcode",
                        digits: "Please enter only digits"
                    },
                    address: {
                        required: "Please enter your address"
                    },
                    user_gender: {
                        required: "Please select your gender"
                    }
                }
            });

            function checkSkills() {
                var checked = $("input[name='user_skills[]']:checked").length > 0;
                if (!checked) {
                    $('#skillsError').text('Please select at least one skill');
                } else {
                    $('#skillsError').text(''); // Clear error message if at least one skill is selected
                }
            }

            // Check skills when the form is submitted
            $('#profileForm').submit(function(e) {
                checkSkills(); // Check skills before form submission
                var checked = $("input[name='user_skills[]']:checked").length > 0;
                if (!checked) {
                    e.preventDefault(); // Prevent form submission if no skill is selected
                }
            });

            // Update skills check when checkboxes are changed
            $("input[name='user_skills[]']").change(function() {
                checkSkills(); // Check skills whenever a checkbox is changed
            });




            // Custom validation method for text format with spaces
            jQuery.validator.addMethod("letterswithspaces", function(value, element) {
                return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
            }, "Please enter only letters");

            //Image client side validation
            // Custom validation method for checking file type
            // $.validator.addMethod("imageType", function(value, element) {
            //     if (element.files && element.files[0]) {
            //         var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            //         var fileType = element.files[0].type;
            //         return $.inArray(fileType, allowedTypes) !== -1; // false
            //     }
            //     return true; // Allow empty file field
            // }, "Only jpeg, jpg and png files are allowed"); //runs when false

            // // Custom validation method for checking file size
            // $.validator.addMethod("imageSize", function(value, element) {
            //     if (element.files && element.files[0]) {
            //         var maxSize = 2 * 1024 * 1024; // 2MB
            //         return element.files[0].size <= maxSize;
            //     }
            //     return true; // Allow empty file field
            // }, "File size must be less than 2MB");

            
        });
    {/* </script> */}