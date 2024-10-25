// <!-- script for timeout of success/error msgs --> 
//     <script>
            document.addEventListener('DOMContentLoaded', function(){
                var successElement = document.getElementById('successElement');
                var errorElement = document.getElementById('errorElement');

                if(successElement){
                    setTimeout(function(){
                        successElement.style.display = 'none';
                    }, 3000);
                }

                if(errorElement){
                    setTimeout(function(){
                        errorElement.style.display = 'none';
                    }, 3000);
                }
            });
        {/* </script> */}
    // <script>
        $(document).ready(function() {
            // Event delegation to handle click events for edit and delete buttons
            //event delegation = This way, any new .editBtn element added dynamically will automatically have the event listener without needing a reload or manual rebind.
            //instead of direct bind like $(.editbtn).on('click').....
            $('#dataTable').on('click', '.editBtn', function() {
                var projectId = $(this).data('project-id');
                editProject(projectId);
            });

            $('#dataTable').on('click', '.deleteBtn', function() {
                var projectId = $(this).data('project-id');
                deleteProject(projectId);
            });

            // Function to handle editing project
            function editProject(projectId) { 
                var newUrl = "add_edit_projects.php";
                //encodeURIComponent(projectId) = this the encoded name which encodes special characters 
                newUrl += "?id=" + encodeURIComponent(projectId); // concatenate or Append project parameter if it exists
                window.location.href = newUrl;
            }

            // Function to handle delete project
            function deleteProject(projectId) {
                // Show the modal
                $('#deleteModal').modal('show');

                // Update data attribute of delete button with project ID
                $('#deleteButton').data('project-id', projectId);

                // Handle click event of delete button inside modal
                $('#deleteButton').off('click').on('click', function() {
                    event.preventDefault(); // Prevent default form submission behavior

                    //firstly redirect to projects page with id of the project to be deleted then get id and run delete query as above
                    var newUrl = "projects.php";
                    //window.location.search = returns everything after ? in url
                    var urlParams = new URLSearchParams(window.location.search);
                    // Check if there are any existing parameters in the URL
                    if (urlParams.has("id")) {
                        urlParams.set("id", encodeURIComponent(projectId)); // Update existing id parameter
                    } else {
                        urlParams.append("id", encodeURIComponent(projectId)); // Append id parameter
                    }


                    newUrl += "?" + urlParams.toString(); // Convert URLSearchParams to string and append to newUrl

                    // Update URL without reloading the page
                    window.location.href = newUrl;
                });
            }
        });
    {/* </script> */}

    // <!-- this JS is for searching and to send the typed input in the url-->
    // <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchForm').addEventListener('submit', function(event) {
                var searchValue = document.getElementById('search').value;
                // var typeValue = "<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>";
                var typeValue = searchForm.getAttribute('data-type');
                //get csrf token of search form
                var csrf_token = document.getElementById('search_csrf_token').value;
                // Add the search query parameter and type parameter to the URL
                window.location.href = window.location.pathname + '?type=' + typeValue + '&search=' + encodeURIComponent(searchValue) + '&search_csrf_token=' + encodeURIComponent(csrf_token);
                event.preventDefault(); // Prevent default form submission on reload to preserve search
            });
        });
    {/* </script> */}

    // <!-- this JS is for filtering and to send the filtered input in the url-->
    // <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterForm').addEventListener('submit', function(event) {
                //this line will rather not work bcoz the file is js to use this write it in form in data-* attribute see in filter form in php file
                // var typeValue = "<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>";
                var typeValue = filterForm.getAttribute('data-type');
                // Construct the URL with type parameter
                var url = window.location.pathname + '?type=' + typeValue + '&filter=on';

                //get csrf token of filter form
                // var csrf_token = document.querySelector('#filter_csrf_token').value;
                // Get all input and select elements
                var inputs = document.querySelectorAll('#filterForm input, #filterForm select');

                // Iterate through each input/select
                inputs.forEach(function(input) {
                    var value;
                    // For select elements, get the selected option(s) value(s)
                    if (input.tagName.toLowerCase() === 'select') {
                        var selectedOptions = Array.from(input.selectedOptions).map(option => option.value); //map returns array of value of option elements inside the select it is an arrow function iterating over selected options
                        value = selectedOptions.join(',');
                    } else {
                        value = input.value.trim();
                    }
                    // If the value is not empty, append it to the URL
                    if (value !== '') {
                        url += '&' + input.id + '=' + encodeURIComponent(value);
                    }
                });

                // Append the CSRF token to the URL
                // url += '&filter_csrf_token=' + encodeURIComponent(csrf_token);

                // Redirect to the constructed URL
                window.location.href = url;

                // Prevent default form submission on reload to preserve 
                event.preventDefault();
            });
        });
    {/* </script> */}
