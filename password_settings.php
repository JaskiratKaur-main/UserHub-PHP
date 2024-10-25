<?php

include "config.php";
session_start();

if(!isset($_SESSION['userId'])){
    header('Location:index.php');
}

//generation of csrf token did not used function because there is only one form
if(!isset($_SESSION['csrf_token']['password_setting_form'])){
    $_SESSION['csrf_token']['password_setting_form'] = bin2hex(random_bytes(32));
}

$curr_pass_err = $new_pass_err = $confirm_pass_err = '';
$flag = 0;
if(isset($_POST['pass_submit']) && $_SERVER["REQUEST_METHOD"] == "POST"){

    //csrf validation
    if(isset($_SESSION['csrf_token']['password_setting_form']) && hash_equals($_SESSION['csrf_token']['password_setting_form'], $_POST['csrf_token'])){


        // server side validations

        if(empty($_POST['inputPasswordCurrent'])){
            $curr_pass_err = "Please enter your current password";
            $flag = 1;
        }else{
            $query = "SELECT hashed_password FROM registration_data_table WHERE user_id = ? ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_SESSION['userId']);
            $stmt->execute();
            // $result = mysqli_query($conn, $query);
            $result = $stmt->get_result();
            if($result->num_rows > 0){
            // if(mysqli_num_rows($result) > 0){
                // $row = mysqli_fetch_assoc($result);
                $row = $result->fetch_assoc();
                $password = $row['hashed_password'];
            }
            //check if password matches
            if(!password_verify($_POST['inputPasswordCurrent'], $password)){
                $curr_pass_err = "Please enter correct password";
                $flag = 1;
            }// else store in db, no need to write else automatically make a var and run query below only once
        }


        if(empty($_POST['inputPasswordNew'])){
            $new_pass_err = "Please enter a new password";
            $flag = 1;
        }else{
            if(strlen($_POST['inputPasswordNew']) < 8){
                $new_pass_err = "Please enter Atleast 8 character password";
                $flag = 1;
            }
            if(!preg_match("#[0-9]+#", $_POST['inputPasswordNew'])){
                $new_pass_err = "Please enter Atleast 1 number"; 
                $flag = 1;
            }
            if(!preg_match("#[A-Z]+#", $_POST['inputPasswordNew'])){
                $new_pass_err = "Please enter Alteast a capital letter";
                $flag = 1;
            }
            if(!preg_match("#[a-z]+#", $_POST['inputPasswordNew'])){
                $new_pass_err = "Please enter Atleast a lowercase letter";
                $flag = 1;
            }
        }


        if(empty($_POST['inputPasswordNew2'])){
            $confirm_pass_err = "Please enter confirm password";
            $flag = 1;
        }else{
            if($_POST['inputPasswordNew2'] != $_POST['inputPasswordNew']){
                $confirm_pass_err = "Password and confirm password do not match";
                $flag = 1;
            }
        }
    }else{
        $error_msg = "Invalid csrf token of password setting form";
    }

}

if(isset($_POST['pass_submit']) && $_SERVER["REQUEST_METHOD"] == "POST"){

    if(isset($_POST['pass_submit'])){

        //csrf validation
        if(isset($_SESSION['csrf_token']['password_setting_form']) && hash_equals($_SESSION['csrf_token']['password_setting_form'], $_POST['csrf_token'])){


            // $currPass = $_POST['inputPasswordCurrent']; //no need to insert
            // $confirmPass = $_POST['inputPasswordNew2']; //no need to insert
            $newPass = password_hash($_POST['inputPasswordNew'], PASSWORD_DEFAULT);

            if($flag == 0){

                $passUpdateQuery = "UPDATE registration_data_table SET hashed_password=?";
                $stmt = $conn->prepare($passUpdateQuery);
                $stmt->bind_param("s", $newPass);
                $passUpdateQueryResult = $stmt->execute();
                // $passUpdateQueryResult = mysqli_query($conn, $passUpdateQuery);
        
                if($passUpdateQueryResult){
                    $success_msg = "Password updated successfully";
                    header('Refresh:3;url="login.php"');
                }else{
                    $error_msg = "Error: " . mysqli_error($conn);
                }
            }
        }else{
            $error_msg = "Invalid csrf token of password setting form";
        }
    }
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Settings</title>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- for header -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <!-- jquery to be loaded before validation js -->
    <script src="password_setting.js"></script>
</head>

<body>
    
    <!-- header -->
    <div class="container-fluid px-0 justify-content-center">
        <nav class="navbar navbar-expand-md bg-body-tertiary">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="uploads/logo-dark.png" class="img-fluid " alt="oriental" width="95"
                        height="35"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="index.php">Home</a>
                        </li>
                        <?php
                        if (isset($_SESSION['userId'])) {
                        ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false">Projects
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown1">
                                    <li><a class="dropdown-item" href="projects.php?type=1">All Projects</a></li>
                                    <li><a class="dropdown-item" href="projects.php?type=2">My Projects</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="projects.php?type=3">Projects Matching My Skills</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if ($_SESSION['user_profile_image'] == 'default_user.jpg') { ?>
                                        <img class="img-profile rounded-circle" src="images/default_user.jpg" height="30" width="30">
                                    <?php } else { ?>
                                        <img class="img-profile rounded-circle" src="uploads/<?php echo htmlspecialchars($_SESSION['user_profile_image']); ?>" height="30" width="30">
                                    <?php } ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown2">
                                    <li><a class="dropdown-item" href="profile.php">Profile Settings</a></li>
                                    <li><a class="dropdown-item" href="password_settings.php">Password Settings</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="delete_account.php">Delete account</a></li>

                                </ul>
                            </li>
                            <!-- logout -->
                            <a href="logout.php" class="nav-link">Logout</a>
                        <?php
                        } else {
                        ?>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="Registration.php">Register</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="login.php">Login</a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>


    <!-- Password -->
    <!-- this container prevents from taking entire width -->
    <div class="container my-5">
        <!-- this ensures horizontally centered -->
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-6">
                <!-- <div class="tab-content">
                <div class="" id="password" role="tabpanel"> -->
                <div class="card" style="border: 2px solid grey;">
                    <div class="card-body px-0 py-4"> <!-- here px-0 is for hr to be full width --> 
                        <h5 class="card-title text-center">Password</h5>
                        <hr>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="passwordUpdate">
                            
                            <!-- csrf --> 
                            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']['password_setting_form']; ?>"> 

                            <div class="form-group px-4 py-2">
                                <label for="inputPasswordCurrent">Current password</label>
                                <input type="password" class="form-control" id="inputPasswordCurrent" name="inputPasswordCurrent">
                                <!-- <small><a href="#">Forgot your password?</a></small> -->
                                <span class="error" style="color: red;"><?php echo htmlspecialchars($curr_pass_err); ?></span>
                            </div>
                            <div class="form-group px-4">
                                <label for="inputPasswordNew">New password</label>
                                <input type="password" class="form-control" id="inputPasswordNew" name="inputPasswordNew">
                                <span class="error" style="color: red;"><?php echo htmlspecialchars($new_pass_err); ?></span>
                            </div>
                            <div class="form-group px-4">
                                <label for="inputPasswordNew2">Confirm password</label>
                                <input type="password" class="form-control" id="inputPasswordNew2" name="inputPasswordNew2">
                                <span class="error" style="color: red;"><?php echo htmlspecialchars($confirm_pass_err); ?></span>
                            </div>
                            <div class="d-flex justify-content-center align-items py-2">
                                <button type="submit" name="pass_submit" class="btn btn-primary">Save changes</button>
                                <!-- display success or error update msgs -->
                                <?php 
                                    if(!empty($success_msg)){
                                        echo "<span style='margin-top:5px;font-weight:bold;color:green;;display:block'>" . htmlspecialchars($success_msg) . "</span>";
                                    }
                                    if(!empty($error_msg)){
                                        echo "<span style='margin-top:5px;font-weight:bold;color:green;;display:block'>" . htmlspecialchars($error_msg) . "</span>";
                                    }
                                ?>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- </div>
                </div> -->
            </div>
        </div>
    </div>
    
    



    <!-- footer -->
    <footer class="bg-dark text-white">
        <div class="container">
            <div class="row pt-4">
                <div class="col-md-4 pb-4">
                    <div class="text-left">
                        <img src="uploads/logo-light.png" alt="Logo" class="img-fluid mb-3">
                    </div>
                    <div class="d-flex justify-content-left">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-4 pb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="#" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="#" class="text-white text-decoration-none">About Us</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Services</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4 pb-4">
                    <h5>Contact Info</h5>
                    <p class="text-white mb-0 pb-1">SCO-63B(1st floor),City Heart, Kharar, Punjab, 140301</p>
                    <p class="text-white mb-0 pb-1">Email: orientaloutsourcing@outlook.com</p>
                    <p class="text-white mb-0 pb-1">Phone: +123456789</p>
                </div>
            </div>
            <hr class="bg-white mt-0 mb-2">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p class="text-white mb-1 copy">&copy; 2024 Orientaloutsourcing. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    

    <style>
        /* Error message style */
        label.error {
            color: red;
        }

        /* Input box border style */
        input.error,
        textarea.error,
        select.error {
            border: 1px solid red;
        }
    </style>

</body>

</html>