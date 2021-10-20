// Wait for the DOM to be ready
$(function() {
    // Initialize form validation on the registration form.
    // It has the name attribute "registration"
    $("form[name='registration']").validate({
      // Specify validation rules
      rules: {
        // The key name on the left side is the name attribute
        // of an input field. Validation rules are defined
        // on the right side
        numero_identificacion:{
            required: true,
            regex: "^[0-9]{8,8}[A-Za-z]$",
            length: 9
        },
        nombre: "required",
        email: {
          required: true,
          // Specify that email should be validated
          // by the built-in "email" rule
          email: true
        },
      },
      // Specify validation error messages
      messages: {
        nombre: "Introduce tu nombre",
        lastname: "Introduce tu apellido",
        password: {
          required: "Introduzca una contraseña",
          minlength: "La contraseña debe tener, al menos, 8 caracteres"
        },
        email: "Introduzca un correo válido."
      },
      // Make sure the form is submitted to the destination defined
      // in the "action" attribute of the form when valid
      submitHandler: function(form) {
        form.submit();
      }
    });
  });
  