<?php


session_start();
include 'config.php';

if (!isset($_SESSION['userId'])) {
    header('Location:index.php');
}

// When you click on logout button this php will run
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    // Redirect to login page or wherever you want
    header("Location: index.php");
    exit;
}

//CSRF 
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
//validate csrf token of filter and search forms in this projects page using get method
if(isset($_GET['filter_csrf_token'])) {

    // echo $_SESSION['csrf_token']['filter_form'];
    // echo "<br>";
    // echo $_GET['filter_csrf_token'];
    if(validateCSRFtoken('filter_form', $_GET['filter_csrf_token'])){
    }
    else{
        $error_msg = "Invalid filter form csrf token";
    }
}

if(isset($_GET['search_csrf_token'])){

    // echo $_SESSION['csrf_token']['search_form'];
    // echo "<br>";
    // echo $_GET['search_csrf_token'];
    if(validateCSRFtoken('search_form', $_GET['search_csrf_token'])){
    }else{
        $error_msg = "Invalid search form csrf token";
    }
}



// CSRF Validation for Delete Modal Form (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_row'])){
 
    if(isset($_POST['csrf_token']) && validateCSRFtoken('delete_modal_form', $_POST['csrf_token'])) {

        // When Delete button is clicked, run this logic
        if (isset($_POST['id'])) {
            $stmt = $conn->prepare("DELETE FROM projects_table WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
        }
    } else {
        $error_msg = "Invalid delete modal form CSRF token";
    }
}
//CSRF ends



// For extra protection these are the columns that the user can sort by (in your database table).
$columns = array('user_id', 'project_name', 'project_creation_date', 'project_start_date', 'project_deadline', 'project_status');

// Only get the column if it exists in the above columns array, if it doesn't exist the database table will be sorted by the first item in the columns array.
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];

// Get the sort order for the column, ascending or descending, default is ascending.
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oriental Outsourcing-Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="project.js"></script>

</head>
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

<body>
    <!-- <div class="d-flex flex-column min-vh-100"> --> <!-- no need of this div --> 
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
    <!-- old of kunal's code -->
    <!-- <nav class="navbar navbar-expand-md bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="#"> <img src="assets/logo-dark.png" class="img-fluid " alt="oriental" width="95" height="35"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                        Dropdown menu For projects option 
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Projects
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="projects.php?type=1">All Projects</a></li>
                            <li><a class="dropdown-item" href="projects.php?type=2">My Projects</a></li>
                            <li><a class="dropdown-item" href="projects.php?type=3">Projects matching my skills</a>
                            </li>
                        </ul>
                    </li>

                    Dropdown menu For profile option
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Password</a></li>
                            <li><a class="dropdown-item" href="#">Delete Account</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 large"></span>
                            <img class="img-profile rounded-circle" src="uploads/<?php echo $_SESSION['profile_img'] ?>" style="width: 40px; height: 40px;">
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal">Import</a></li>
                            <li><a href="export.php" class="dropdown-item">export</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav> -->
    
    <div class="mx-2" id="table_spacing">

        <!-----------------Search And add button html Start-------------->

        <div class="py-3">
            <div class="container mt-4">
                <div class="d-flex justify-content-between" >
                    <div>
                        <a href="add_edit_projects.php?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>" class="btn btn-primary mx-2 mt-4 mb-4 px-4">ADD</a>
                    </div>
                    <!-- search --> 
                    <div class="mt-4">
                        <form id="searchForm" action="" method="post" role="search" data-type="<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>">
                            <!-- csrf --> 
                            <input type="hidden" id="search_csrf_token" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('search_form')); ?>">

                            <div class="input-group d-flex">
                                <input type="search" name="search" id="search" placeholder="Search" class="form-control rounded">
                                <button type="submit" class="btn btn-primary" name="search_btn"><i class="fa fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-----------------Search And add button html End-------------->

            <!------------------------------Filter code start-------------------------------------->
            <div class="filter-button mt-5" style="margin-left:120px;">
                <form class="row align-items-center" method="POST" action="" name="filter-form" id="filterForm" data-type="<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>">
                    <!-- csrf --> 
                    <input type="hidden" id="filter_csrf_token" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('filter_form')); ?>">

                    <div class="col">
                        <label class="form-label">From Starting Date:</label>
                        <input type="date" class="form-control" placeholder="Start" name="date1" id="date1" value="<?php echo htmlspecialchars(isset($_POST['date1']) ? $_POST['date1'] : ''); ?>" />
                    </div>
                    <div class="col">
                        <label class="form-label">To Starting Date:</label>
                        <input type="date" class="form-control" placeholder="End" name="date2" id="date2" value="<?php echo htmlspecialchars(isset($_POST['date2']) ? $_POST['date2'] : ''); ?>" />
                    </div>
                    <div class="col">
                        <label class="form-label">Deadline Date:</label>
                        <input type="date" class="form-control" placeholder="Deadline" name="date3" id="date3" value="<?php echo htmlspecialchars(isset($_POST['date3']) ? $_POST['date3'] : ''); ?>" />
                    </div>
                    <div class="col">
                        <label for="project_filter_status" class="form-label">Project Status:</label>
                        <select class="form-select" name="project_filter_status" id="project_filter_status">
                            <option value="">--Select--</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Canceled">Canceled</option>
                            <option value="Hold">Hold</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="project_filter_skills" class="form-label">Project Skills:</label>
                        <select class="form-select" name="project_filter_skills" id="project_filter_skills">
                            <option value="">--Select--</option>
                            <option value="JAVA">JAVA</option>
                            <option value="CPP">CPP</option>
                            <option value="HTML">HTML</option>
                            <option value="CSS">CSS</option>
                            <option value="JAVASCRIPT">JAVASCRIPT</option>
                            <option value="PHP">PHP</option>
                        </select>
                    </div>

                    <div class="col">
                        <button class="btn btn-primary ml-4" type="submit" name="filter_search">
                            <span class="fa fa-search"></span>
                        </button>
                        <a href="projects.php?type=<?php echo htmlspecialchars($_GET['type']); ?>" class="btn btn-success ml-2">
                            <span class="fa fa-refresh"></span>
                        </a>
                    </div>
                </form>
            </div>
            <!-------------------------------Filter code end-------------------------->

            
        </div>
        <!-- error or success msg displayed here --> 
        <?php

            if(isset($success_msg)){
                echo "<span id='successElement' style='color: green;font-weight:bold;display:block;margin-top:20px;'>" . htmlspecialchars($success_msg) . "</span>";
            }
            if(isset($error_msg)){
                echo "<span id='errorElement' style='color: green;font-weight:bold;display:block;margin-top:20px;'>" . htmlspecialchars($error_msg) . "</span>";
            }
        ?>
        <div class="container justify-content-center px-4">
            <table class="table table-bordered my-5" id="dataTable" data-type="<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>">
                <thead class="table-dark mx-5">
                    <tr>
                        <!-- sorting on clicking the col header along with others (info in url) --> 
                        <th class="rounded-start-2">
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=user_id&order=<?php echo htmlspecialchars($column == 'user_id' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                } ?>" class="text-decoration-none text-light">User ID<i class="fas fa-sort ms-1"></i></a>
                        </th>
                        <th>
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=project_name&order=<?php echo htmlspecialchars($column == 'project_name' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                        } ?>" class="text-decoration-none text-light">Project Name<i class="fas fa-sort ms-1"></i></a>
                        </th>
                        <th>
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=project_creation_date&order=<?php echo htmlspecialchars($column == 'project_creation_date' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                                            } ?>" class="text-decoration-none text-light">Project Creation
                                Date<i class="fas fa-sort ms-1"></i></a>
                        </th>
                        <th>
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=project_start_date&order=<?php echo htmlspecialchars($column == 'project_start_date' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                                    } ?>" class="text-decoration-none text-light">Project Start
                                Date<i class="fas fa-sort ms-1"></i></a>
                        </th>
                        <th>
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=project_deadline&order=<?php echo htmlspecialchars($column == 'project_deadline' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                                } ?>" class="text-decoration-none text-light">Project Deadline<i class="fas fa-sort ms-1"></i></a>
                        </th>
                        <th>Project Skills</th>
                        <th>Project Description</th>
                        <th <?php if ($_GET['type'] != 2)
                                echo htmlspecialchars('class="rounded-end-2"'); ?>>
                            <a href="?type=<?php echo htmlspecialchars(isset($_GET['type']) ? $_GET['type'] : ''); ?>&page=<?php echo htmlspecialchars(isset($_GET['page']) ? $_GET['page'] : '1'); ?>&column=project_status&order=<?php echo htmlspecialchars($column == 'project_status' && $sort_order == 'ASC' ? 'desc' : 'asc'); ?><?php if (isset($_GET['search'])) { ?>&search=<?php echo htmlspecialchars($_GET['search']);
                                                                                                                                                                                                                                                                                                                            } ?>" class="text-decoration-none text-light">Project Status<i class="fas fa-sort ms-1"></i></a>
                        </th>

                        <!------------------------------Table Head Code For Type 2 Start---------------------------------------->

                        <?php
                        if (isset($_GET['type'])) {
                            if ($_GET['type'] == 2) { ?>
                                <th>Project Edit</th>
                                <th class="rounded-end-2">Project Delete</th>
                    </tr>
            <?php }
                        } ?>

            <!------------------------------Table Head Code For Type 2 End---------------------------------------->

            <!------------------------------Table Head Code For Type 1 && 2 && 3 End---------------------------------------->

                </thead>
                <?php
                // Check if the 'type' parameter exists in the URL
                if (isset($_GET['type'])) {
                    // Get the value of the 'type' parameter
                    $type = $_GET['type'];
                    // Modify the SQL query based on the 'type' parameter
                    switch ($type) {
                        case 1:
                            global $sql_case_1;
                            //more than one where clause is not allowed in a query but this works because either there will be a filter or search
                            //group_concat() -> is an aggregate function (which groups multiple rows to one row) ,which concats multiple rows into one row comma separated
                            //use of groupby clause is important(else you will get error "only_full_group_by" to include select column in groupby) with aggregate functions which displays the aggregate column with result and the groupby column, if the groupby column has same name multiple rows then they will be grouped to a single row and a result column and same applies with other rows if they more than 1, else the groupby column and aggregate column
                            //note: if you write a query selecting a column and then aggregate function like sum(col) then these columns which you are selecting again selecting that should be either the part of the groupby or aggregate, else you will get an error from only_full_group_by
                            $sql_case_1 = "SELECT pt.*, 
                            GROUP_CONCAT(ps.skills) AS skills FROM projects_table as pt 
                            JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                            JOIN project_skills as ps ON pst.skill_id = ps.id 
                            GROUP BY pt.id";  // // you can also groupby primary key else just add select columns in aggregate function or in groupby
                            $result = $conn->query($sql_case_1);
                            break;

                        case 2:
                            global $sql_case_2;
                            $sql_case_2 = "SELECT pt.*, 
                            GROUP_CONCAT(ps.skills) AS skills FROM projects_table as pt 
                            JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                            JOIN project_skills as ps ON pst.skill_id = ps.id
                            WHERE user_id = {$_SESSION['userId']}
                            GROUP BY pt.id";
                            $result2 = $conn->query($sql_case_2);
                            break;

                        case 3:
                            global $sql_case_3;
                            $sql_case_3 = "SELECT pt.*, 
                            GROUP_CONCAT(ps.skills) AS skills FROM projects_table as pt 
                            JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                            JOIN project_skills as ps ON pst.skill_id = ps.id 
                            WHERE user_id != {$_SESSION['userId']}
                            GROUP BY pt.id, pt.user_id, pt.project_name, pt.project_creation_date, pt.project_start_date, pt.project_deadline, pt.project_description, pt.project_status";
                            $result3 = $conn->query($sql_case_3);
                            break;
                    }
                }

                // Check if the result variable is set
                if (isset($result)) {
                    /* Pagination code for type 1 to set limit per page Start **/
                    // Number of records per page
                    $recordsPerPage = 6;
                    // Current page number
                    if (isset($_GET['page'])) {
                        $currentPage = $_GET['page'];
                    } else {
                        $currentPage = 1;
                    }
                    // Calculate the starting record index i.e. the index where the first record of that page is suppose u r on page 2 then the first record of page 2 will be on 7th index as per records per page and thus can be calculated by the formula
                    $startFrom = ($currentPage - 1) * $recordsPerPage;
                    /* Pagination code for type 1 to set limit per page End **/

                    /* Searching code for type 1 start **/
                    if (isset($_GET['search'])) {
                        $search = $_GET['search'];
                        //here user id has used equal to instead of like because it is numeric or integer , And like here searches for start,middle and end all of these
                        //description is excluded for search
                        $sql_case_1 .= " HAVING pt.user_id = '$search' OR pt.project_name LIKE '%$search%' OR pt.project_status LIKE '%$search%' OR skills LIKE '%$search%'";
                    }
                    /* Searching code for type 1 End **/

                    /* table Code for type 1 with sorting and pagination End **/ 
                    else if (isset($_GET['filter'])) {
                        // Initialize an array to store the filter conditions
                        $filter_conditions = [];

                        // Define the keys corresponding to parameters and their conditions
                        $parameters = [
                            'date1' => 'pt.project_start_date >=',
                            'date2' => 'pt.project_start_date <=',
                            'date3' => 'pt.project_deadline =',
                            'project_filter_status' => 'pt.project_status =',
                            'project_filter_skills' => 'skills like'
                        ];

                        // Iterate over the parameters array
                        foreach ($parameters as $key => $condition) {
                            // Check if the parameter exists in the $_GET array and is not empty
                            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                                // Get the value the main purpose of this code
                                $value = "";

                                // If the parameter is project_filter_skills, add '%' signs for a partial match
                                if ($key === 'project_filter_skills') {
                                    $value = $_GET[$key];
                                    $value = "%$value%";
                                } else {
                                    // Assign value directly for other parameters
                                    $value = $_GET[$key];
                                }

                                // Add condition to filter
                                $filter_conditions[] = "$condition '$value'";
                            }
                        }

                        // If there are filter conditions, add them to the query
                        if (!empty($filter_conditions)) {
                            // Add WHERE keyword and concatenate filter conditions
                            $sql_case_1 .= " HAVING " . implode(" AND ", $filter_conditions);
                        }
                    }
                    //else part is not compulsory if you have nothing to write then skip not compulsory in any language 
                    /* Filtration code for type 1 End **/

                    //if search and filtration both are not applied (sorting for the whole table not on searching filtering records)
                    //this is both sorting and pagination code limit is for pagination for records per page to be displayed on sorting
                    $sql_case_1 .= " ORDER BY " . $column . " " . $sort_order . " LIMIT " . $startFrom . ", " . $recordsPerPage;

                    // Execute the $sql_case_1 SQL query
                    $result = $conn->query($sql_case_1);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_creation_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_start_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_deadline']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['skills']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>" . htmlspecialchars('No data found') . "</td></tr>";
                    }

                    /*------------------------------Fatching Data for Table Body Type 1 Code End----------------------------------------*/
                } else if (isset($result2)) {
                    /* Pagination code for type 2 to set limit per page Start **/
                    // Number of records per page
                    $recordsPerPage = 6;
                    // Current page number
                    if (isset($_GET['page'])) {
                        $currentPage = $_GET['page'];
                    } else {
                        $currentPage = 1;
                    }
                    // Calculate the starting record index
                    $startFrom = ($currentPage - 1) * $recordsPerPage;
                    /* Pagination code for type 2 to set limit per page End **/

                    // $sql_case_2 .= " WHERE user_id = " . $_SESSION['userId'];

                    /* Searching code for type 2 Start **/
                    if (isset($_GET['search'])) {
                        $search = $_GET['search'];

                        //Here, we can't search in user_id because user_id will be same i.e. logged-in user's id
                        $sql_case_2 .= " HAVING (pt.user_id = '$search' OR pt.project_name LIKE '%$search%' OR pt.project_status LIKE '%$search%' OR skills LIKE '%$search%')";
                    }
                    /* Searching code for type 2 End **/

                    /* table Code for type 2 with sorting and pagination End **/
                    if (isset($_GET['filter'])) {
                        // Initialize an array to store the filter conditions
                        $filter_conditions = [];

                        // Define the keys corresponding to parameters and their conditions
                        $parameters = [
                            'date1' => 'pt.project_start_date >=',
                            'date2' => 'pt.project_start_date <=',
                            'date3' => 'pt.project_deadline =',
                            'project_filter_status' => 'pt.project_status =',
                            'project_filter_skills' => 'skills like'
                        ];

                        // Iterate over the parameters array
                        foreach ($parameters as $key => $condition) {
                            // Check if the parameter exists in the $_GET array and is not empty
                            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                                // Get the value
                                $value = "";

                                // If the parameter is project_filter_skills, add '%' signs for a partial match
                                if ($key === 'project_filter_skills') {
                                    $value = $_GET[$key];
                                    $value = "%$value%";
                                } else {
                                    // Assign value directly for other parameters
                                    $value = $_GET[$key];
                                }

                                // Add condition to filter
                                $filter_conditions[] = "$condition '$value'";
                            }
                        }

                        // If there are filter conditions, add them to the query
                        if (!empty($filter_conditions)) {
                            // concatenate filter conditions
                            $sql_case_2 .= " HAVING " . implode(" AND ", $filter_conditions);
                        }
                    }
                    /* Filtration code for type 2 End **/

                    $sql_case_2 .= " ORDER BY " . $column . " " . $sort_order . " LIMIT " . $startFrom . ", " . $recordsPerPage;

                    $result = $conn->query($sql_case_2);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_creation_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_start_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_deadline']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['skills']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_status']) . "</td>";
                            echo "<td><button class='editBtn btn btn-primary edit' data-project-id='" . $row['id'] . "' >Edit</button></td>"; 
                            echo "<td><button class='deleteBtn btn btn-danger delete' data-project-id='" . $row['id'] . "'>Delete</button></td>"; 
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>" . htmlspecialchars('No data found') . "</td></tr>";
                    }

                    /*------------------------------Fatching Data for Table Body Type 2 Code End----------------------------------------*/
                } else if (isset($result3)) {
                    
                    /* Pagination code for type 3 to set limit per page Start **/
                    // Number of records per page
                    $recordsPerPage = 6;
                    // Current page number
                    if (isset($_GET['page'])) {
                        $currentPage = $_GET['page'];
                    } else {
                        $currentPage = 1;
                    }
                    // Calculate the starting record index
                    $startFrom = ($currentPage - 1) * $recordsPerPage;
                    /* Pagination code for type 3 to set limit per page End **/

                    /* Get user skills from user_info table and match it with projects skill code start**/
                    $userSkills = [];
                    $id = $_SESSION['userId'];
                    $sql_skills = "SELECT s.skills FROM skills AS s JOIN user_skills_pivot AS usp ON usp.skill_id = s.skill_id where usp.user_id= $id";
                    $result_skills = $conn->query($sql_skills);
                    if($result_skills->num_rows > 0){
                        while($row = $result_skills->fetch_assoc()){
                            $userSkills[] = $row['skills'];
                        }
                    }
                    // print_r($userSkills);
    
                    $skill_conditions = [];
                    // Generate conditions for each skill
                    foreach ($userSkills as $skill) {
                        $skill_conditions[] = "'%" . $skill . "%'";
                    }
        
                    $combined_conditions = implode(' OR skills LIKE ', $skill_conditions);
                    /* Get user skills from user_info table and match it with projects skill code End**/

                    // $sql_case_3 .= " WHERE user_id != " . $_SESSION['userId'];

                    /*When we make a new account at starting that account have no skills added at starting so this condition will run**/
                    if (!empty($userSkills)) {
                        $sql_case_3 .= " HAVING (ps.skills LIKE $combined_conditions)";

                        /* Searching code for type 3 Start **/
                        if (isset($_GET['search'])) {
                            $search = $_GET['search'];
                            $sql_case_3 .= " AND (user_id = '$search' OR project_name LIKE '%$search%' OR project_status LIKE '%$search%' OR ps.skills LIKE '%$search%')";
                        }
                        /* Searching code for type 3 End **/ 
                        else if (isset($_GET['filter'])) {
                            // Initialize an array to store the filter conditions
                            $filter_conditions = [];

                            // Define the keys corresponding to parameters and their conditions
                            $parameters = [
                                'date1' => 'project_start_date >=',
                                'date2' => 'project_start_date <=',
                                'date3' => 'project_deadline =',
                                'project_filter_status' => 'project_status =',
                                'project_filter_skills' => 'skills like '
                            ];

                            // Iterate over the parameters array
                            foreach ($parameters as $key => $condition) {
                                // Check if the parameter exists in the $_GET array and is not empty
                                if (isset($_GET[$key]) && $_GET[$key] !== '') {
                                    // Get the value

                                    $value = "";

                                    // If the parameter is project_filter_skills, add '%' signs for a partial match
                                    if ($key === 'project_filter_skills') {

                                        $value = $_GET[$key];
                                        $value = "%$value%";
                                    } else {
                                        // Assign value directly for other parameters
                                        $value = $_GET[$key];
                                    }

                                    // Add condition to filter
                                    $filter_conditions[] = "$condition '$value'";
                                }
                            }
                            /*When the skills is added then this query run**/

                            // If there are filter conditions, add them to the query
                            if (!empty($filter_conditions)) {
                                // Add WHERE keyword and concatenate filter conditions
                                $sql_case_3 .= " AND " . implode(" AND ", $filter_conditions);
                            }


                            //  $sql_case_3 .= " ORDER BY " . $column . " " . $sort_order . " LIMIT " . $startFrom . ", " . $recordsPerPage;
                        }
                        /* Filtration code for type 3 End **/

                        //if search and filtration both are not applied
                        $sql_case_3 .= " ORDER BY " . $column . " " . $sort_order . " LIMIT " . $startFrom . ", " . $recordsPerPage;
                        

                        $result = $conn->query($sql_case_3);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_creation_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_start_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_deadline']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['skills']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_description']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>" . htmlspecialchars('No data found') . "</td></tr>";
                        }
                    } else if (empty($userSkills)) {
                        echo "<tr><td colspan='2'>" . htmlspecialchars('No data found') . "</td></tr>";
                        // die;
                    }

                    /* table Code for type 3 with sorting and pagination End **/

                    /*------------------------------Fatching Data for Table Body Type 3 Code End----------------------------------------*/
                }
                ?>

                <!------------------------------Table body Code End---------------------------------------->

                </tbody>
            </table>
        </div>
        <!------------------------------Pagination Code Start---------------------------------------->

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 justify-content-center text-center">
                    <?php
                    
                    // Pagination links
                    if ($_GET['type'] == 1) {
                        $sql = "SELECT COUNT(DISTINCT pt.id) AS total, GROUP_CONCAT(ps.skills) as skills FROM projects_table as pt 
                        JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                        JOIN project_skills as ps ON pst.skill_id = ps.id
                        ";

                        //Pagination for searching in type 1
                        if (isset($_GET['search']) && !isset($_GET['filter'])) {
                            $search = $_GET['search'];
                            $sql .= " WHERE pt.user_id = '$search' OR pt.project_name LIKE '%$search%' OR pt.project_status LIKE '%$search%' OR skills LIKE '%$search%'";
                        }
                        //Pagination for Type 1 without searching
                        else if (!isset($_GET['search']) && isset($_GET['filter'])) {
                            // If there are filter conditions, add them to the query
                            if (!empty($filter_conditions)) {
                                // Add WHERE keyword and concatenate filter conditions
                                $sql .= " WHERE " . implode(" AND ", $filter_conditions);
                            } else {
                                $sql .= "";
                            }
                        }
                    } else if ($_GET['type'] == 2) {
                        $sql = "SELECT COUNT(DISTINCT pt.id) AS total,
                        GROUP_CONCAT(ps.skills) as skills FROM projects_table as pt 
                        JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                        JOIN project_skills as ps ON pst.skill_id = ps.id 
                        WHERE user_id = " . $_SESSION['userId'];

                        //Pagination for searching in type 1
                        if (isset($_GET['search']) && !isset($_GET['filter'])) {
                            $search = $_GET['search'];

                            //Here, we can't search in user_id because user_id will be same i.e. logged-in user's id
                            $sql .= " AND (pt.user_id = '$search' OR pt.project_name LIKE '%$search%' OR pt.project_status LIKE '%$search%' OR ps.skills LIKE '%$search%')";
                        }
                        //Pagination for Type 1 without searching
                        else if (!isset($_GET['search']) && isset($_GET['filter'])) {
                            // If there are filter conditions, add them to the query
                            if (!empty($filter_conditions)) {
                                // Add WHERE keyword and concatenate filter conditions
                                $sql .= " AND " . implode(" AND ", $filter_conditions);
                            } else {
                                $sql .= "";
                            }
                        }
                    } else if ($_GET['type'] == 3) {
                        $sql = "SELECT COUNT(DISTINCT pt.id) AS total, GROUP_CONCAT(ps.skills) as skills FROM projects_table as pt 
                        JOIN project_skill_pivot as pst ON pst.project_id = pt.id 
                        JOIN project_skills as ps ON pst.skill_id = ps.id 
                        WHERE user_id != " . $_SESSION['userId'];
                        /* Get user skills from user_info table and match it with projects skill code start**/
                        $userSkills = [];
                        $id = $_SESSION['userId'];
                        $sql_skills = "SELECT s.skills FROM skills AS s JOIN user_skills_pivot AS usp ON usp.skill_id = s.skill_id where usp.user_id= $id";
                        $result_skills = $conn->query($sql_skills);
                        if($result_skills->num_rows > 0){
                            while($row = $result_skills->fetch_assoc()){
                                $userSkills[] = $row['skills'];
                            }
                        }
                        $skill_conditions = [];
                        // Generate conditions for each skill
                        foreach ($userSkills as $skill) {
                            $skill_conditions[] = "'%" . $skill . "%'";
                        }
                        $combined_conditions = implode(' OR ps.skills LIKE ', $skill_conditions);
                        /* Get user skills from user_info table and match it with projects skill code End**/
                        if(!empty($userSkills)){
                            $sql .= " AND (ps.skills LIKE $combined_conditions)";
                            // echo $sql;
                        }else {
                            // If userSkills is empty, you might want to return no results
                            $sql .= " AND pt.id IS NULL"; // Ensures no results are returned
                        }                    

                        //Pagination for searching in type 1
                        if (isset($_GET['search']) && !isset($_GET['filter'])) {
                            $search = $_GET['search'];
                            //Here, we can't search in user_id because user_id will be same i.e. logged-in user's id
                            $sql .= " AND (pt.user_id  = '$search' or  pt.project_name LIKE '%$search%' OR pt.project_status LIKE '%$search%' OR ps.skills LIKE '%$search%')";
                        }
                        //Pagination for Type 1 without searching
                        else if (!isset($_GET['search']) && isset($_GET['filter'])) {
                            // If there are filter conditions, add them to the query
                            if (!empty($filter_conditions)) {
                                // Add WHERE keyword and concatenate filter conditions
                                $sql .= " AND " . implode(" AND ", $filter_conditions);
                            } else {
                                $sql .= "";
                            }
                        }
                        
                    }
                    // echo $sql;
                    $result = $conn->query($sql);
                    // Check for errors in query execution
                    if (!$result) {
                        echo $conn->error;
                    }
                    $row = $result->fetch_assoc();
                    $totalRecords = $row["total"]; //alias
                    // echo $totalRecords;
                                       
                    $totalPages = ceil($totalRecords / $recordsPerPage);

                    echo "<ul class='pagination justify-content-center'>";

                    if ($totalPages > 1) {
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $currentPage) {
                                echo "<li class='page-item active' aria-current='page'>
                                    <span class='page-link'>$i</span>
                                </li>";
                            } else {
                                //sorting and searching
                                if (isset($_GET['order']) && isset($_GET['column']) && isset($_GET['search'])) {
                                    echo "<li class='page-item'>
                                    <a class='page-link' href='?type=" . htmlspecialchars($_GET['type']) . "&page=$i&column=" . htmlspecialchars($_GET['column']) . "&order=" . htmlspecialchars($_GET['order']) . "&search=" . urlencode(htmlspecialchars($_GET['search'])) . "'>$i</a> 
                                    </li>";
                                    
                                } else if (isset($_GET['order']) && isset($_GET['column'])) { //only sorting
                                    echo "<li class='page-item'><a class='page-link' href='?type=" . htmlspecialchars($_GET['type']) . "&page=$i&column=" . htmlspecialchars($_GET['column']) . "&order=" . htmlspecialchars($_GET['order']) . "'>$i</a> </li>";
                                } else if (isset($_GET['search'])) { //only searching
                                    echo "<li class='page-item'><a class='page-link' href='?type=" . htmlspecialchars($_GET['type']) . "&page=$i&search=" . htmlspecialchars($_GET['search']) . "'>$i</a> </li>";
                                } else if (isset($_GET['filter'])) { //only filter
                                    echo "<li class='page-item'><a class='page-link' href='?type=" . htmlspecialchars($_GET['type']) . "&page=$i&filter=on";
                                    // Append existing parameters from the URL
                                    foreach ($_GET as $key => $value) {
                                        if ($key !== 'type' && $key !== 'page' && $key !== 'filter') {
                                            echo "&". htmlspecialchars($key) ."=". htmlspecialchars($value);
                                        }
                                    }
                                    echo "'>$i</a></li>"; //closing of href and anchor tag
                                } else { //nothing
                                    echo "<li class='page-item'><a class='page-link' href='?type=" . htmlspecialchars($_GET['type']) . "&page=$i'>$i</a></li>";
                                }
                            }
                        }
                    }
                    // echo "</div>";
                    echo "</ul>";
                    ?>
                </div>
            </div>
        </div>

        <!------------------------------Pagination Code End---------------------------------------->

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
    </div>
    

    <!------------------------------Logout Modal Code Start---------------------------------------->

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    If you want to Logout then click on "Logout" button.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form method="post" action="">
                        <input type="submit" class="btn btn-primary" name="logout" value="Logout">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------Logout Modal Code End---------------------------------------->

    <!------------------------------Delete Modal Code Start---------------------------------------->

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    If you want to Delete the Project then click on "Delete" button.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    
                    <form method="post" action="">
                        <!-- csrf --> 
                        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFtoken('delete_modal_form')); ?>">
                        <input type="submit" class="btn btn-danger" id="deleteButton" name="delete_row" value="Delete">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------Delete Modal Code End---------------------------------------->

    <!------------------------------import Modal Code Start---------------------------------------->

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Import file</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    If you want to Import the Project then click on "Import" button.
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between">
                        <form method="POST" action="import.php" id="importForm" enctype="multipart/form-data">
                            <input type="file" name="import_file" id="file" class="form-control">
                            <br>
                            <input type="hidden" name="import-modal-btn" value="ok">
                            <input type="submit" class="btn btn-info" value="Upload" id="btn_upload">
                            <span id="import_msg"></span>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------import Modal Code End---------------------------------------->
                      

    <!-- old kunal's code of footer --> 
    <!-- <footer class="bg-dark text-white">
        <div class="container">
            <div class="row pt-4">
                <div class="col-md-4 pb-4">
                    <div class="text-left">
                        <img src="assets/logo-light.png" alt="Logo" class="img-fluid mb-3">
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
    </footer> -->


    
</body>

</html>