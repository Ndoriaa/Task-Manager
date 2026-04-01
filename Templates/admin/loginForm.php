<?php include "templates/include/header.php" ?>
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #001f3f; /* Navy blue */
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">

      <div class="card shadow-lg">
        <div class="card-body">
          <h2 class="card-title text-center mb-4">Login</h2>

          <div id="loginStatus" class="text-danger mb-3"></div>

          <form id="loginForm" method="post" action="/TaskManager/loginUser">
            <div class="mb-3">
              <input type="text" name="firstName" class="form-control" placeholder="First Name" required>
            </div>
            <div class="mb-3">
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
          </form>

          <p class="mt-3 text-center">
            <a href="admin.php?action=registerUser">Don't have an account? Register</a>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="templates/include/login.js"></script>

</body>
</html>
<?php include "templates/include/footer.php" ?>
