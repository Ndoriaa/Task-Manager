$(document).ready(function() {
  $("#loginForm").on("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    $.ajax({
      url: form.action, // /TaskManager/loginUser
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        "X-Requested-With": "XMLHttpRequest"
      },
      dataType: "json",
      success: function(data) {
        console.log(data); // For debugging

        if (data.success) {
          localStorage.setItem("token", data.token);

          Swal.fire({
            icon: "success",
            title: "Login Successful!",
            text: "Welcome back!",
            confirmButtonColor:"#001f3f " ,
            confirmButtonText: "Continue"
          }).then(() => {
            window.location.href = "admin.php?action=dashboard";
          });

        } else {
          Swal.fire({
            icon: "error",
            title: "Login Failed",
            text: data.message || "Incorrect username or password.",
            confirmButtonColor: " #ffc300"
          });
        }
      },
      error: function(xhr, status, error) {
        console.log(xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Network Error",
          text: "Please check your connection or server route.",
          confirmButtonColor: "#fedd59"
        });
      }
    });
  });
});
