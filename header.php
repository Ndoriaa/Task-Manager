<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo htmlspecialchars($results['pageTitle'] ?? "Task Manager"); ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Optional: Your custom CSS -->
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<!-- Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="admin.php">
      <img src="images/download.png" alt="Task Manager Logo" width="40" height="40" class="d-inline-block align-text-top">
      Task Manager
    </a>
  </div>
</nav>
