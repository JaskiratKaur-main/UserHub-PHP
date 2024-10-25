<?php

session_start();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oriental Outsourcing-Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
    
     .card-footer p{
        font-size: 12px;
     }
     .copy {
        font-size:12px !important;
     }

     .services{
        background-color: rgb(234, 244, 246);
        text-align: center;
     }
     @media screen and (max-width:767px) {
        .slide img{
            max-height: 100%;
        }

        .next
        {
            padding-top: 0;
        }
        .carousel-caption 
        {
            padding-bottom: 5px!important;
        }
     }
     
     
</style>

<body>
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
                                <a class="nav-link" aria-current="page" href="home.php">Home</a>
                            </li>
                            <?php
                                if(isset($_SESSION['userId']))
                                {
                            ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false">Projects
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown1">
                                            <li><a class="dropdown-item" href="projects.php?type=1">All Projects</a></li>
                                            <li><a class="dropdown-item" href="projects.php?type=2">My Projects</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="projects.php?type=3">Projects Matching My Skills</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php
                                                    
                                            if($_SESSION['user_profile_image'] == 'default_user.jpg')
                                            {
                                        ?>
                                                <img class="img-profile rounded-circle" src="images/default_user.jpg" height="30" width="30">
                                                
                                        <?php
                                            }
                                            else
                                            {
                                        ?>
                                                <img class="img-profile rounded-circle" src="uploads/<?php echo htmlspecialchars($_SESSION['user_profile_image']);?>"  height="30" width="30">
                                        <?php
                                            }
                                        ?>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown2">
                                            <li><a class="dropdown-item" href="profile.php">Profile Settings</a></li>
                                            <li><a class="dropdown-item" href="password_settings.php">Password Settings</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="delete_account.php">Delete account</a></li>
                                            
                                        </ul>
                                    </li>
                                    <!-- logout -->
                                     <a href="logout.php" class="nav-link">Logout</a>
                                <?php
                                }
                                else
                                {
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


        <div id="myCarousel" class="carousel slide " data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active slide">
                    <img class="bd-placeholder-img" width="100%" height="100%"
                        src="uploads/emily-bernal-v9vII5gV8Lw-unsplash.jpg" aria-hidden="true"
                        preserveAspectRatio="xMidYMid slice" focusable="false">

                    <div class="container">
                        <div class="carousel-caption pb-5">
                            <h1>Software Development</h1>
                            <p>A Reliable Partner for Startups, Small Businesses, and You.</p>
                            <p><a class="btn  btn-primary rounded-pill px-3" href="#">GET IN TOUCH</a></p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img class="bd-placeholder-img" width="100%" height="100%"
                        src="uploads/mario-gogh-VBLHICVh-lI-unsplash.jpg" preserveAspectRatio="xMidYMid slice"
                        focusable="false">
                    

                    <div class="container">
                        <div class="carousel-caption pb-5">
                            <h1>Best Team.</h1>
                            <p>Some of the most telented developers.</p>
                            <p><a class="btn  btn-primary rounded-5 px-3" href="#">LEARN MORE</a></p>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img class="bd-placeholder-img" width="100%" height="100%"
                        src="uploads/mohammad-rahmani-_Fx34KeqIEw-unsplash (1).jpg" aria-hidden="true"
                        preserveAspectRatio="xMidYMid slice" focusable="false">
                    

                    <div class="container">
                        <div class="carousel-caption pb-5">
                            <h1>One Moto.</h1>
                            <p>Our Business Philosophy Is “You Dream It: We Create It.”</p>
                            <p><a class="btn btn-primary rounded-5 px-3" href="#">PROJECTS</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <div class="container my-5">
            <div class="row featurette">
                <div class="col-md-6 sm-12 order-md-1 text-center">
                    <img class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500" height="500" src="uploads/scott-graham-5fNmWej4tAA-unsplash.jpg" role="img" aria-label="Placeholder: 500x500" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#eee"/>
                    <!-- <text x="50%" y="50%" fill="#aaa" dy=".3em">500x500</text> -->
                </div>
                <div class="col-md-6 sm-12 order-md-2 d-flex justify-content-center align-items-center">
                    <div >
                  <h5 class="text-primary pt-3">VISION</h5>
                  <h2 class="featurette-heading">Become best in the business.</h2>
                  <p class="lead">Our vision shapes our business strategy each day, as we strive to be a leading software development company, known for delivering excellence and innovation to our clients worldwide. We are committed to providing the world’s best software development services, while prioritizing the well-being of our team members and upholding our core values.</p>
                  <p><a class="btn btn-outline-primary" href="#">Read More  <i class="fa-solid fa-arrow-up fa-rotate-by" style="--fa-rotate-angle: 60deg;"></i></a></p>
                </div>
                </div>
                
              </div>

        </div>
        <div class="services " >
        <div class="container py-4 ">
            
                <h2 class="mt-3">Our Services</h2>
                <div class="container mt-5 mb-3 services">
                    <div class="row mt-3">
                        <div class="col-lg-3 col-md-6 col-12 justify-content-center px-4">
                            <i class="fas fa-solid fa-laptop-code fa-2xl"></i>
                            <h6 class="mt-3">Web Development</h6>
                            <p>Transform your website with our state-of-the-art web development services.</p>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 px-4">
                            <i class="fas fa-solid fa-chart-line fa-2xl"></i>
                            <h6 class="mt-3">Task Analytics</h6>
                            <p>Use tools to analyse data and infer value form it</p>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 px-4">
                            <i class="fas fa-solid fa-gears fa-2xl"></i>
                            <h6 class="mt-3">Maintainance</h6>
                            <p>To make sure that your website is updated at regular intervals and that all security threats are eliminated</p>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 px-4">
                            <i class="fas fa-solid fa-list-check fa-2xl"></i>
                            <h6 class="mt-3">QA and Testing</h6>
                            <p>Automated software testing, use our testing services to ensure the efficient functioning of your software services.</p>
                        </div>
                    </div>
                    <div class="row mt-3 ">
                        <div class="next col-lg-3 col-md-6 col-12 px-4 ">
                            <i class="fas fa-solid fa-mobile-alt fa-2xl"></i>
                            <h6 class="mt-3">Mobile App Development</h6>
                            <p>Meet your demanding timeframes with our mobile app development services that have cutting-edge technology.</p>
                        </div> 
                        <div class="col-lg-3 col-md-6 col-12 px-4">
                            <i class="fas fa-solid fa-store fa-2xl"></i>
                            <h6 class="mt-3">E-Commerce Development</h6>
                            <p>Boost the performance and efficiency of your store with our eCommerce services & solutions.</p>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 px-4 ">
                            <i class="fas fa-solid fa-bezier-curve fa-2xl"></i>
                            <h6 class="mt-3">UI/UX Design</h6>
                            <p>Develop the products you've visualized with the help of our experienced designers.</p>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 px-4 ">
                            <i class="fas fa-solid fa-bullhorn fa-2xl"></i>
                            <h6 class="mt-3">Digital Marketing</h6>
                            <p>Craft a large customer flow with our digital marketing services. Our experts excel in SEO, PPC, SMO to boost website traffic.</p>
                        </div>
                    </div>
                </div>
            
        </div>
    </div>

        <div class="container my-5">
            <h2 class="my-3 text-center">
                Testimonials
            </h2>
            <div class="container my-3">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-quote-left"></i></h5>
                                <p class="card-text">One common experience that we had with all of them is that they are permanently with us now.</p>
                                <div class="rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-body-secondary font-weight-bold"><b>Kewin</b></small>
                                <p class="mb-0">Director @ FoodPanda</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-quote-left"></i></h5>
                                <p class="card-text">Oriental Outsourcing is able to give our clients a more tailored approach and services as per requirement specific to each sector and industry’s needs. </p>
                                <div class="rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-body-secondary font-weight-bolder"><b>John Doe</b></small>
                                <p class="mb-0 ">Founder at XYZ Co.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-quote-left"></i></h5>
                                <p class="card-text">Oriental Outsourcing Is fantastic in development…they does exactly as they says and more fantastic work recommend i will not be using any others from now on</p>
                                <div class="rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-body-secondary font-weight-bold"><b>Andrew J.</b></small>
                                <p class="mb-0" >Director @ FoodPanda</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
</body>

</html>