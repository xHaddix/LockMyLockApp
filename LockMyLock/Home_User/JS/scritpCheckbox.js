document.addEventListener("DOMContentLoaded", function() {
    let checkbox = document.getElementById("checkbox");
    let passwordInput = document.getElementById("contrasena");

    checkbox.addEventListener("change", function() {
        if (this.checked) {
            passwordInput.disabled = false;
        } else {
            passwordInput.disabled = true;
        }
    });

    let checkboxPIN = document.getElementById("checkboxPIN");
    let pinInput = document.getElementById("pin");

    checkboxPIN.addEventListener("change", function() {
        if (this.checked) {
            pinInput.disabled = false;
        } else {
            pinInput.disabled = true;
        }
    });
});
