//<script> 
    // code for succews and error msg timeout
        document.addEventListener('DOMContentLoaded', function (){
            var success_element = document.getElementById('success_msg');
            var error_element = document.getElementById('error_msg');

            if(success_element){
                setTimeout(function (){
                    success_element.style.display='none';
                }, 3000);
            }
            if(error_element){
                setTimeout(function (){
                    error_element.style.display='none';
                }, 3000);
            }

        });
    // </script>


    // <script>
        $(document).ready(function (){
            //on clicking form submit btn prevent default submission
            $('#submit').click(function (e){
                e.preventDefault();
                $('#deleteModal').modal('show');
            });
            // //do submission of form on delete modal btn click
            // $('#deleted').click(function (){
            //     $('#deleteForm').submit();
            //     // $('#deleteModal').modal('hide');
            // });
        });
    // </script>