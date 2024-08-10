document.addEventListener("DOMContentLoaded", function() {
    // Obtener todos los campos de entrada del formulario
    var formInputs = document.querySelectorAll('input[type="email"], input[type="text"], input[type="password"]');

    // Desactivar el autocompletado para cada campo de entrada
    formInputs.forEach(function(input) {
        input.setAttribute("autocomplete", "off");
    });

    // Prevenir que el formulario se autocomplete al hacer submit
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.setAttribute("autocomplete", "off");
    });
});
