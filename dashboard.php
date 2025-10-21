<?php 
require("config.php");

session_start();

if (!isset($username)) {
  header("Location: admin.php?action=loginUser");
  exit;
}

include "Templates/include/header.php";
?>


  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>You are logged in. This is your dashboard.</p>
  <a href="admin.php?action=logoutUser">Logout</a>

<?php include "Templates/include/footer.php" ?>