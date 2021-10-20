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

  $.validator.addMethod("custom_regexnombreapellidocalle", function(value, element){
    return value.match(regexNombreYApellido);
  });
    // It has the name attribute "registration"
    $("form[name='registration']").validate({
      // Specify validation rules
      rules: {
        tipo_documento: "required",
        numero_identificacion: {
            required: true,
            custom_regexidentificacion: true
        },
        nombre:{
          required: true,
          custom_regexnombreapellidocalle: true
        },
        primer_apellido:{
          required: true,
          custom_regexnombreapellidocalle: true
        },
        segundo_apellido:{
          required: true,
          custom_regexnombreapellidocalle: true
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
        nombre_de_via:{
          required: true,
          custom_regexnombreapellidocalle: true
        },
        numero_de_via:{
          required: true,
          number: true
        },
        complemento:{
          required: true,
          custom_regexnombreapellidocalle: true
        },
        fecha:{
          required: true,
        },

      },
      
      // Specify validation error messages
      messages: {
        tipo_documento: "Elija el tipo de documento",
        numero_identificacion: {
          required: "El DNI del alumno es obligatorio",
          custom_regexidentificacion: "El DNI/NIE introducido es incorrecto"
        },
        nombre: {
          required:"Introduce el nombre del alumno",
          custom_regexnombreapellidocalle: "El nombre introducido solo puede tener letras y espacios"
        },
        primer_apellido: {
          required:"Introduce el primer apellido del alumno",
          custom_regexnombreapellidocalle: "El apellido introducido solo puede tener letras y espacios"
        },
        segundo_apellido: {
          required:"Introduce el segundo apellido del alumno",
          custom_regexnombreapellidocalle: "El apellido introducido solo puede tener letras y espacios"
        },
        telefono_movil: {
          required: "El teléfono móvil es obligatorio",
          //TODO modificar teléfono para hacer regex
          min: "El número de teléfono introducido es inferior al rango esperado",
          max: "El número de teléfono móvil introducido está por encima del rango esperado",
          digits: "El valor introducido no es un número."
        },
        email:{
          required: "El correo electrónico es obligatorio",
          email: "El correo electrónico introducido es inválido"
        },
        nombre_de_via:{
          required: "La dirección del domicilio es obligatoria",
          custom_regexnombreapellidocalle: "La dirección escrita no es válida"
        },
        numero_de_via:{
          required: "El número de la dirección es obligatoria",
          number: "Este campo solo acepta números"
        },
        complemento:{
          required: "Es necesario escribir el complemento",
          custom_regexnombreapellidocalle: "Solo se pueden escribir espacios y letras"
        },
        fecha:{
          required: "La fecha de nacimiento es obligatoria",
        }
      },
      // Make sure the form is submitted to the destination defined
      // in the "action" attribute of the form when valid
      submitHandler: function(form) {
        form.submit();
      }
    });
  });
  