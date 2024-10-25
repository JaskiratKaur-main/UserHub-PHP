<?php

include "config.php";
session_start();


//if not logged in donot allow to enter profile page
if (!isset($_SESSION['userId'])) {
    header('Location:index.php');
}

//generation of csrf token did not used function because there is only one form
if(!isset($_SESSION['csrf_token']['profile_form'])){
    $_SESSION['csrf_token']['profile_form'] = bin2hex(random_bytes(32));
}

// Prepare and execute SELECT query
$sql = "SELECT * FROM registration_data_table WHERE user_id = ?"; // Assuming you have a column named 'email_address' to uniquely identify users
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['userId']); // Assuming you're passing the email address via POST
$stmt->execute();
$result = $stmt->get_result();

// Fetch data to display in form if already there
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_profile_image = $row['user_profile_image']; 
    $user_bio = $row['user_bio'];
    $email = $row['your_email'];
    $first_name = $row['full_name'];
    // $last_name = $row['last_name'];
    $user_gender = $row['user_gender'];
    $mobile_no = $row['mobile_no'];
    $date_of_birth = $row['date_of_birth'];
    $country = $row['country'];
    $state = $row['state'];
    $postcode = $row['postcode'];
    $address = $row['address'];


    // $skills_array = [];
    // $skills_array = explode(" ", $row['user_skills']);

    // Process fetched data here
} else {
    echo htmlspecialchars("No records found.");
}

// Define variables and initialize with empty values
$flag = 0;
$first_name_err = $last_name_err = $email_err = $mobile_no_err = $country_err = $state_err = $postcode_err = $address_err = $image_err = "";
$update_message_error = $update_message_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {

    //csrf validation
    if(isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token']['profile_form'], $_POST['csrf_token'])){

        //image validations using mime type validations
        if (empty($_FILES["image"]["name"])) {
            $image_err = "Please choose an image";
            $flag = 1;
        }else{
            // print_r($_FILES['image']);
            $imageSize = $_FILES['image']['size'];
            // temporary file location
            $imagetmp = $_FILES['image']['tmp_name'];
            // $imageType = $_FILES['image']['type'];  //instead of this
            // $_FILES['image']['type'] will still return image/jpeg, because the browser determines it based on the .jpg extension.
            // But mime_content_type($_FILES['image']['tmp_name']) will correctly detect the true nature of the file and return something like application/x-msdownload (for .exe), identifying that it's not a valid image.
            // and prevents spoofing means faking
            $imageType = mime_content_type($_FILES['image']['tmp_name']);

            $exif_data = @exif_read_data($imagetmp);

            $maxSize = 2 * 1024 * 1024; //2Mb
            $allowed_mime_types = ['image/jpg', 'image/jpeg', 'image/png'];
            if($imageSize >= $maxSize){
                $image_err = "Please choose a file of size less than 2MB";
                $flag = 1;
            }else if(!in_array($imageType, $allowed_mime_types)){
                $image_err = "Please choose a jpeg, jpg or png file only";
                $flag = 1;
            }else if(in_array($imageType, ['image/jpeg', 'image/jpg']) && $exif_data === false){ // exif jpeg malware
                //If exif_read_data() fails (for example, if the provided file is not a valid JPEG or cannot be read), it typically generates a warning. Using @ prevents this warning from being displayed to the user. and instead display a cleaner error msg instead of more sensitive details
                // $exif_data = @exif_read_data($imagetmp);
                $image_err = "The uploaded jpeg is corrupted or not a valid image";
                $flag = 1;
            }else{
                $imageName = $_FILES['image']['name'];  //and save in db
                
                
                //firstly upload or save it in a folder in your project
                //transfer it to permanent location
                $uploadDir = 'uploads/';
                move_uploaded_file($imagetmp, $uploadDir.$imageName); //from , to full path
                $_SESSION['user_profile_image'] = $imageName;
                
            }

        }
        

        // Validate First Name
        if (empty($_POST["first_name"])) {
            $first_name_err = "Please enter your first name";
            $flag = 1;
        } else {
            $first_name = test_input($_POST["first_name"]);
            
            // Check if first name contains only letters and whitespace
            if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
                $first_name_err = "Only letters and white space allowed";
                $flag = 1;
            }
        }

        // Validate Last Name
        if (empty($_POST["last_name"])) {
            $last_name_err = "Please enter your last name";
            $flag = 1;
        } else {
            $last_name = test_input($_POST["last_name"]);
            
            // Check if last name contains only letters and whitespace
            if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
                $last_name_err = "Only letters and white space allowed";
                $flag = 1;
            }
        }

        // Validate Phone Number
        if (empty($_POST["mobile_no"])) {
            $mobile_no_err = "Please enter your phone number";
            $flag = 1;
        } else {
            $mobile_no = test_input($_POST["mobile_no"]);
            
            // Check if phone number contains only digits and is 10 digits long
            if (!preg_match("/^\d{10}$/", $mobile_no)) {
                $mobile_no_err = "Please enter a valid 10-digit phone number";
                $flag = 1;
            }
        }

        if (empty($_POST["date_of_birth"])) {
            $date_of_birth_err = "Please enter your date of birth";
            $flag = 1; // Set flag indicating error
        } else {
            $date_of_birth = test_input($_POST["date_of_birth"]);
            
            // Validate date format
            if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_of_birth)) {
                $date_of_birth_err = "Invalid date format. Please use YYYY-MM-DD.";
                $flag = 1; // Set flag indicating error
            }
        }

        // Validate Country
        if (empty($_POST["country"])) {
            $country_err = "Please enter your country";
            $flag = 1;
        } else {
            $country = test_input($_POST["country"]);
            
            // Check if country contains only letters
            if (!preg_match("/^[a-zA-Z]*$/", $country)) {
                $country_err = "Country must contain only letters";
                $flag = 1;
            }
        }

        // Validate State
        if (empty($_POST["state"])) {
            $state_err = "Please enter your state/region";
            $flag = 1;
        } else {
            $state = test_input($_POST["state"]);
            
            // Check if state contains only letters
            if (!preg_match("/^[a-zA-Z]*$/", $state)) {
                $state_err = "State/region must contain only letters";
                $flag = 1;
            }
        }

        // Validate Postcode
        if (empty($_POST["postcode"])) {
            $postcode_err = "Please enter your postcode";
            $flag = 1;
        } else {
            $postcode = test_input($_POST["postcode"]);
        
            // Check if postcode contains only digits and is 6 digits long
            if (!preg_match("/^\d{6}$/", $postcode)) {
                $postcode_err = "Please enter a valid 6-digit postcode";
                $flag = 1;
            }
        }

        // Validate Address
        if (empty($_POST["address"])) {
            $address_err = "Please enter your address";
            $flag = 1;
        } else {
            $address = test_input($_POST["address"]);
            
            // Check if address meets minimum length requirement
            if (strlen($address) < 10) {
                $address_err = "Address must be at least 10 characters long";
                $flag = 1;
            }
            // Additional validation for address format if needed
        }


        // Validate user gender
        if (empty($_POST["user_gender"])) {
            $user_gender_err = "Please select your gender";
            $flag = 1; // Set flag if validation fails
        } else {
            $user_gender = test_input($_POST["user_gender"]);
            
        }

        // Validate user skills
        if (empty($_POST["user_skills"])) {
            $user_skills_err = "Please select at least one skill";
            $flag = 1; // Set flag if validation fails
        }
    }
    else{
        $update_message_error = "Invalid csrf token of profile form";
    }
}

// $first_name = $last_name = $email = $mobile_no = $country = $state = $postcode = $address = "";

// Function to sanitize and validate input data
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    //image validations using mime type validations

    //if not empty
    if (isset($_POST['submit'])) {

        //csrf validation
        if(isset($_SESSION['csrf_token']['profile_form']) && hash_equals($_SESSION['csrf_token']['profile_form'], $_POST['csrf_token'])){

            $user_bio = $_POST['user_bio'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $mobile_no = $_POST['mobile_no'];
            $date_of_birth = $_POST['date_of_birth']; 
            $user_gender = isset($_POST['user_gender']) ? $_POST['user_gender'] : "";
            $country = $_POST['country'];
            $state = $_POST['state'];
            $postcode = $_POST['postcode'];
            $address = $_POST['address'];
            //checkboxes (without normalization)
            // $user_skills = isset($_POST['user_skills']) ? implode(' , ', $_POST['user_skills']) : '';

            



            // $user_skills="";

            // echo $flag;
            if ($flag == 0) {
                // last_name='$last_name', user_skills='$user_skills',

                $conn->begin_transaction();
                try{
                    // $sqlUpdate1 = "UPDATE registration_data_table SET user_profile_image='$imageName', user_gender='$user_gender', full_name='$first_name', user_bio='$user_bio', mobile_no='$mobile_no', date_of_birth='$date_of_birth', country='$country', state='$state', postcode='$postcode', address='$address'  WHERE user_id='{$_SESSION['userId']}'";
                    // $UpdateResult1 = mysqli_query($conn, $sqlUpdate1);
                    $stmt = $conn->prepare("UPDATE registration_data_table SET user_profile_image=?, user_gender=?, full_name=?, user_bio=?, mobile_no=?, date_of_birth=?, country=?, state=?, postcode=?, address=?  WHERE user_id=?");
                    $stmt->bind_param("ssssssssssi", $imageName, $user_gender, $first_name, $user_bio, $mobile_no, $date_of_birth, $country, $state, $postcode, $address, $_SESSION['userId']);
                    $UpdateResult1 = $stmt->execute();
                    if ($UpdateResult1) {
                        
                        //checkbox insertion with normalization
                        if(isset($_POST['user_skills'])){
                            // as the skills can be multiple so it is an array so run a loop
                            foreach($_POST['user_skills'] as $skillName){
                                // check if skill already exists in table skill if yes select its id
                                $skillName = $conn->real_escape_string($skillName);
                                $query1 = "SELECT skill_id FROM skills WHERE skills=?";
                                $stmt = $conn->prepare($query1);
                                $stmt->bind_param("s", $skillName);
                                $stmt->execute();
                                $query_result1 = $stmt->get_result();                    // $query_result1 = mysqli_query($conn, $query1);
                                // if(mysqli_num_rows($query_result1) > 0){
                                if($query_result1->num_rows > 0){
                                    // $skillRow1 = mysqli_fetch_assoc($query_result1);
                                    $skillRow1 = $query_result1->fetch_assoc();
                                    $skills_id = $skillRow1['skill_id'];
                                }else{
                                    $query2 = "INSERT INTO skills (skills) VALUES (?)";
                                    // $query_result2 = mysqli_query($conn, $query2);
                                    $stmt = $conn->prepare($query2);
                                    $stmt->bind_param("s", $skillName);
                                    $stmt->execute();
                                    $query_result2 = $stmt->get_result();
                                    if($query_result2){
                                        // Get the ID of the newly inserted skill
                                        $skills_id = mysqli_insert_id($conn);
                                    }
                                }
                                $userId = $_SESSION['userId'];
                                //check if user has the link with the skill already to prevent duplicate links
                                $stmt = $conn->prepare("SELECT user_id FROM user_skills_pivot WHERE user_id=? AND skill_id=?");
                                $stmt->bind_param("ii", $userId, $skills_id);
                                $stmt->execute();
                                $checkSkillLink = $stmt->get_result();
                                if($checkSkillLink){
                                    if($checkSkillLink->num_rows == 0){
                                        $query3 = "INSERT INTO user_skills_pivot (user_id, skill_id) VALUES (?, ?)";
                                        // $query_result3 = mysqli_query($conn, $query3);
                                        $stmt = $conn->prepare($query3);
                                        $stmt->bind_param("ii", $userId, $skills_id);
                                        $stmt->execute();
                                    }
                                }
                            }

                        }
                    
                        $conn->commit();
                        
                        $update_message_success = 'User data submitted. Updating your profile...';
                        header('Refresh:3;url="profile.php"');
                        
                    } else {
                        // echo "<script>alert('no');</script>";
                        throw(new Exception("Failed"));// Rollback and delete info if insertion failed
                    }
                }catch(Exception $e){
                    $conn->rollback();
                    $update_message_error = "Oops.. some error while updating profile info" . $e->getMessage();
                }

                

            }
            
            //this code will run both time i.e. when image is uploaded or not
        }
        else{
            $update_message_error = "Invalid csrf token of profile form";
        }
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oriental Outsourcing-Home</title>
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <script src="profile.js"></script>
    

    <style>
        .services p {
            font-size: 12px;
        }

        .test {
            align-items: center;
        }

        .test img {
            max-height: 200px;
        }

        .card-body {
            height: 200px;

            overflow: auto;

        }

        .slide img {
            object-fit: cover;
            max-height: 600px;
        }

        .card-footer p {
            font-size: 12px;
        }

        .copy {
            font-size: 12px !important;
        }

        .services {
            background-color: rgb(234, 244, 246);
            text-align: center;
        }

        @media screen and (max-width:767px) {
            .slide img {
                max-height: 100%;
            }

            .next {
                padding-top: 0;
            }

            .carousel-caption {
                padding-bottom: 5px !important;
            }
        }
    </style>

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
                                    <?php if($_SESSION['user_profile_image'] == 'default_user.jpg') { ?>
                                        <img class="img-profile rounded-circle" src="images/default_user.jpg" height="30" width="30">
                                    <?php }else{ ?>
                                        <img class="img-profile rounded-circle" src="uploads/<?php echo htmlspecialchars($_SESSION['user_profile_image'])?>" height="30" width="30">
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


    <div class="container rounded bg-white mt-5 mb-5" style="border:2px solid grey">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="row" id="profileForm" enctype="multipart/form-data">
            
            <!-- add csrf token here --> 
            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']['profile_form']); ?>"> 

            <h4 class="" style="text-align:center;padding:10px">Profile Settings</h4>
            <hr>
            <div class="col-md-4 border-right">
                <div class="d-flex flex-column align-items-center text-center p-3" style="margin-top:50px">
                    <!-- <img class="img-profile rounded-circle" src="images/default_user.jpg" height="150" width="150"> -->
                    <img class="img-profile rounded-circle" id="selectedImage" src="
                        <?php 
                            if($user_profile_image != 'default_user.jpg') { 
                                echo 'uploads/'. htmlspecialchars($user_profile_image) ;
                            }else{
                            echo htmlspecialchars('images/default_user.jpg'); 
                            }
                        ?>" 
                        height="150" width="150"
                    >
                    <div class="small font-italic text-muted mb-4" style="margin-top:10px;">
                        <label>JPG or JPEG no larger than 500 KB</label>

                        <!-- Profile picture upload button-->
                        <label for="upload" id="uploadbtn" class="btn btn-primary"><i class="fa fa-upload"></i> Upload profile image</label>
                        <!-- onchange="displayImage(event) -->
                        <input id="upload" type="file" name="image" class="profile_pic" style="display:none;">
                        <span id="profileImageErr" class="error" style="color:red; display:block;"><?php echo htmlspecialchars(isset($image_err) ? $image_err : ''); ?></span>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center text-center">
                    <label for="$user_bio">User Bio:</label>
                    <textarea rows="3" style="width:20rem" id="$user_bio" name="user_bio" value="" class="form-control"><?php echo htmlspecialchars($user_bio); ?></textarea>
                </div>

            </div>
            <div class="col-md-8 border-right">
                <div class="p-3 py-5">
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels" style="font-size:15px">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="enter first name" value="<?php echo htmlspecialchars($first_name); ?>">
                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($first_name_err) ? $first_name_err : ''); ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="labels" style="font-size:15px">Last Name</label>
                            <!-- value="<?php echo $last_name ?>" -->
                            <input type="text" class="form-control" name="last_name" placeholder="enter last name" value="">
                            <span class="error" style="color:red"><?php echo htmlspecialchars(isset($last_name_err) ? $last_name_err : ''); ?></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels" style="font-size:15px">Mobile Number</label>
                            <input type="text" class="form-control" placeholder="enter mobile number" name="mobile_no" value="<?php echo htmlspecialchars($mobile_no); ?>">
                            <span class="error" style="color:red"><?php echo htmlspecialchars(isset($mobile_no_err) ? $mobile_no_err : ''); ?></span>
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1" for="inputGender" style="font-size: 15px">Gender </label>
                            <div class="" style="display: flex;">
                                <div class="col-md-3">
                                    <label for="input">Male&nbsp;</label><input id="inputGenderMale" type="radio" name="user_gender" value="Male" <?php echo htmlspecialchars($user_gender == 'Male' ? 'checked' : ''); ?>>
                                </div>
                                <div class="col-md-3">
                                    <label for="input">Female&nbsp;</label><input id="inputGenderFemale" type="radio" name="user_gender" value="Female" <?php echo htmlspecialchars($user_gender == 'Female' ? 'checked' : ''); ?>>
                                </div>
                            </div>
                            <span class="error" style="color:red"><?php echo htmlspecialchars(isset($user_gender_err) ? $user_gender_err : ''); ?></span> <!-- php validation -->
                            <span id="genderError"></span> <!-- jquery validation -->
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="labels" style="font-size:15px">Email ID</label>
                            <input type="text" class="form-control" placeholder="enter email id" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
                        </div>
                        <div class="col-md-6"><label class="labels" style="font-size:15px">Date of Birth</label>
                            <input type="date" class="form-control" placeholder="enter date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>"> <!-- //?? = null coalescing operator = Fallback to an empty string if not set -->
                            <span class="error" style="color:red"><?php echo htmlspecialchars(isset($date_of_birth_err) ? $date_of_birth_err : ''); ?></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4"><label class="labels" style="font-size:15px">Country</label><input type="text" class="form-control" placeholder="enter country" name="country" value="<?php echo htmlspecialchars($country); ?>"><span class="error" style="color:red"><?php echo htmlspecialchars(isset($country_err) ? $country_err : ''); ?></span></div>
                        <div class="col-md-4"><label class="labels" style="font-size:15px">State/Region</label><input type="text" class="form-control" placeholder="enter state" name="state" value="<?php echo htmlspecialchars($state); ?>"><span class="error" style="color:red"><?php echo htmlspecialchars(isset($state_err) ? $state_err : ''); ?></span></div>
                        <div class="col-md-4"><label class="labels" style="font-size:15px">Postcode</label><input type="text" class="form-control" placeholder="enter postcode" name="postcode" value="<?php echo htmlspecialchars($postcode); ?>"><span class="error" style="color:red"><?php echo htmlspecialchars(isset($postcode_err) ? $postcode_err : ''); ?></span></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><label class="labels" style="font-size:15px">Address</label><input type="text" class="form-control" placeholder="enter address" name="address" value="<?php echo htmlspecialchars($address); ?>"><span class="error" style="color:red"><?php echo htmlspecialchars(isset($address_err) ? $address_err : ''); ?></span></div>
                    </div>
                    <div class="row mt-3 col-md-12">
                        <label class="small mb-1" for="inputskills" style="font-size:15px">Skills </label>
                        <div class="col-md-6" style="display: flex;">
                            <div style="margin-right: 30px;">
                                <label for="input">DSA&nbsp;</label>
                                <!-- value = echo (in_array('DSA', $skills_array)) ? 'checked' : '';  -->
                                <input id="inputskills1" type="checkbox" name="user_skills[]" value="DSA">
                            </div>
                            <div style="margin-right: 30px;">
                                <label for="input">OOPS&nbsp;</label>
                                <input id="inputskills2" type="checkbox" name="user_skills[]" value="OOPS">
                            </div>
                            <div style="margin-right: 30px;">
                                <label for="input">APTITUDE&nbsp;</label>
                                <input id="inputskills3" type="checkbox" name="user_skills[]" value="APTITUDE">
                            </div>
                            <div style="margin-right: 30px;">
                                <label for="input">SQL&nbsp;</label>
                                <input id="inputskills4" type="checkbox" name="user_skills[]" value="SQL">
                            </div>
                        </div>
                        <span class="error" style="color:red"><?php echo htmlspecialchars(isset($user_skills_err) ? $user_skills_err : ''); ?></span> <!-- php validation -->
                        <span id="skillsError" style="color:red"></span> <!-- jquery validation -->
                    </div>
                </div>
            </div>
            <p class="mt-5 text-center" style="margin-bottom:10px;padding:none">
                <button class="btn btn-primary profile-button" type="submit" value="submit" name="submit">Save Profile</button>
                <?php
                if (!empty($update_message_success)) {
                    echo "<span id='update-message-success' style='margin-top:5px;font-weight:bold;color:green;;display:block'>" . htmlspecialchars($update_message_success) . "</span>";
                } else if (!empty($update_message_error)) {
                    echo "<span id='update-message-error' style='margin-top:5px;font-weight:bold;color:red;display:block'>" . htmlspecialchars($update_message_error) . "</span>";
                }
                ?>
            </p>


        </form>
    </div>

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

<?php









?>