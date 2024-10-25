<?php

include "config.php";
session_start();


if(!isset($_SESSION['userId'])){
    header('Location:index.php');
}

//generation of csrf token (you can also use function for generation like done in other pages)
if(!isset($_SESSION['csrf_token']['delete_account_form'])){
    $_SESSION['csrf_token']['delete_account_form'] = bin2hex(random_bytes(32));
}
if(!isset($_SESSION['csrf_token']['delete_account_modal_form'])){
    $_SESSION['csrf_token']['delete_account_modal_form'] = bin2hex(random_bytes(32));
}


$success_msg = $error_msg = $del_err = "";
//check if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account_btn'])){

    //csrf validation
    if(isset($_SESSION['csrf_token']['delete_account_form']) && hash_equals($_SESSION['csrf_token']['delete_account_form'], $_POST['csrf_token_1'])){

        //validation
        if(empty($_POST['why_delete'])){
            $del_err = "Please enter the reason to delete";
        }else{
            //1. Soft delete
            // $is_delete = 1;
            // $is_active = 0;
            // $stmt = $conn->prepare("UPDATE registration_data_table SET is_delete = ?, is_active = ? WHERE user_id = ?");
            // $stmt->bind_param("iii", $is_delete, $is_active, $_SESSION['userId']);
            // if($stmt->execute()){
            //     $success_msg = "Account deleted successfully";
            //     //and logout
            //     session_unset();
            //     session_destroy();
            //     header('Refresh:3;url="index.php"');
            // }else{
            //     $error_msg = "Oops... Could not delete the account";
            // }
            // ------- soft delete end ------

            
        }
    }else{
        $error_msg = "Invalid csrf token of delete account form";
    }
}


//2. Hard delete
// form inside delete modal
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleted'])){

    //csrf validation
    if(isset($_SESSION['csrf_token']['delete_account_modal_form']) && hash_equals($_SESSION['csrf_token']['delete_account_modal_form'], $_POST['csrf_token_2'])){

    
        //2. Hard delete
        //firstly create a backup by storing all the data in another table
        $stmt1 = $conn->prepare('SELECT * FROM registration_data_table r LEFT JOIN user_skills_pivot usp ON usp.user_id = r.user_id WHERE r.user_id = ?');
        $stmt1->bind_param('i', $_SESSION['userId']);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        if($result1->num_rows > 0){
            $userdata = $result1->fetch_assoc();
            $stmt2 = $conn->prepare("INSERT INTO user_backup (user_id, user_profile_image, full_name, your_email, user_bio, user_gender, mobile_no, date_of_birth, country, state, postcode, address , hashed_password, email_verification_code, is_email_verified, is_active, is_delete, created_at, modified_on) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt2->bind_param('isssssssssssssiiiss', $userdata['user_id'], $userdata['user_profile_image'], $userdata['full_name'], $userdata['your_email'], $userdata['user_bio'], $userdata['user_gender'], $userdata['mobile_no'], $userdata['date_of_birth'], $userdata['country'], $userdata['state'], $userdata['postcode'], $userdata['address'], $userdata['hashed_password'], $userdata['email_verification_code'], $userdata['is_email_verified'], $userdata['is_active'], $userdata['is_delete'], $userdata['created_at'], $userdata['modified_on']);
            //as soon as the backup is done delete the user info from the table
            if($stmt2->execute()){
                $stmt3 = $conn->prepare('DELETE FROM registration_data_table WHERE user_id = ?');
                $stmt3->bind_param('i', $_SESSION['userId']);
                if($stmt3->execute()){
                    $success_msg = "Account deleted successfully!";
                    //logout
                    session_unset();
                    session_destroy();
                    header('Refresh:3;url="index.php"');
                }else{
                    $error_msg = "Oops.. issue while deleting account";
                }
            }else{
                $error_msg = "Issue in backing up the account";
            }
        }else{
            $error_msg = "User not found";
        }
    }else{
        $error_msg = "Invalid csrf token of delete account modal form";
    }

}


?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- for header -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="delete_account.js"></script>

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


    <!-- Delete account -->
    <div class="container" style="padding: 6rem;">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-7">
                <!-- <div class="tab-content"> -->
                <!-- <div class="" id="delete_account" role="tabpanel"> -->
                <div class="card">
                    <div class="card-body">

                        <h5 class="card-title">Account Deletion</h5>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="deleteForm">
                            <input type="hidden" id="csrf_token_1" name="csrf_token_1" value="<?php echo htmlspecialchars($_SESSION['csrf_token']['delete_account_form']); ?>"> 

                            <div class="form-group">
                                <label for="inputUsername">Why are you deleting your account</label>
                                <textarea rows="2" class="form-control" id="delete_acc" name="why_delete"></textarea>
                                <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($del_err) ? $del_err : ''); ?></span>
                            </div>
                            <button type="submit" class="btn btn-danger" name="delete_account_btn" id="submit" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Account</button>
                            <?php
                                if(!empty($success_msg)){
                                    echo "<span id='success_msg' style='display:block;margin-top:5px;color:green;font-weight:bold;'>" . htmlspecialchars($success_msg) . "</span>";
                                }
                                if(!empty($error_msg)){

                                    echo "<span id='error_msg' style='display:block;margin-top:5px;color:green;font-weight:bold;'>" . htmlspecialchars($error_msg) . "</span>";
                                }
                            ?>
                        </form>

                    </div>
                </div>
                <!-- </div> -->
                <!-- </div> -->
            </div>
        </div>
    </div>



    <!-- delete account modal -->
     <!-- both modal-dialog and content are required in a modal for styling and also tab index for focus hidden true etc --> 
    <div class="modal" id="deleteModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Confirm deletion</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <!-- csrf -->
                    <input type="hidden" id="csrf_token_2" name="csrf_token_2" value="<?php echo htmlspecialchars($_SESSION['csrf_token']['delete_account_modal_form']); ?>"> 

                    <div class="modal-body">Are you sure you want to delete your account?</div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-danger" name="deleted" type="submit" id="deleted">Delete</button>
                    </div>
                </form>

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


</body>

</html>