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
    // Seleccionamos el form con el nombre "registration"
    $("form[name='registration']").validate({
      // Especificamos las reglas de validación
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
        en_calidad_de: "required",
        telefono_movil: {
          required: true,
          custom_regexmovil: true
        },
        telefono_fijo: {
          custom_regexfijo: true
        },
        email: {
          required: true,
          email: true
        },
        tipo_de_via: "required",
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
        pais: "required",
        provincia: "required",
        isla: "required",
        municipio: "required",
        codigo_postal: "required",
        ciencias: "required",
        bloque_ii: "required",
        bloque_iii: "required",
        bloque_iv: "required",
        bloque_v: "required",
        archivo_dni: "required",
        certificado_academico: "required"

      },
      
      // Mensajes que se escriben en caso de error
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
        en_calidad_de: "Es necesario elegir quién es el representante",
        telefono_movil: {
          required: "El teléfono móvil es obligatorio",
          custom_regexmovil: "El teléfono móvil introducido no es válido",
        },
        telefono_fijo: "El teléfono fijo introducido no es válido",
        email:{
          required: "El correo electrónico es obligatorio",
          email: "El correo electrónico introducido es inválido"
        },
        tipo_de_via: "Es necesario elegir el tipo de vía",
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
        fecha: "La fecha de nacimiento es obligatoria",
        pais: "Elija el país",
        provincia: "Elija la provincia",
        isla: "Elija la isla",
        municipio: "Elija el municipio",
        codigo_postal: "Introduzca el código postal",
        ciencias: "Elija el itinerario a estudiar",
        bloque_ii: "Elija la segunda lengua a estudiar",
        bloque_iii: "Elija asignatura troncal",
        bloque_iv: "Elija asignatura optativa",
        bloque_v: "Elija asignatura opcional",
        archivo_dni: "El DNI del alumno es obligatorio",
        certificado_academico: "El certificado académico del alumno es obligatorio",
      },
      // Make sure the form is submitted to the destination defined
      // in the "action" attribute of the form when valid
      submitHandler: function(form) {
        form.submit();
      }
    });
  });
  