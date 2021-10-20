// Wait for the DOM to be ready
$(document).ready(function() {
    // Initialize form validation on the registration form.
    // It has the name attribute "registration"
    $("form[name='registration']").validate({
      // Specify validation rules
      rules: {
        // The key name on the left side is the name attribute
        // of an input field. Validation rules are defined
        // on the right side
        numero_identificacion: {
            required: true,
            regex: "^[0-9]{8,8}[A-Za-z]$",
            minlength: 9
        },
        nombre: "required",
        primer_apellido: "required",
        segundo_apellido: "required",
        telefono_movil: {
          required: true,
          min: 600000000,
          max: 799999999
        },
        email: {
          required: true,
          email: true
        },
      },
      // Specify validation error messages
      messages: {
        numero_identificacion: "Obligatorio",
        nombre: "Introduce tu nombre",
        primer_apellido: "Introduce tu apellido",
        segundo_apellido: "Introduce tu apellido",
        password: {
          required: "Introduzca una contraseña",
          minlength: "La contraseña debe tener, al menos, 8 caracteres"
        },
        telefono_movil: {
          required: "El teléfono móvil es obligatorio",
          min: "El número de teléfono introducido es inferior al rango esperado",
          max: "El número de teléfono móvil introducido está por encima del rango esperado"
        }
        email: "El correo debe tener el formato: nombre@dominio.algo",
      },
      // Make sure the form is submitted to the destination defined
      // in the "action" attribute of the form when valid
      submitHandler: function(form) {
        form.submit();
      }
    });
  });
  