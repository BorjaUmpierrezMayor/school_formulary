// Wait for the DOM to be ready
$(document).ready(function() {
    
  let regexNumeroIdentificacion = /((([X-Z])|([LM])){1}([-]?)((\d){7})([-]?)([A-Z]{1}))|((\d{8})([-]?)([A-Z]))/;
  let regexNumeroMovil = /(6|7)[0-9]{8}/;
  let regexNumeroFijo = /(8|9)[0-9]{8}/;;
  let regexNombreYApellido = /^[a-zA-Z ]{1,30}$/;

  $.validator.addMethod("custom_regexidentificacion", function(value, element){
    return value.match(regexNumeroIdentificacion);
  });

  $.validator.addMethod("custom_regexmovil", function(value, element){
    return value.match(regexNumeroMovil);
  });

  $.validator.addMethod("custom_regexfijo", function(value, element){
    return value.match(regexNumeroFijo);
  });

  $.validator.addMethod("custom_regexnombreapellido", function(value, element){
    return value.match(regexNombreYApellido);
  });
    // It has the name attribute "registration"
    $("form[name='registration']").validate({
      // Specify validation rules
      rules: {
        numero_identificacion: {
            required: true,
            custom_regexidentificacion: true
        },
        nombre:{
          required: true,
          custom_regexnombreapellido: true
        },
        primer_apellido:{
          required: true,
          custom_regexnombreapellido: true
        },
        segundo_apellido:{
          required: true,
          custom_regexnombreapellido: true
        },
        telefono_movil: {
          required: true,
          digits: true,
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
        numero_identificacion: {
          required: "Obligatorio",
          custom_regexidentificacion: "DNI/NIE incorrecto"
        },
        nombre: {
          required:"Introduce tu nombre",
          custom_regexnombreapellido: "El nombre introducido solo puede tener letras y espacios"
        },
        primer_apellido: {
          required:"Introduce tu primer apellido",
          custom_regexnombreapellido: "El apellido introducido solo puede tener letras y espacios"
        },
        segundo_apellido: {
          required:"Introduce tu segundo apellido",
          custom_regexnombreapellido: "El apellido introducido solo puede tener letras y espacios"
        },
        password: {
          required: "Introduzca una contraseña",
          minlength: "La contraseña debe tener, al menos, 8 caracteres"
        },
        telefono_movil: {
          required: "El teléfono móvil es obligatorio",
          min: "El número de teléfono introducido es inferior al rango esperado",
          max: "El número de teléfono móvil introducido está por encima del rango esperado",
          digits: "El valor introducido no es un número válido."
        },
        email: "El correo debe tener el formato: nombre@dominio.algo",
      },
      // Make sure the form is submitted to the destination defined
      // in the "action" attribute of the form when valid
      submitHandler: function(form) {
        form.submit();
      }
    });
  });
  