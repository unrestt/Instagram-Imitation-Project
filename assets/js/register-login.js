var PasswordVisionChange = document.getElementById("password_view_change");
var passwordInput = document.getElementById("password_input");

PasswordVisionChange.addEventListener("click", () => {
  if (passwordInput.type === "password") {
    passwordInput.type = "text"; 
    PasswordVisionChange.classList.remove("fa-eye-slash");
    PasswordVisionChange.classList.add("fa-eye");
  } else {
    passwordInput.type = "password";
    PasswordVisionChange.classList.remove("fa-eye"); 
    PasswordVisionChange.classList.add("fa-eye-slash");
  }
});