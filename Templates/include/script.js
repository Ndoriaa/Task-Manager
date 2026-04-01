$(document).ready(function() {
  $("#registerForm").on("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    $.ajax({
      url: form.action,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      },
      dataType: "json",
      success: function(data) {
        if (data.success) {
          alert("Registration successful!");
          window.location.href = "admin.php?action=loginUser";
        } else {
          alert("Registration failed: " + data.message);
        }
      },
      error: function(xhr, status, error) {
        alert("Network error: " + error);
      }
    });
  });
});
