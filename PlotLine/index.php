<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css\startpage.css?v=1.1">
    <link rel="stylesheet" href="css\bootstap\bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css\formstyle.css?v=1.1">
    <title>Document</title>
</head>
<body>
  <?php include 'components\form.php'; ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
  <div class="container-fluid px-4">

    <!-- LOGO -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <div class="logo-icon me-2">
        <i class="bi bi-feather"></i>
      </div>
      <span class="brand-text">Novela</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
      </ul>

      <div class="d-flex align-items-center right-nav-section">
        <div class="container">
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btnLogin-popup nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    </li>
                    <li class="nav-item">
                        <button class="btnSign-popup nav-link" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                    </li>
                </ul>
            </div>
        </div>
      
      </div>
    </div>
  </div>
</nav>

<!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 left">
                    <h1>Where stories bloom and readers gather.</h1>
                    <h2>Dive into a vast ocean of novels written by people just like you. Experience reading in a calm, tea-colored ambiance designed for focus and imagination.</h2>
                   <button class="btnStart-popup">Get Started</button>
                </div>
                <div class="col-lg-6 right text-center">
                    <img src="Photos/Character_float.svg" alt="Character" class="character" onerror="this.src='https://via.placeholder.com/400x400?text=Character'">
                </div>
            </div>
        </div>
    </div>

<script src="js/bootstrap_js/bootstrap.bundle.min.js"></script>
<script src="js/form.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>