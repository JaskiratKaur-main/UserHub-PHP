<?php
ob_start(); // Start output buffering to allow any output before header redirect like in line 163 and 297
require_once("config.php"); // Load configuration settings
require_once("vendor/autoload.php"); // Load Composer autoload for libraries
session_start();

// Use namespaces for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// for more than one forms in a page use functions and add form name with csrf token session name
function generateCSRFtoken($formName){
    if(!isset($_SESSION['csrf_token'][$formName])){
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

?>
<!-- Start of HTML document -->
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Character encoding and viewport settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Document title -->
    <title>Registration Page</title>

    <!-- External CSS and JS libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <!-- Custom stylesheets -->
    <link rel="stylesheet" href="css/Registration.css">
    <link rel="stylesheet" href="css/otp.css">

    <!-- for header -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Add jQuery validation rules -->
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="registration.js"></script>

</head>
<!-- Body section for page content -->

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
                            <a class="nav-link" href="logout.php">Logout</a>
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

    <!-- Main wrapper for the page content -->
    <div class="wrapper">
        <?php
        if (!isset($_GET['verify-otp'])) {
            // otp verification form
        ?>
            <div class="form-left">
                <h2 class="text-uppercase">Information</h2>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </p>

                <div class="form-field">
                    <?php 
                        if(isset($_POST['login_redirect'])){
                            if(isset($_POST['csrf_token']) && validateCSRFtoken('login_redirect_form', $_POST['csrf_token'])){
                                header('url="login.php"');
                            }
                            else{
                                $error_message = "Invalid login redirect form csrf token";
                            }
                        }
                    ?>
                    <!-- Redirect to login page -->
                    <!-- each and every form should contain csrf token -->  
                    <form action="login.php" method="post">
                        <!-- csrf --> 
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('login_redirect_form'));  ?>">
                        <input type="submit" name="login_redirect" class="account" value="Have an Account?">
                    </form>
                </div>
            </div>
        <?php
        }
        $error_message = ""; // Initialize error message variable
        $success_message = ""; // Initialize success message variable
        $otp_msg = "";
        $is_delete = 0;

        // Check if the form is submitted
        if (isset($_POST['register'])) {
            //validate csrf token of registration form on register btn submit
            if(isset($_POST['csrf_token']) && validateCSRFtoken('registration_form', $_POST['csrf_token']))
            {
                // Validate form fields
                if (empty($_POST['full_name'])) {
                    $error_message = "First Name is required";
                // } else if (empty($_POST['last_name'])) {
                //     $error_message = "Last Name is required";
                } else if (empty($_POST['email'])) {
                    $error_message = "Email is required";
                } else if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $_POST['email'])) {
                    // check if e-mail address syntax is valid
                    $error_message = "You Entered An Invalid Email Format";
                } else if (empty($_POST['Password'])) {
                    $error_message = "Password is required";
                } else if (empty($_POST['Confirm_password'])) {
                    $error_message = "Confirm password is required";
                } else if ($_POST['Password'] != $_POST['Confirm_password']) {
                    $error_message = "Password and Confirm password do not match.";
                } else if (strlen($_POST["Password"]) <= '8') {
                    $error_message = "Your Password Must Contain At Least 8 Characters!";
                } else if (!preg_match("#[0-9]+#", $_POST['Password'])) {
                    $error_message = "Your Password Must Contain At Least 1 Number!";
                } else if (!preg_match("#[A-Z]+#", $_POST['Password'])) {
                    $error_message = "Your Password Must Contain At Least 1 Capital Letter!";
                } else if (!preg_match("#[a-z]+#", $_POST['Password'])) {
                    $error_message = "Your Password Must Contain At Least 1 Lowercase Letter!";
                } else {
                    // Query the database to check if email is already registered
                    $stmt = $conn->prepare("SELECT * FROM registration_data_table WHERE your_email = ? ");
                    $stmt->bind_param('s', $_POST['email']); // no need to use htmlspecialchars or real_escape_string because prepare already does it for you so it is safe just for outputting safely use these
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Check if the query was successful and if there are any results
                    if ($result && $result->num_rows > 0) {
                        // Fetch the first row from the result set
                        $userdata = $result->fetch_assoc();
                        
                        // Check if the `is_active` field is 1
                        if ($userdata['is_active'] == 1) {
                            $error_message = "Email already registered. Please log in or enter a different email address.";
                        } else {
                            // This is an example output; replace it with your own logic
                            $otp = rand(111111, 999999);
                            $hash_password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
                            $curr_date_time = date("Y-m-d H:i:s");
                            $profile_image = 'default_user.jpg';



                            // Example update query based on email_address
                            // `last_name` = '" . $conn->real_escape_string($_POST['last_name']) . "',
                            // $qry = "UPDATE `registration_data_table` 
                            // SET 
                            //     `full_name` = '" . $conn->real_escape_string($_POST['full_name']) . "',
                            //     `is_delete` = '$is_delete',
                            //     `hashed_password` = '" . $conn->real_escape_string($hash_password) . "',
                            //     `email_verification_code` = '" . $conn->real_escape_string($otp) . "',
                            //     `created_at` = '" . $conn->real_escape_string($curr_date_time) . "',
                            //     `modified_on` = '" . $conn->real_escape_string($curr_date_time) . "'
                            //     WHERE `your_email` = '" . $conn->real_escape_string($_POST['email']) . "'";

                            $stmt1 = $conn->prepare("UPDATE `registration_data_table` SET `full_name` = ?, `is_delete` = ?, `user_profile_image` = ?, `hashed_password` = ?,  `email_verification_code` = ?, `created_at` = ?, `modified_on` = ? WHERE `your_email` = ?");
                            $stmt1->bind_param('sissssss', $_POST['full_name'], $is_delete, $profile_image, $hash_password, $otp, $curr_date_time, $curr_date_time, $_POST['email']);
                            $result1 = $stmt1->execute();
                            //not using result because it is to be used with select 
                            // $result1 = $stmt1->get_result();
                            if ($result1) {
                                // record inserted
                                //$success_message = "Successfully registered";
                                $user_id = $userdata['user_id'];
                                // record inserted

                                // $success_message = "Successfully registered";
                                //Create an instance; passing `true` enables exceptions
                                $mail = new PHPMailer();

                                try {
                                    //Server settings
                                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                                    $mail->isSMTP();                                            //Send using SMTP
                                    // $mail->Host       = 'em2.pwh-r1.com';                     //Set the SMTP server to send through
                                    $mail->Host = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                                    $mail->Username   = 'jaskiratkaur4417@gmail.com';                     //SMTP username
                                    $mail->Password   = 'mfsd nekm alun twtz';                               //SMTP password
                                    $mail->SMTPSecure = 'SSL';            //Enable implicit TLS encryption
                                    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                                    //Recipients
                                    $mail->setFrom('jaskiratkaur4417@gmail.com');
                                    $mail->addAddress($_POST['email']);     //Add a recipient
                                    //Content
                                    $mail->isHTML(true);                                  //Set email format to HTML
                                    $mail->Subject = 'Upadted Email Verification';
                                    $mail->Body    = '<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
                                    <div style="margin:50px auto;width:70%;padding:20px 0">
                                    <div style="border-bottom:1px solid #eee">
                                        <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">OOCPL</a>
                                    </div>
                                    <p style="font-size:1.1em">Hi ' . $_POST["full_name"] . ',</p>
                                    <p>Thank you for choosing Your Brand. Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes</p>
                                    <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">' . $otp . '</h2>
                                    <p style="font-size:0.9em;">Regards,<br />OOCPL</p>
                                    <hr style="border:none;border-top:1px solid #eee" />
                                    <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
                                        <p>OOCPL</p>
                                        <p>1600 Amphitheatre Parkway</p>
                                        <p>California</p>
                                    </div>
                                    </div>
                                </div>';
                                    if (!$mail->send()) {
                                        echo 'Mailer Error: ' . $mail->ErrorInfo;
                                    }else{
                                        $mail->send();
                                    }

                                    // $_SESSION['verify_user_id'] = $user_id;
                                    // $success_message = "We have sent an OTP on your email address for verification, Please enter em";
                                    header("Location: Registration.php?verify-otp=" . urlencode($user_id));

                                    //echo 'Message has been sent';
                                } catch (Exception $e) {
                                    echo "hii1";
                                    // echo "<div id='error-message' data-error=' " . htmlspecialchars($e->getMessage()) . " '></div>";
                                    // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                    $error_message = "System error, Please contact Administrator.". $e->getMessage();
                                }
                            }
                            else {
                                echo "hii2";
                                // error
                                // echo "<div id='error-message' data-error=' " . htmlspecialchars($conn->error) . " '></div>";
                                $error_message = "System error, Please contact Administrator.". $conn->error;
                            }
                        }
                    }
                    // $updateResult = $conn->query("select * from user_info where email_address = '" . $conn->real_escape_string($_POST['email']) . "' and is_active=0;");
                    // $result = $conn->query($updateResult);
                    // else if($result){
                    // }

                    else {
                        $otp = rand(111111, 999999);
                        $hash_password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
                        $curr_date_time = date("Y-m-d H:i:s");
                        $profile_image = 'default_user.jpg';
            
                        // `last_name`,
                        // '" . $conn->real_escape_string($_POST['last_name']) . "',
                        // $qry = "INSERT INTO `registration_data_table`(`full_name`, `your_email`, `hashed_password`, `email_verification_code`, `is_email_verified`, `is_active`, `created_at`, `modified_on`, `user_profile_image`, `user_bio`, `user_gender`, `mobile_no`, `date_of_birth`, `country`, `state`, `postcode`, `address`, 'is_delete') VALUES
                        //  ('" . $conn->real_escape_string($_POST['full_name']) . "',  '" . $conn->real_escape_string($_POST['email']) . "', '" . $conn->real_escape_string($hash_password) . "','" . $conn->real_escape_string($otp) . "',0,0,'" . $curr_date_time . "' , '" . $curr_date_time . "' , '" . $profile_image . "' , '', '', '', NULL, '', '', '', '', '". $is_delete . "')";
                        $is_email_verified = $is_active = 0;
                        $user_bio = $user_gender = $mobile_no = $country = $state = $postcode = $address = '';
                        $date_of_birth = NULL;
                        $stmt2 = $conn->prepare("INSERT INTO `registration_data_table` (`full_name`, `your_email`, `hashed_password`, `email_verification_code`, `is_email_verified`, `is_active`, `created_at`, `modified_on`, `user_profile_image`, `user_bio`, `user_gender`, `mobile_no`, `date_of_birth`, `country`, `state`, `postcode`, `address`, `is_delete`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt2->bind_param("ssssissssssssssssi", $_POST['full_name'], $_POST['email'], $hash_password, $otp, $is_email_verified, $is_active, $curr_date_time, $curr_date_time, $profile_image, $user_bio, $user_gender, $mobile_no, $date_of_birth, $country, $state, $postcode, $address, $is_delete);
                        $result2 = $stmt2->execute();
                        // $result2 = $stmt2->get_result();
                        if ($result2) {
                            // record inserted
                            //$success_message = "Successfully registered";
                            // Get the last inserted user's ID
                            $user_id = $conn->insert_id;
                            // record inserted

                            // $success_message = "Successfully registered";
                            //Create an instance; passing `true` enables exceptions
                            $mail = new PHPMailer();

                            try {
                                //Server settings
                                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                                $mail->isSMTP();                                            //Send using SMTP
                                // $mail->Host       = 'em2.pwh-r1.com';                     //Set the SMTP server to send through
                                $mail->Host = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                                $mail->Username   = 'jaskiratkaur4417@gmail.com';                                //SMTP username
                                $mail->Password   = 'mfsd nekm alun twtz';                               //SMTP password
                                $mail->SMTPSecure = 'SSL';            //Enable implicit TLS encryption
                                $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                                //Recipients
                                $mail->setFrom('jaskiratkaur4417@gmail.com');
                                $mail->addAddress($_POST['email']);     //Add a recipient
                                //Content
                                $mail->isHTML(true);                                  //Set email format to HTML
                                $mail->Subject = 'Email Verification';
                                $mail->Body    = '<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
                                <div style="margin:50px auto;width:70%;padding:20px 0">
                                <div style="border-bottom:1px solid #eee">
                                    <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">OOCPL</a>
                                </div>
                                <p style="font-size:1.1em">Hi ' . $_POST["full_name"] . ',</p>
                                <p>Thank you for choosing Your Brand. Use the following OTP to complete your Sign Up procedures. OTP is valid for 5 minutes</p>
                                <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">' . $otp . '</h2>
                                <p style="font-size:0.9em;">Regards,<br />OOCPL</p>
                                <hr style="border:none;border-top:1px solid #eee" />
                                <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
                                    <p>OOCPL</p>
                                    <p>1600 Amphitheatre Parkway</p>
                                    <p>California</p>
                                </div>
                                </div>
                            </div>';
                                

                                if (!$mail->send()) {
                                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                                }else{
                                    $mail->send();
                                }

                                $_SESSION['verify_user_id'] = $user_id;
                                // $success_message = "We have sent an OTP on your email address for verification, Please enter em";

                                header("Location: Registration.php?verify-otp=" . urlencode($user_id));
                            } catch (Exception $e) {
                                echo "hii3";
                                // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                                // echo "<div id='error-message' data-error=' " . htmlspecialchars($e->getMessage()) . " '></div>";
                                $error_message = "System error, Please contact Administrator.". $e->getMessage();
                            }
                        }
                        else {
                            echo "hii4";
                            // echo "<div id='error-message' data-error=' " . htmlspecialchars($conn->error) . " '></div>";
                            // error
                            $error_message = "System error, Please contact Administrator.". $conn->error;
                        }
                    }
                }
            }
            else{
                $error_message = "Invalid register form csrf token";
            }
        }
        ?>
        <?php

        if (isset($_GET['verify-otp'])) {
            // but this statement checks if the form submitted has post method irrespective of which particular form is submitted 
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if(isset($_POST['csrf_token']) && validateCSRFtoken('otp_form', $_POST['csrf_token']))
                {


                    $otpValue = '';
                    for ($i = 1; $i <= 6; $i++) {

                        $otpValue .= $_POST["digit" . $i];
                    }
                

                    // otp verification form

                    //button
                    //this statement checks if particularly otp form is submitted 
                    if (isset($_POST['verify'])) {
                    

                        if (isset($_GET['verify-otp'])) {
                            $activation_code = $_GET['verify-otp'];
                            $otp = $otpValue;

                            // $sqlSelect = "SELECT * FROM registration_data_table WHERE user_id='$activation_code'";
                            $stmt3 = $conn->prepare("SELECT * FROM registration_data_table WHERE user_id=?");
                            $stmt3->bind_param('i', $activation_code);
                            $stmt3->execute();
                            // $resultSelect = mysqli_query($conn, $sqlSelect);
                            $resultSelect = $stmt3->get_result();

                            // if (mysqli_num_rows($resultSelect) > 0) {
                            if($resultSelect->num_rows > 0){
                                // $rowSelect = mysqli_fetch_assoc($resultSelect);
                                $rowSelect = $resultSelect->fetch_assoc();

                                $rowOtp = $rowSelect['email_verification_code'];
                                $rowSinupTime = $rowSelect['created_at'];

                                $rowSinupTime = date('d-m-Y h:i:s', strtotime($rowSinupTime));
                                $rowSinupTime = date_create($rowSinupTime);
                                date_modify($rowSinupTime, "+5 minutes");
                                $timeUp = date_format($rowSinupTime, 'd-m-Y h:i:s');

                                if ($rowOtp !== $otp) {
                                    // echo"<script>alert('please provide correct OTP..!')</script>";
                                    $otp_msg = "please provide correct OTP..!";
                                } else {
                                    if (date('d-m-Y h:i:s') >= $timeUp) {
                                        //   echo"<script>alert('your time is up...try it again...!')</script>";
                                        $otp_msg = "your time is up...try it again...!";
                                        header("Refresh:1; url=Registration.php");
                                    } else {
                                        //is_active is for registration only means registered with otp verification
                                        $empty_ver_code = '';
                                        $is_active_after = $is_email_verified_after = 1;
                                        // $sqlUpdate = "UPDATE registration_data_table SET email_verification_code='', is_active=1,is_email_verified=1 where email_verification_code='$otp' AND user_id='$activation_code'";
                                        $stmt4 = $conn->prepare("UPDATE registration_data_table SET email_verification_code=?, is_active=?, is_email_verified=? where email_verification_code=? AND user_id=?");
                                        $stmt4->bind_param("siisi", $empty_ver_code, $is_active_after, $is_email_verified_after, $otp, $activation_code);
                                        $resultUpdate = $stmt4->execute();
                                        // $resultUpdate = mysqli_query($conn, $sqlUpdate);
                                        // $resultUpdate = $stmt4->get_result();

                                        if ($resultUpdate) {
                                            //   echo"<script>alert('your account successfully activated')</script>";
                                            $otp_msg = "1";
                                            header("Refresh:2; url=login.php");
                                            //header("Refresh:1; url=http://localhost/registration/index.php");
                                        } else {
                                            //  echo"<script>alert('oops..your account not activated')</script>";
                                            $otp_msg = "oops..your account not activated";
                                        }
                                    }
                                }
                            } else {
                                header("Location:Registration.php");
                            }
                        }
                        
                    }
                }
                else{
                    $error_message = "Invalid otp form csrf token";
                }
            }
        ?>
            <div class="container d-flex justify-content-center align-items-center" style="margin-top: 60px; padding-bottom: 60px;">
                <!-- Card with shadow effect -->
                <div class="card bg-white border-0" style="box-shadow: 0 12px 15px rgba(0, 0, 0, 0.02);">
                    <div class="card-body p-5 text-center">
                        <h4>Verify</h4>
                        <p>Your code was sent to you via email</p>

                        <!-- OTP fields centered horizontally -->
                        <form action="" method="post">
                            <!-- csrf --> 
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('otp_form')); ?>">

                            <div class="otp-field mb-4 d-flex justify-content-center">
                                <input type="number" class="form-control me-2" name="digit1" style="width: 40px;" />
                                <input type="number" class="form-control me-2" name="digit2" style="width: 40px;" disabled />
                                <input type="number" class="form-control me-2" name="digit3" style="width: 40px;" disabled />
                                <input type="number" class="form-control me-2" name="digit4" style="width: 40px;" disabled />
                                <input type="number" class="form-control me-2" name="digit5" style="width: 40px;" disabled />
                                <input type="number" class="form-control me-2" name="digit6" style="width: 40px;" disabled />
                            </div>

                            <!-- Verify button -->
                            <button type="submit" name="verify" class="btn btn-primary mb-3" value="verify">
                                Verify
                            </button>
                        </form>
                        <!-- Resend link -->
                        <p class="resend text-muted mb-0">
                            Didn't receive code? <a href="#">Request again</a>
                        </p>
                        <?php if ($otp_msg == "1") { ?>
                            <span class="text-success">your account successfully activated</span>
                        <?php } else { ?>
                            <span class="text-danger"><?php echo htmlspecialchars($otp_msg); ?></span>
                        <?php } ?>

                    </div>
                </div>
            </div>


    </div>
<?php
        } else {
            // registration form
?>

    <form class="form-right" id="registrationForm" method="post" action="">
        <!-- csrf --> 
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('registration_form')); ?>">

        <h2 class="text-uppercase">Registration Form</h2>
        <?php
            if (!empty($success_message)) {
        ?>
            <div class="success-container text-success"><?php echo htmlspecialchars($success_message);  ?></div>
        <?php
            } else {
        ?>
            <?php if (!empty($error_message)) { ?>
                <div class="error-container text-danger"><?php echo htmlspecialchars($error_message);  ?></div>
            <?php } ?>
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <label>First Name</label>
                    <input type="text" name="full_name" id="full_name" class="input-field" value="<?php if (isset($_POST['full_name'])) {
                                                                                                        echo htmlspecialchars($_POST['full_name']);
                                                                                                    } ?>" required>
                </div>
                <!-- <div class="col-sm-6 mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="input-field" value="<?php if (isset($_POST['last_name'])) {
                                                                                                        echo htmlspecialchars($_POST['last_name']);
                                                                                                    } ?>" required>
                </div> -->
            </div>
            <div class="mb-3">
                <label>Your Email</label>
                <input type="email" class="input-field" name="email" value="<?php if (isset($_POST['email'])) {
                                                                                echo htmlspecialchars($_POST['email']);
                                                                            } ?>" required>
            </div>
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <label>Password</label>
                    <input type="password" name="Password" id="Password" class="input-field" required>
                </div>
                <div class="col-sm-6 mb-3">
                    <label>Confirm Password</label>
                    <input type="password" name="Confirm_password" class="input-field" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="option">I agree to the <a href="#">Terms and Conditions</a>
                    <input type="checkbox" name="Checkbox_terms" required>
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="form-field">
                <input type="submit" value="Register" class="register" name="register">
            </div>
        <?php
            }
        ?>
    </form>
<?php
        }
?>

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
<?php 
ob_end_flush(); // Flush the output buffer 
?>