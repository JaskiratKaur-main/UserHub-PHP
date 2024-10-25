{/* <script> */}
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