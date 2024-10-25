<?php

// Including configuration file
include "config.php";
session_start();


function generateCSRFtoken($formName){
    if(empty($_SESSION['csrf_token'][$formName])){
        $_SESSION['csrf_token'][$formName] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'][$formName];
}

function validateCSRFtoken($formName, $token){
    if(isset($_SESSION['csrf_token'][$formName]) && hash_equals($_SESSION['csrf_token'][$formName], $token)){
        return true;
    }
    return false;
}

// Initializing error and success messages
$error_message = "";
$success_message = "";

// Handling login form submission
if (isset($_POST['login'])) {
    //validate csrf
    if(isset($_POST['csrf_token']) && validateCSRFtoken('login_form', $_POST['csrf_token'])){
    
        if (empty($_POST['emailLogin'])) {
            $error_message = "Email is required";
        } else if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $_POST['emailLogin'])) {
            // Check if email address syntax is valid
            $error_message = "You Entered An Invalid Email Format";
        } else if (empty($_POST['pwdLogin'])) {
            $error_message = "Password is required";
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Check if email and password are provided
                if (!empty($_POST['emailLogin']) && !empty($_POST['pwdLogin'])) {
                    $email = $conn->real_escape_string($_POST['emailLogin']);
                    $password = $conn->real_escape_string($_POST['pwdLogin']);

                    // Retrieve user information based on the provided email
                    // $result = $conn->query("SELECT * FROM registration_data_table WHERE your_email = '$email'");
                    $stmt = $conn->prepare("SELECT * FROM registration_data_table WHERE your_email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Check if the user with the provided email exists and their email is verified
                    if ($result->num_rows > 0) 
                    {
                        $user = $result->fetch_assoc();
                        if($user['is_active'] == 0){
                            header('Refresh:3;url="Registration.php"');
                        }else{
                            if (password_verify($password, $user['hashed_password'])) 
                            {
                                // Password is correct, user is authenticated
                                $success_message = "Login successful!";
                                $_SESSION['userId']= $user['user_id'];


                                // Update user profile image to default_user.jpg 
                                if($user['user_profile_image'] == 'default_user.jpg'){
                                    $_SESSION['user_profile_image'] = 'default_user.jpg';
                                }else{
                                    $_SESSION['user_profile_image'] = $user['user_profile_image'];
                                }

                                // $updateResult = $conn->query("UPDATE registration_data_table SET user_profile_image = '{$_SESSION['user_profile_image']}' WHERE user_id = {$user['user_id']}");
                                $stmt1 = $conn->prepare("UPDATE registration_data_table SET user_profile_image = ? WHERE user_id = ?");
                                $stmt1->bind_param("si", $_SESSION['user_profile_image'], $user['user_id']);
                                $updateResult = $stmt1->execute();
                                if ($updateResult) 
                                {
                                    // $_SESSION['user_profile_image'] = 'default_user.jpg';
                                }
                                header("Refresh:2; url=index.php"); //to update checkboxes as they are not updated when the form subits so another refresh is required for updating them
                            } 
                            else 
                            {
                                // Incorrect password
                                $error_message = "Incorrect password. Please try again.";
                            }
                        }
                    } 
                    else 
                    {
                        // User with provided email doesn't exist or email is not verified
                        $error_message = "Email not registered or not verified.";
                    }
                } 
                else 
                {
                    // Email or password is empty
                    $error_message = "Please provide both email and password.";
                }
            }
        }
    }
    else{
        $error_message = "Invalid login form csrf token";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login.css">
    <!-- for header -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Include jQuery Validation Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <script src="login.js"></script>
    <title>Login</title>
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

    <!-- Wrapper for the entire login page -->
    <div class="wrapper">
        <!-- Left section with information and registration link -->
        <div class="form-left">
            <h2 class="text-uppercase">Information</h2>
            <p>
                <!-- Some information text -->
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Et molestie ac feugiat sed. Diam volutpat commodo.
            </p>
            
            <div class="form-field">
                <?php 
                    if(isset($_POST['register_redirect'])){
                        if(isset($_POST['csrf_token']) && validateCSRFtoken('register_redirect_form', $_POST['csrf_token'])){
                            header('url="Registration.php"');
                        }else{
                            $error_message = "Invalid register redirect form csrf token";
                        }
                    }
                ?>
                <!-- Redirect to registration -->
                <form action="Registration.php" method="post">
                    <!-- csrf --> 
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('register_redirect_form')); ?>">
                    <input type="submit" name="register_redirect" class="account" value="Not Have an Account?">
                </form>    
            </div>
        </div>
        <!-- Right section with login form -->
        <form class="form-right" id="loginForm" method="post">
            <!-- csrf --> 
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('login_form')); ?>">
            <h2 class="text-uppercase">Login form</h2>
            <div class="mb-3">
                <label>Your Email</label>
                <input type="email" class="input-field" name="emailLogin" id="emailId">
            </div>
            <div class="row">
                <div class="mb-3">
                    <label>Password</label>
                    <!-- Password input field with pattern validation -->
                    <input type="password" name="pwdLogin" id="passwordId" class="input-field" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*]).{6,}$">
                </div>
            </div>
            <div class="mb-3">
                <label class="option">I agree to the <a href="#">Terms and Conditions</a>
                    <!-- Checkbox for terms and conditions -->
                    <input type="checkbox" id="checkboxId" name="checkboxLogin">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="form-field">
                <!-- Submit button for login -->
                <input type="submit" value="Login" class="register" name="login">
            </div>
            <div class="form-field">
                <!-- Display success message -->
                <p class='text-success text-center fw-bold' id="successMessage"><?php echo htmlspecialchars($success_message); ?></p>
            </div>
            <div class="form-field">
                <!-- Display error message -->
                <p class='text-danger fw-bold' id="errorMessage"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        </form>
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



</body>

</html>
