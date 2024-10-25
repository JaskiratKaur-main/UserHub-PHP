<?php 

session_start();
include 'config.php';

    //generation of csrf token did not used function because there is only one form
    if(!isset($_SESSION['csrf_token']['add_edit_form'])){
        $_SESSION['csrf_token']['add_edit_form'] = bin2hex(random_bytes(32));
    }


    //apply validations on form submission for both add/edit form
    $flag = 0;
    $project_name_err = $creation_date_err = $start_date_err = $deadline_err = $description_err = $skill_err = $status_err = '';
    if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['submit'])){

        $project_name = $_POST['projectName'];
        $project_creation_date = $_POST['creationDate'];
        $project_start_date = $_POST['startDate'];
        $project_deadline = $_POST['deadline'];
        $project_description = $_POST['description'];
        // $project_skills = $_POST['checkbox_values']; //it's an array
        $project_status = $_POST['status'];


        //csrf validation
        if(isset($_SESSION['csrf_token']['add_edit_form']) && hash_equals($_SESSION['csrf_token']['add_edit_form'], $_POST['csrf_token'])){

            if(empty($project_name)){
                $project_name_err = "Please enter a project name";
                $flag = 1;
            }else{
                // Check if first name contains only letters and whitespace
                if (!preg_match("/^[a-zA-Z ]*$/", $project_name)) {
                    $project_name_err = "Only letters and white space allowed";
                    $flag = 1;
                }
            }

            if(empty($project_creation_date)){
                $creation_date_err = "Please enter a creation date";
                $flag = 1;
            }else{
                // Validate date format
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $project_creation_date)) {
                    $creation_date_err = "Invalid date format. Please use YYYY-MM-DD.";
                    $flag = 1; // Set flag indicating error
                }
            }

            if(empty($project_start_date)){
                $start_date_err = "Please enter a start date";
                $flag = 1;
            }else{
                // Validate date format
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $project_start_date)) {
                    $start_date_err = "Invalid date format. Please use YYYY-MM-DD.";
                    $flag = 1; // Set flag indicating error
                }
            }

            if(empty($project_deadline)){
                $deadline_err = "Please enter a deadline date";
                $flag = 1;
            }else{
                // Validate date format
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $project_deadline)) {
                    $deadline_err = "Invalid date format. Please use YYYY-MM-DD.";
                    $flag = 1; // Set flag indicating error
                }
            }

            if(empty($project_description)){
                $description_err = "Please enter project description";
                $flag = 1;
            }else{
                if(strlen($project_description) < 20){
                    $description_err = "Please enter the description 20 characters long";
                    $flag = 1;
                }
            }

            if(empty($_POST['checkbox_values'])){
                $skill_err = "Please enter a skill";
                $flag = 1;
            }

            if(empty($project_status)){
                $status_err = "Please select a project status";
                $flag = 1;
            }

        }else{
            $error_msg = "Invalid csrf token of add form";
        }
    }
    
    

    //If the part of the code that retrieves data for displaying in the form (i.e., the SELECT query based on $_GET['id'])
    // does not have a CSRF check, it is generally acceptable because retrieving data is a read-only operation and does not pose a 
    // security risk. Moreover, it uses prepare which is safe against sql injections 
    if(isset($_GET['id'])){
        //before editing get the data to display in form
        $select = $conn->prepare("SELECT * FROM projects_table WHERE id = ?");
        $select->bind_param('i', $_GET['id']);
        $select->execute();
        $select_result = $select->get_result();
        if($select_result->num_rows > 0){
            $row = $select_result->fetch_assoc();
        }

        if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['submit'])){
            //csrf validation
            if(isset($_SESSION['csrf_token']['add_edit_form']) && hash_equals($_SESSION['csrf_token']['add_edit_form'], $_POST['csrf_token'])){

                if($flag == 0){

                    //updating project skills code
                    if(isset($_POST['checkbox_values'])){

                        $project_skills = $_POST['checkbox_values']; //its an array

                        $stmt = $conn->prepare("DELETE FROM project_skill_pivot WHERE project_id=?");
                        $stmt->bind_param('i', $_GET['id']);
                        $deletedOldSkills = $stmt->execute();
                        //if deleted old skills now insert the current chosen skills 
                        if($deletedOldSkills){
                            foreach($project_skills as $skill){
                                //only insert the skill if it already doesnot exist, else first get id of the skill and insert in pivot directly
                                $stmt = $conn->prepare("SELECT id FROM project_skills WHERE skills=?");
                                $stmt->bind_param("s", $skill);
                                $getSkill = $stmt->execute();
                                if(!$getSkill){
                                    $stmt = $conn->prepare("INSERT INTO project_skills (skills) VALUES(?)");
                                    $stmt->bind_param('s', $skill);
                                    $newSkillAdded = $stmt->execute();
                                    if($newSkillAdded){
                                        $skill_id = $conn->insert_id;
                                        
                                        $stmt = $conn->prepare("INSERT INTO project_skill_pivot (project_id, skill_id) VALUES(?, ?)");
                                        $stmt->bind_param('ii', $_GET['id'], $skill_id);
                                        $stmt->execute();
                                    }
                                }else{
                                    $skillResult = $stmt->get_result();
                                    if($skillResult->num_rows > 0){
                                        $row = $skillResult->fetch_assoc();
                                        $skill_id = $row['id'];
                                    }
                                    //if project id with skill id has links already then donot insert again
                                    $stmt1 = $conn->prepare("SELECT project_id FROM project_skill_pivot WHERE project_id=? AND skill_id=?");
                                    $stmt1->bind_param("ii", $project_id, $skill_id);
                                    $checkSkillLink = $stmt1->execute();
                                    if(!$checkSkillLink){
                                        $stmt2 = $conn->prepare("INSERT INTO project_skill_pivot (project_id, skill_id) VALUES(?, ?)");
                                        $stmt2->bind_param("ii", $_GET['id'], $skill_id);
                                        $stmt2->execute();
                                    }
                                }
                            }

                        }
                    }
    
                    //if id is set in url that means user needs to EDIT
                    $stmt = $conn->prepare("UPDATE projects_table SET project_name = ?, project_creation_date = ?, project_start_date = ?, project_deadline = ?, project_description = ?, project_status = ? WHERE id = ?"); 
                    $stmt->bind_param('ssssssi', $project_name, $project_creation_date, $project_start_date, $project_deadline, $project_description, $project_status, $_GET['id']);
                    if($stmt->execute()){
                        $success_msg = "Project details updated successfully!";
                        header('Refresh:3;url="projects.php?type=2"');
                    }else{
                        $error_msg = "Oops.. some issues while updating the project details!";
                    }
                }
            }
            else{
                $error_msg = "Invalid csrf token of edit form";
            }

        }
    }
    //on clicking add button no id will be send in url but on clicking edit it will so if
    else{
        //means we need to ADD
        if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['submit'])){

            //csrf validation
            if(isset($_SESSION['csrf_token']['add_edit_form']) && hash_equals($_SESSION['csrf_token']['add_edit_form'], $_POST['csrf_token'])){

                if($flag == 0){

                    //begin transaction to avoid any irrelevant records to be inserted in db if their is any mistake, use try catch along with it
                    $conn->begin_transaction();
                    try{
                        $stmt = $conn->prepare("INSERT INTO projects_table (user_id, project_name, project_creation_date, project_start_date, project_deadline, project_description, project_status) VALUES(?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('issssss', $_SESSION['userId'], $project_name, $project_creation_date, $project_start_date, $project_deadline, $project_description, $project_status);
                        $insertProjectExecute = $stmt->execute();
                        
                    
                        if($insertProjectExecute){
                            
                            $project_id = $conn->insert_id; //get the newly inserted project id and use it in skill insertion
                            $_SESSION['projectId'] = $project_id;

                            //add skills
                            if(isset($_POST['checkbox_values'])){
                                $project_skills = $_POST['checkbox_values']; //it's an array
                                // print_r($project_skills);
                                //inserting project skills using normalization
                                foreach($project_skills as $skill){
                                    //only insert the skill if it already doesnot exist, else first get id of the skill and insert in pivot directly
                                    $stmt1 = $conn->prepare("SELECT id FROM project_skills WHERE skills=?");
                                    $stmt1->bind_param("s", $skill);
                                    $stmt1->execute();
                                    $getSkill = $stmt1->get_result();
                                    if($getSkill){
                                        if($getSkill->num_rows == 0){

                                            //take a particular skill and insert in db in project skills table
                                            $stmt2 = $conn->prepare("INSERT INTO project_skills (skills) VALUES(?)");
                                            $stmt2->bind_param("s", $skill);
                                            if($stmt2->execute()){
                                                $skill_id = $conn->insert_id; //get the id of newly inserted skill to be used to be inserted in pivot table 
                                                $stmt3 = $conn->prepare("INSERT INTO project_skill_pivot (project_id, skill_id) VALUES(?, ?)");
                                                $stmt3->bind_param("ii", $project_id, $skill_id);
                                                $stmt3->execute();
                                            }
                                        }else{
                                            $row = $getSkill->fetch_assoc();
                                            $skill_id = $row['id'];

                                            $stmt4 = $conn->prepare("SELECT project_id FROM project_skill_pivot WHERE project_id=? AND skill_id=?");
                                            $stmt4->bind_param("ii", $project_id, $skill_id);
                                            $stmt4->execute();
                                            $checkSkillLink = $stmt4->get_result();
                                            if($checkSkillLink){
                                                if($checkSkillLink->num_rows == 0){
                                                    $stmt5 = $conn->prepare("INSERT INTO project_skill_pivot (project_id, skill_id) VALUES(?, ?)");
                                                    $stmt5->bind_param("ii", $project_id, $skill_id);
                                                    $stmt5->execute();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            //commit in db before success msg
                            $conn->commit();

                            $success_msg = "Data submitted. Project being added...";
                            $type = (int) $_GET['type'];
                            header("Refresh:3;url='projects.php?type=$type'");
                        }
                        else{
                            // Rollback and delete project if insertion failed
                            throw new Exception("Project insertion failed.");
                        }
                    }
                    catch(Exception $e){
                        //instead of this
                        // else{
                        //     // Rollback in case of an error in skill insertion
                        //     $stmt = $conn->prepare("DELETE FROM projects_table WHERE id = ?");
                        //     $stmt->bind_param('i', $project_id);
                        //     $stmt->execute();
                        //     $error_msg = "Oops.. some issues while inserting!";
                        // }
                        $conn->rollback();
                        $error_msg = "Oops.. some issues while inserting!" . $e->getMessage();
                    }

    

                }
            }
            else{
                $error_msg = "Invalid csrf token of add form";
            }
        }
    }
    


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet">
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

    <!-- add inline scripts in external js file for security --> 
    <script src="add_edit_scripts.js"></script>
    
    
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


        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-8">
                    <div class="card" style="border: 2px solid grey;">
                        <div class="card-body px-0 py-4"> <!-- here px-0 is for hr to be full width -->
                            <!-- <div class="mb-3"> -->
                                <h4 class="card-title text-center mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey"><?php echo htmlspecialchars(isset($_GET['id']) ? 'Edit project' : 'Add Project'); ?></h4>
                                <hr>
                            <!-- </div> -->
                            <form action="" method="POST" class="p-4">
                                <!-- add csrf token to form -->
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']['add_edit_form']; ?>">

                                <div class="row">
                                    <div class="col-md-6 pr-5">
                                        <div class="mb-3">
                                            <label for="projectname">Project name</label>
                                            <input type="text" class="form-control" name="projectName" value="<?php echo htmlspecialchars(isset($row['project_name']) ? $row['project_name'] : ''); ?>" id="projectname" placeholder="enter project name">
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($project_name_err) ? $project_name_err : '');?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pl-5">
                                        <div class="mb-3">
                                            <label for="creationdate">Creation Date</label>
                                            <input type="date" class="form-control" name="creationDate" value="<?php echo htmlspecialchars(isset($row['project_creation_date']) ? $row['project_creation_date'] : ''); ?>" id="creationdate" placeholder="">
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($creation_date_err) ? $creation_date_err : '');?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 pr-5">
                                        <div class="mb-3">
                                            <label for="startdate">Start Date</label>
                                            <input type="date" class="form-control" name="startDate" value="<?php echo htmlspecialchars(isset($row['project_start_date']) ? $row['project_start_date'] : ''); ?>" id="startdate" placeholder="">
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($start_date_err) ? $start_date_err : '');?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pl-5">
                                        <div class="mb-3">
                                            <label for="deadline">Deadline</label>
                                            <input type="date" class="form-control" name="deadline" value="<?php echo htmlspecialchars(isset($row['project_deadline']) ? $row['project_deadline'] : ''); ?>" id="deadline" placeholder="">
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($deadline_err) ? $deadline_err : '');?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 pr-5">
                                        <div class="mb-3">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description" placeholder="enter project description" rows="2"><?php echo htmlspecialchars(isset($row['project_description']) ? $row['project_description'] : ''); ?></textarea>
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($description_err) ? $description_err : '');?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pl-5">
                                        <div class="mb-3">
                                            <label for="skills">Skills</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline">
                                                    <!--  echo in_array('html', $skills) ? 'checked' : '';   -->
                                                        <input type="checkbox" class="form-check-input" id="skillhtmlbox" name="checkbox_values[]" value="html" >
                                                        <label class="form-check-label" for="skillhtmlbox">HTML</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline col-md-6">
                                                        <input type="checkbox" class="form-check-input" id="skillcssbox" name="checkbox_values[]" value="sql" >
                                                        <label class="form-check-label" for="skillcssbox">SQL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline col-md-6">
                                                        <input type="checkbox" class="form-check-input" id="skilljsbox" name="checkbox_values[]" value="js" >
                                                        <label class="form-check-label" for="skilljsbox">JavaScript</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline col-md-6">
                                                        <input type="checkbox" class="form-check-input" id="skillphpbox" name="checkbox_values[]" value="php">
                                                        <label class="form-check-label" for="skillphpbox">PhP</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($skill_err) ? $skill_err : '');?></span>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 pr-5">
                                        <div class="mb-3">
                                            <label for="status">Status</label>
                                            <select id="status" name="status" class="form-control" placeholder="">
                                                <option selected="">Choose...</option>
                                                <option name="pending" <?php echo htmlspecialchars((isset($row['project_status']) ? $row['project_status'] : '' == 'Pending') ? 'selected' : ''); ?> >Pending</option>
                                                <option name="completed" <?php echo htmlspecialchars((isset($row['project_status']) ? $row['project_status'] : '' == 'Completed') ? 'selected' : ''); ?>>Completed</option>
                                                <option name="hold" <?php echo htmlspecialchars((isset($row['project_status']) ? $row['project_status'] : '' == 'Hold') ? 'selected' : ''); ?>>Hold</option>
                                                <option name="cancelled" <?php echo htmlspecialchars((isset($row['project_status']) ? $row['project_status'] : '' == 'Cancelled') ? 'selected' : ''); ?>>Cancelled</option>
                                            </select>
                                            <span class="error" style="color:red;"><?php echo htmlspecialchars(isset($status_err) ? $status_err : '');?></span>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="mb-3">
                                    <button type="submit" name="submit" class="btn btn-primary"><?php echo htmlspecialchars(isset($_GET['id']) ? 'Update' : 'Add'); ?></button>
                                    <?php

                                        if(isset($success_msg)){
                                            echo "<span id='successElement' style='color: green;font-weight:bold;display:block;margin-top:20px;'>" . htmlspecialchars($success_msg) . "</span>";
                                        }
                                        if(isset($error_msg)){
                                            echo "<span id='errorElement' style='color: green;font-weight:bold;display:block;margin-top:20px;'>" . htmlspecialchars($error_msg) . "</span>";
                                        }
                                    ?>
                                </div>
                                
                                
                            </form>
                        </div>
                    </div>
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





