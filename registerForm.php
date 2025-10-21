<?php include "templates/include/header.php" ?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
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
          <h2 class="card-title text-center mb-4">Register</h2>

          <div id="registerStatus" class="text-danger mb-3"></div>

          <form id="registerForm" method="post" action="/TaskManager/registerUser">
            <div class="mb-3">
              <input type="text" name="firstName" class="form-control" placeholder="First Name" required>
            </div>
            <div class="mb-3">
              <input type="text" name="secondName" class="form-control" placeholder="Second Name" required>
            </div>
            <div class="mb-3">
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="mb-3">
              <input type="text" name="department" class="form-control" placeholder="Department" required>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="isAdmin">
              <label class="form-check-label" for="isAdmin">
                Make this user an admin
              </label>
            </div>

            <input type="hidden" name="register" value="Register">
            <button type="submit" class="btn btn-primary w-100">Register</button>
          </form>

          <p class="mt-3 text-center">
            <a href="admin.php?action=loginUser">Already have an account? Login</a>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="templates/include/script.js"></script>

</body>
</html>
<?php include "templates/include/footer.php" ?>
