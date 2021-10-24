<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de servicios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
        // Retorna false(0) si hay errror o el DNI validado y con letra si no hay error.
        function validate_NIF($dni) {
            $str = trim($dni);  
            $str = str_replace("-","",$str);  
            $str = str_ireplace(" ","",$str);  
            $n = substr($str,0,strlen($str)-1);  
            $n = intval($n);

            $l = substr($str,-1);

            // Si no tiene 9 dígitos, $n no es un número l $l no es una string.
            if (strlen($str) !== 9 || !is_int($n) || !is_string($l)) {
                return false;
            }

            $letra = substr ("TRWAGMYFPDXBNJZSQVHLCKE", $n%23, 1);

            if (strtolower($l) == strtolower($letra)) {  
                return $n.$l;  
            } else {  
                return 0; 
            }
        }

        function validate_NIE($nie) {
            $str = trim($nie);
            $str = str_replace("-","",$str);  
            $str = str_ireplace(" ","",$str);
            $n = substr($str,1,strlen($str)-2);
            $n = intval($n);

            $li = strtolower(substr($nie,0,1));
            $lf = strtolower(substr($nie,8,9));

            // Si no tiene 9 dígitos o $n no es un número.
            if(strlen($str) !== 9 || !is_int($n)) {
                return false;
            }

            // Si la primera letra no es X o T, y la última cifra es un número.
            if(($li != "x" && $li != "t") || is_numeric($lf)) {
                return false;
            }

            return true;
        }

        function validate_NIF_NIE($documento, $dni) {
            if($documento == "NIF") {
                return validate_NIF($dni);
            } else if ($documento == "NIE") {
                return validate_NIE($dni);
            } 
            return false;
        }

        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        function validarTelefonoFijo($number) {
            if(is_numeric($number) && strlen($number) == 9 && (substr($number,0,1) == "9" || substr($number,0,1) == "8")) {
                return true;
            }
            return false;
        }

        function validarTelefonoMovil($number) {
            if(is_numeric($number) && strlen($number) == 9 && (substr($number,0,1) == "7" || substr($number,0,1) == "6")) {
                return true;
            }
            return false;
        }

        function validarEMail($email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
            return false;
        }

        function validarString($string) {
            $string = test_input($string);
            if(is_string($string) && strlen($string) > 3) {
                return true;
            }
            return false;
        }

        /*
        * Validamos los ficheros de la siguiente forma:
        *      1. Nos seguramos de que el archivo esté declarado isset($file);
        *      2. Si la extensión no está incluida en $valid_formats es erróneo.
        *      3. Si el archivo es mayor a 10485760B o 10MB hay un error.
        *      4. Si hay carácteres especiales: ! " # $ & ' * + , . / ; < - > ? @ [ ] ( ) ^ ' { | }. dará error.
        *      5. Para asegurarnos de que se vean todos los mensajes, el $error
        *         nos permitirá retornar falso una vez termine de recorrer todas las posibilidades.
        */
        function validateFile($file,$nombre) {
            $valid_formats = Array ('jpg', 'png', 'doc', 'docx', 'txt', 'pdf', 'odt');
            $error = false;

            if(isset($file)) {
                $name       = $file['name'];  
                $size       = $file['size'];

                $extension = pathinfo($name, PATHINFO_EXTENSION);
                if (!in_array($extension, $valid_formats)) {
                    echo "El archivo ".$nombre." tiene una extensión errónea: ".$extension.".<br />";
                    $error = true;
                }
                if($size > 10485760) {
                    echo "El archivo ".$nombre." tiene un tamaño superior a 10MB.<br />";
                    $error = true;
                }
                if (preg_match('/[\'^£$%&*()}{#~?><>,|+¬-]/', $name)) {
                    echo "El archivo ".$nombre." no puede tener carácteres especiales: ! \" # $ & ' * + , . / ; < - > ? @ [ ] ( ) ^ '\ { | } .<br />";
                    $error = true;
                }
                if($error) {
                    return false;
                }
                return true;
            }
            return false;
        }

        function generarSelect($datos, $name, $id, $field){
            $text = '<select name="'.$name.' id="'.$name.'" class="form-select" aria-label="Seleccionar '.$name.'">';
            $text .= '<option value="">Seleccione una opción.</option>';
            foreach ($datos as $dato) {
                $text .= '<option value="'.$dato[$id].'">'.$dato[$field].'</option>';
            }
            $text .= '</select>';
            return $text;
        }
    ?>
    <div class="container">
        <?php
        /*
        * Debido a la validación del FRONT END los mensajes serán generados a partir de Javascript,
        * por lo cual, la función de esta validación será impedir que información malformada entre
        * en el servidor. Por ello crearemos una estructura de IFs donde, salvo que todos los casos
        * sean correctos no permitirá que el documento se envíe.
        */

            $valid_form = true;

            if(isset($_POST['cancel'])) {
                header("Location: index.php");
            }

            if(isset($_POST['submit'])) {
                /*
                * Si es verdad que cualquiea de estas condiciones se cumple, no será enviado.
                * Si deseáramos mostrar un mensaje diferente por cada error, podríamos generar
                * una variable por cada mensaje de error, por ejemplo:
                * 
                *      $error_representante = '<p class="error">Por favor, seleccione uno.</p>';
                * 
                * Y mostrarlo mediante un echo. Estaría vacío si no hay error, y en caso de haberlo
                * se mostraría por pantalla.
                */

                //  *********** DATOS ACTÚA COMO REPRESENTANTE
                // Representante:
                if((!isset($_POST['representante']))) {
                    echo "No ha seleccionado un representante."."<br/>";
                    $valid_form = false;
                }
                
                //  *********** DATOS DEL REPRESENTANTE
                // NO existe tipo de documento o ha sido seleccionado un tipo incorrecto.
                if (!isset($_POST['tipo_documento']) || (test_input($_POST['tipo_documento']) !== "NIF" && test_input($_POST['tipo_documento'] !== "NIE"))) {
                    echo "Seleccione un tipo de documento."."<br/>";
                    $valid_form = false;
                }
                
                // NO existe número de identificación.
                if (!isset($_POST['numero_identificacion'])) {
                    echo "No ha elegido un número de identificación válido."."<br/>";
                    $valid_form = false;
                }
                
                // El número NO está bien formado o está vacío:
                if (!validate_NIF_NIE(
                        test_input($_POST['tipo_documento']),
                        test_input($_POST['numero_identificacion']))
                    ) {
                    echo "El NIF o NIE no está bien definido o está vacío."."<br/>";
                    $valid_form = false;
                }

                // Nombre está vacío o no está en formato.
                if (isset($_POST['nombre']) && !validarString($_POST['nombre']) ) {
                    echo "Nombre vacío."."<br/>";
                    $valid_form = false;
                }

                // Primer apellido está vacío o no está en formato.
                if (isset($_POST['primer_apellido']) && !validarString($_POST['primer_apellido']) ) {
                    echo "Primer apellido vacío."."<br/>";
                    $valid_form = false;
                }

                // Segundo apellido está vacío o no está en formato.
                if (isset($_POST['segundo_apellido']) && !validarString($_POST['segundo_apellido']) ) {
                    echo "Segundo apellido vacío."."<br/>";
                    $valid_form = false;
                }

                // En calidad de.
                if (isset($_POST['en_calidad_de']) && test_input($_POST['en_calidad_de']) == "") {
                    echo "En calidad de está vacío."."<br/>";
                    $valid_form = false;
                }

                // Teléfono fijo. NO ES OBLIGATORIO, pero conviene asegurarse de que está bien formado.
                if (isset($_POST['telefono_fijo'])) {
                    $telefono_fijo = test_input(preg_replace('/\s+/', '', $_POST['telefono_fijo']));

                    if(!validarTelefonoFijo($telefono_fijo)) {
                        echo "Por favor, introduzca un teléfono fijo válido."."<br/>";
                    }
                }

                // Teléfono móvil.
                if (isset($_POST['telefono_movil'])) {
                    $telefono_movil = test_input(preg_replace('/\s+/', '', $_POST['telefono_movil']));

                    if(!validarTelefonoMovil($telefono_movil)) {
                        echo "Por favor, introduzca un teléfono móvil válido."."<br/>";
                        $valid_form = false;
                    }
                }

                // Email.
                if (isset($_POST['email'])) {
                    if(!validarEMail(test_input($_POST['email']))) {
                        echo "Por favor, introduzca un correo válido."."<br/>";
                        $valid_form = false;
                    }
                }

                //  *********** DOMICILIO DE CONTACTO

                // Tipo de vía.
                if (isset($_POST['tipo_de_via']) && $_POST['tipo_de_via'] == "") {
                    echo "Por favor, seleccione un tipo de vía."."<br/>";
                    $valid_form = false;
                }

                // Nombre de la vía.
                if (isset($_POST['nombre_de_via']) && !validarString($_POST['nombre_de_via'])) {
                    echo "Por favor, introduzca el nombre de la vía."."<br/>";
                    $valid_form = false;
                }

                // Número de la vía.
                if (!isset($_POST['numero_de_via'])) {
                    echo "Por favor, introduzca el número de la vía."."<br/>";
                    $valid_form = false;
                }

                if (isset($_POST['numero_de_via'])) {
                    $numero = intval($_POST['numero_de_via']);

                    if(!is_int($numero) || $numero < 1 || strlen($numero) > 3) {
                        echo "Por favor, introduzca el número de la vía."."<br/>";
                        $valid_form = false;
                    }
                }

                /*
                 * En Bloque, escalera y otros datos similares nos encontramos con un problema, y es que
                 * no hay un estandar en el que podamos basarnos salvo que indiquemos al usuario específicamente
                 * qué queremos que escriba, por ejemplo: Piso --> PRIMERO o 1 o 1º.
                 * 
                 * Si no lo especificamos, no podemos validar ese dato, siendo responsable el usuario de
                 * rellenar correctamente estos datos.
                 */

                // Bloque, escalera, piso, portal, letra y puerta.
                if (isset($_POST['bloque']) && !test_input($_POST['bloque'])) {
                    echo "Por favor, introduzca el bloque correctamente."."<br/>";
                }
                
                if (isset($_POST['escalera']) && !test_input($_POST['escalera'])) {
                    echo "Por favor, introduzca la escalera correctamente."."<br/>";
                }
                
                if (isset($_POST['piso']) && !test_input($_POST['piso'])) {
                    echo "Por favor, introduzca el piso correctamente."."<br/>";
                }
                
                if (isset($_POST['portal']) && !test_input($_POST['portal'])) {
                    echo "Por favor, introduzca el portal correctamente."."<br/>";
                }
                
                if (isset($_POST['letra']) && !test_input($_POST['letra'])) {
                    echo "Por favor, introduzca la letra correctamente."."<br/>";
                }
                
                if (isset($_POST['puerta']) && !test_input($_POST['puerta'])) {
                    echo "Por favor, introduzca la puerta correctamente."."<br/>";
                }

                // Complemento.
                if (isset($_POST['complemento']) && !test_input($_POST['complemento'])) {
                    echo "Por favor, introduzca el complemento correctamente."."<br/>";
                    $valid_form = false;
                }

                // Fecha de nacimiento.
                if (isset($_POST['fecha'])) {
                    $hoy = date("Y-m-d");
                    $validacion_fecha = date("Y-m-d",strtotime($hoy."- 6 years"));
                    $nacimiento = date("Y-m-d", strtotime($_POST['fecha']));

                    // Consideramos una fecha válida si tiene 6 o más años de diferencia.
                    if($nacimiento > $validacion_fecha) {
                        echo "Por favor, introduzca la fecha de nacimiento correcta."."<br/>";
                        $valid_form = false;
                    }
                }

                // País, provincia, isla, municipio, localidad y código postal.
                if (isset($_POST['pais']) && $_POST['pais'] == "") {
                    echo "Por favor, seleccione un pais."."<br/>";
                    $valid_form = false;
                }
                
                if (isset($_POST['provincia']) && $_POST['provincia'] == "") {
                    echo "Por favor, seleccione una provincia."."<br/>";
                    $valid_form = false;
                }
                
                if (isset($_POST['isla']) && $_POST['isla'] == "") {
                    echo "Por favor, seleccione una isla."."<br/>";
                    $valid_form = false;
                }
                
                if (isset($_POST['municipio']) && $_POST['municipio'] == "") {
                    echo "Por favor, seleccione un municipio."."<br/>";
                    $valid_form = false;
                }
                
                if (isset($_POST['localidad']) && $_POST['localidad'] == "") {
                    echo "Por favor, seleccione una localidad."."<br/>";
                    $valid_form = false;
                }
                
                if (isset($_POST['codigo_postal']) && $_POST['codigo_postal'] == "") {
                    echo "Por favor, seleccione un código postal."."<br/>";
                    $valid_form = false;
                }

                //  *********** MÁS DATOS.
                // Son datos opcionales basados en un checkbox múltiple, solo podríamos validar
                // los valores introducidos no han sido modificados.
                
                if (isset($_POST['alumno_huerfano']) && $_POST['alumno_huerfano'] !== "El alumno es huérfano absoluto.") {
                    echo "Error. El valor de alumno_huerfano ha sido modificado.".'<br />';
                }
                
                if (isset($_POST['alumno_tutelado']) && $_POST['alumno_tutelado'] !== "El alumno se encuentra en régimen de tutela y guarda por la Administración.") {
                    echo "Error. El valor de alumno_tutelado ha sido modificado.".'<br />';
                }


                //  *********** ALERGIAS, PATOLOGÍAS O DIETAS ESPECIALES
                // Al ser un textarea simplemente comprobaremos que se ha escrito algo.
                if (isset($_POST['otras_alergias']) && $_POST['otras_alergias'] == "") {
                    echo "Por favor, termine de rellenar otras alergias."."<br/>";
                }

                //  *********** DATOS CADÉMICOS DEL ALUMNO o ALUMNA
                // Ciencias.
                if((!isset($_POST['ciencias']))) {
                    echo "No ha seleccionado una asignatura de ciencias."."<br/>";
                }
                
                // Bloque I.
                // Nos pide que seleccionemos 6 en orden, por lo que si no han sido
                // seleccionadas 6 daremos un error.

                // Bloque I.
                if(empty($_POST['bloque_i_list']) || (!empty($_POST['bloque_i_list']) && count($_POST['bloque_i_list']) < 6)) {
                    echo "Debe seleccionar las 6 asignaturas del Bloque I en orden de preferencia.";
                    $valid_form = false;
                }

                // Bloque II.
                if((!isset($_POST['bloque_ii']))) {
                    echo "No ha seleccionado ninguna opción en el BLOQUE_II."."<br/>";
                    $valid_form = false;
                }

                // Bloque III.
                if((!isset($_POST['bloque_iii']))) {
                    echo "No ha seleccionado ninguna opción en el BLOQUE_III."."<br/>";
                    $valid_form = false;
                }

                // Bloque IV.
                if((!isset($_POST['bloque_iv']))) {
                    echo "No ha seleccionado ninguna opción en el BLOQUE_IV."."<br/>";
                    $valid_form = false;
                }

                // Bloque IV.
                if((!isset($_POST['bloque_v']))) {
                    echo "No ha seleccionado ninguna opción en el BLOQUE_V."."<br/>";
                    $valid_form = false;
                }

                //  *********** MEDIOS DE DIFUSIÓN

                // Consentimiento firmado:
                if((!isset($_POST['consiente']))) {
                    echo "No ha seleccionado ninguna opción en el consiente."."<br/>";
                    $valid_form = false;
                }

                // Consentimiento Página web del centro docente:
                if((!isset($_POST['pagina_consiente']))) {
                    echo "No ha seleccionado ninguna opción en el pagina_consiente."."<br/>";
                    $valid_form = false;
                }

                // Consentimiento App de alumnos y familias:
                if((!isset($_POST['app_consiente']))) {
                    echo "No ha seleccionado ninguna opción en el app_consiente."."<br/>";
                    $valid_form = false;
                }

                // Consentimiento Facebook:
                if((!isset($_POST['facebook_consiente']))) {
                    echo "No ha seleccionado ninguna opción en el facebook_consiente."."<br/>";
                    $valid_form = false;
                }

                //  *********** DOCUMENTOS ADJUNTOS

                // DNI DEL ALUMNO:
                // El error == 0 nos indica que no han habido errores de subida.
                if(isset($_FILES['archivo_dni']) && $_FILES['archivo_dni']['error'] == 0) {
                    if(!validateFile($_FILES['archivo_dni'],"DNI")) {
                        $valid_form = false;
                    }
                }

                // CERTIFICADO:
                // El error == 0 nos indica que no han habido errores de subida.
                    if(isset($_FILES['certificado_academico']) && $_FILES['certificado_academico']['error'] == 0) {
                    if(!validateFile($_FILES['certificado_academico'],"CERTIFICADO ACADÉMICO")) {
                        $valid_form = false;
                    }
                }

                /*
                 * Para prevenir el envío de información incorrecta deberíamos emplear jquery para validar de cara al usuario,
                 * pero si queremos que no se envíe solo deberíamos descarmar la siguiente línea:
                 */
                if(!$valid_form) {
                //header("Location: index.php");
                }
            }
        ?>

        <form action="index.php" method="post" enctype="multipart/form-data" name="registration" id="#registration">
            <h1>Solicitud de servicios</h1>

            <p class="title mt-3">DATOS ACTÚA COMO REPRESENTANTE</p>

            <div class="content">
                <p class="text-justify">¿Actúa como representante?</p>

                <div class="form-check representante">
                    <input class="form-check-input" type="radio" value="Alumno/a" name="representante" id="alumno"
                        <?php if (isset($_POST['representante']) && $_POST['representante'] == 'Alumno/a'): ?>checked='checked'<?php endif; ?>
                    />
                    <label class="form-check-label" for="alumno">
                        Alumno/a
                    </label>
                </div>
                <div class="form-check representante">
                    <input class="form-check-input" type="radio" value="Representante" name="representante" id="tutor"
                        <?php if (isset($_POST['representante']) && $_POST['representante'] == 'Representante'): ?>checked='checked'<?php endif; ?>
                    />
                    <label class="form-check-label" for="tutor">
                        Representante
                    </label>
                </div>
                <br>
                <label for="representante" class="error" style="display:none;">* Please pick an option above</label>
            </div>

            
            <p class="title mt-3">DATOS DEL REPRESENTANTE</p>

            <div class="content">
                <div class="row mt-3">
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="tipo_documento" class="form-label">
                                Tipo de documento: (*)
                            </label>
                            <select name="tipo_documento" class="form-select" aria-label="Default select example">
                                <option value="">Seleccione una</option>
                                <option value="NIF" <?php if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'NIF'): ?>selected<?php endif; ?> >NIF</option>
                                <option value="NIE" <?php if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'NIE'): ?>selected<?php endif; ?> >NIE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="numero_identificacion" class="form-label" name="numero_identificacion">
                                Nº de identifiación: (*)
                            </label>
                            <input type="text" maxlength="9" class="form-control" name="numero_identificacion" id="numero_identificacion" placeholder="Ej: 12345678Z / Z1234567X"
                                <?php
                                    if (isset($_POST['numero_identificacion'])) {
                                        echo ' value="'.$_POST['numero_identificacion'].'"';
                                    }
                                ?>
                            >
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="nombre" class="form-label">
                                Nombre: (*)
                            </label>
                            <input type="text" class="form-control" name="nombre" id="nombre" 
                                <?php
                                    if (isset($_POST['nombre'])) {
                                        echo ' value="'.trim($_POST['nombre']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="primer_apellido" class="form-label">
                                Primer apellido: (*)
                            </label>
                            <input type="text" class="form-control" name="primer_apellido" id="primer_apellido" 
                                <?php
                                    if (isset($_POST['primer_apellido'])) {
                                        echo ' value="'.trim($_POST['primer_apellido']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="segundo_apellido" class="form-label">
                                Segundo apellido: (*)
                            </label>
                            <input type="text" class="form-control" name="segundo_apellido" id="segundo_apellido" 
                                <?php
                                    if (isset($_POST['segundo_apellido'])) {
                                        echo ' value="'.trim($_POST['segundo_apellido']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 col-lg-4">
                        <div class="form-group">
                            <label for="en_calidad_de" class="form-label">
                                En calidad de: (*)
                            </label>
                            <select name="en_calidad_de" class="form-select" aria-label="Default select example">
                                <option value="">Representante</option>
                                <option value="madre" <?php if (isset($_POST['en_calidad_de']) && $_POST['en_calidad_de'] == 'madre'): ?>selected<?php endif; ?> >Madre</option>
                                <option value="padre" <?php if (isset($_POST['en_calidad_de']) && $_POST['en_calidad_de'] == 'padre'): ?>selected<?php endif; ?> >Padre</option>
                                <option value="tutor_legal" <?php if (isset($_POST['en_calidad_de']) && $_POST['en_calidad_de'] == 'tutor_legal'): ?>selected<?php endif; ?> >Tutor legal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="form-group">
                            <label for="telefono_fijo" class="form-label">
                                Teléfono fijo:
                            </label>
                            <input type="text" maxlength="9" class="form-control" name="telefono_fijo" id="telefono_fijo" placeholder="920 000 000"
                                <?php
                                    if (isset($_POST['telefono_fijo'])) {
                                        echo ' value="'.trim($_POST['telefono_fijo']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="form-group">
                            <label for="telefono_movil" class="form-label">
                                Teléfono móvil: (*)
                            </label>
                            <input type="text"  maxlength="9" class="form-control" name="telefono_movil" id="telefono_movil" placeholder="612 345 678"
                                <?php
                                    if (isset($_POST['telefono_movil'])) {
                                        echo ' value="'.trim($_POST['telefono_movil']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Correo electrónico: (*)
                            </label>
                            <input type="text" class="form-control" name="email" id="email" placeholder="nombre@ejemplo.com" 
                                <?php
                                    if (isset($_POST['email'])) {
                                        echo ' value="'.trim($_POST['email']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                </div>
            </div>
        
        
            <p class="title mt-3">DOMICILIO DE CONTACTO</p>

            <div class="content">
                <div class="row mt-3">
                    <div class="col-12 col-lg-3">
                        <div class="form-group">
                            <label for="tipo_de_via" class="form-label">
                                Tipo de vía: (*)
                            </label>
                            <select name="tipo_de_via" class="form-select" aria-label="Default select example">
                                <option value="">Vía del domicilio</option>
                                <option value="calle" <?php if (isset($_POST['tipo_de_via']) && $_POST['tipo_de_via'] == 'calle'): ?>selected<?php endif; ?> >Calle</option>
                                <option value="avenida" <?php if (isset($_POST['tipo_de_via']) && $_POST['tipo_de_via'] == 'avenida'): ?>selected<?php endif; ?> >Avenida</option>
                                <option value="via" <?php if (isset($_POST['tipo_de_via']) && $_POST['tipo_de_via'] == 'via'): ?>selected<?php endif; ?> >Vía</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="form-group">
                            <label for="nombre_de_via" class="form-label">
                                Nombre de vía: (*)
                            </label>
                            <input type="text" class="form-control" name="nombre_de_via" id="nombre_de_via"
                                <?php
                                    if (isset($_POST['nombre_de_via'])) {
                                        echo ' value="'.trim($_POST['nombre_de_via']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label for="numero_de_via" class="form-label">
                                Número: (*)
                            </label>
                            <input type="number" class="form-control" name="numero_de_via" id="numero_de_via"
                                <?php
                                    if (isset($_POST['numero_de_via'])) {
                                        echo ' value="'.trim($_POST['numero_de_via']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="bloque" class="form-label">
                                Bloque:
                            </label>
                            <input type="text" class="form-control" name="bloque" id="bloque"
                                <?php
                                    if (isset($_POST['bloque'])) {
                                        echo ' value="'.trim($_POST['bloque']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="escalera" class="form-label">
                                Escalera:
                            </label>
                            <input type="text" class="form-control" name="escalera" id="escalera"
                                <?php
                                    if (isset($_POST['escalera'])) {
                                        echo ' value="'.trim($_POST['escalera']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="piso" class="form-label">
                                Piso:
                            </label>
                            <input type="text" class="form-control" name="piso" id="piso"
                                <?php
                                    if (isset($_POST['piso'])) {
                                        echo ' value="'.trim($_POST['piso']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="portal" class="form-label">
                                Portal:
                            </label>
                            <input type="text" class="form-control" name="portal" id="portal"
                                <?php
                                    if (isset($_POST['portal'])) {
                                        echo ' value="'.trim($_POST['portal']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="letra" class="form-label">
                                Letra:
                            </label>
                            <input type="text" class="form-control" name="letra" id="letra"
                                <?php
                                    if (isset($_POST['letra'])) {
                                        echo ' value="'.trim($_POST['letra']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-group">
                            <label for="puerta" class="form-label">
                                Puerta:
                            </label>
                            <input type="text" class="form-control" name="puerta" id="puerta"
                                <?php
                                    if (isset($_POST['puerta'])) {
                                        echo ' value="'.trim($_POST['puerta']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="complemento" class="form-label">
                                Complemento: (*)
                            </label>
                            <input type="text" class="form-control" name="complemento" id="complemento" 
                                <?php
                                    if (isset($_POST['complemento'])) {
                                        echo ' value="'.trim($_POST['complemento']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="fecha" class="form-label">
                                Fecha de nacimiento: (*)
                            </label>
                            <input type="date" class="form-control" name="fecha" id="fecha"
                                <?php
                                    if (isset($_POST['fecha'])) {
                                        echo ' value="'.trim($_POST['fecha']).'"';
                                    }
                                ?>
                                >
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="form-group">
                            <label for="pais" class="form-label">
                                País: (*)
                            </label>
                            <?php 
                                $data = file_get_contents("json/paises.json");
                                $countries = json_decode($data, true);
                                $name = "pais";
                                $id = "code";
                                $field = "name_es";

                                echo generarSelect($countries['countries'], $name, $id, $field);
                            ?>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="form-group">
                            <label for="provincia" class="form-label">
                                Provincia: (*)
                            </label>
                            <?php 
                                $data = file_get_contents("json/provincias.json");
                                $countries = json_decode($data, true);
                                $name = "provincia";
                                $id = "provincia_id";
                                $field = "nombre";

                                echo generarSelect($countries, $name, $id, $field);
                            ?>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="form-group isla-hidden">
                            <label for="isla" class="form-label">
                                Isla: (*)
                            </label>
                            <select name="isla" class="form-select" aria-label="Seleccionar isla">
                                <option value="">Seleccione una opción.</option>
                                <option value="gran-canaria" <?php if (isset($_POST['isla']) && $_POST['isla'] == '1'): ?>selected<?php endif; ?> >Gran Canaria</option>
                                <option value="tenerife" <?php if (isset($_POST['isla']) && $_POST['isla'] == '2'): ?>selected<?php endif; ?> >Tenerife</option>
                                <option value="la-palma" <?php if (isset($_POST['isla']) && $_POST['isla'] == '3'): ?>selected<?php endif; ?> >La Palma</option>
                                <option value="el-hierro" <?php if (isset($_POST['isla']) && $_POST['isla'] == '2'): ?>selected<?php endif; ?> >El Hierro</option>
                                <option value="la-gomera" <?php if (isset($_POST['isla']) && $_POST['isla'] == '3'): ?>selected<?php endif; ?> >La Gomera</option>
                                <option value="fuerteventura" <?php if (isset($_POST['isla']) && $_POST['isla'] == '2'): ?>selected<?php endif; ?> >Fuerteventura</option>
                                <option value="lanzarote" <?php if (isset($_POST['isla']) && $_POST['isla'] == '3'): ?>selected<?php endif; ?> >Lanzarote</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="municipio" class="form-label">
                                Municipio: (*)
                            </label>
                            <?php 
                                $data = file_get_contents("json/municipios.json");
                                $countries = json_decode($data, true);
                                $name = "municipio";
                                $id = "municipio_id";
                                $field = "nombre";

                                echo generarSelect($countries, $name, $id, $field);
                            ?>
                        </div>
                    </div>
                    <!--<div class="col-3">
                        <div class="form-group">
                            <label for="localidad" class="form-label">
                                Localidad: (*)
                            </label>
                            <select name="localidad" class="form-select" aria-label="Seleccionar localidad">
                                <option value="">Seleccione una opción.</option>
                                <option value="1" <?php if (isset($_POST['localidad']) && $_POST['localidad'] == '1'): ?>selected<?php endif; ?> >One</option>
                                <option value="2" <?php if (isset($_POST['localidad']) && $_POST['localidad'] == '2'): ?>selected<?php endif; ?> >Two</option>
                                <option value="3" <?php if (isset($_POST['localidad']) && $_POST['localidad'] == '3'): ?>selected<?php endif; ?> >Three</option>
                            </select>
                        </div>
                    </div>-->
                    <div class="col-6">
                        <div class="form-group">
                            <label for="codigo_postal" class="form-label">
                                Código postal: (*)
                            </label>
                            <select name="codigo_postal" class="form-select" aria-label="Seleccionar código postal">
                                <option value="">Seleccione una opción.</option>
                                <option value="1" <?php if (isset($_POST['codigo_postal']) && $_POST['codigo_postal'] == '1'): ?>selected<?php endif; ?> >One</option>
                                <option value="2" <?php if (isset($_POST['codigo_postal']) && $_POST['codigo_postal'] == '2'): ?>selected<?php endif; ?> >Two</option>
                                <option value="3" <?php if (isset($_POST['codigo_postal']) && $_POST['codigo_postal'] == '3'): ?>selected<?php endif; ?> >Three</option>
                            </select>
                        </div>
                    </div>
                </div>                
            </div>
        
        
            <p class="title mt-3">MÁS DATOS</p>

            <div class="content">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="El alumno es huérfano absoluto." name="alumno_huerfano" id="alumno_huerfano" <?php if (isset($_POST['alumno_huerfano']) && $_POST['alumno_huerfano'] == 'El alumno es huérfano absoluto.'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="alumno_huerfano">
                        El alumno es huérfano absoluto.
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="El alumno se encuentra en régimen de tutela y guarda por la Administración." name="alumno_tutelado" id="alumno_tutelado" <?php if (isset($_POST['alumno_tutelado']) && $_POST['alumno_tutelado'] == 'El alumno se encuentra en régimen de tutela y guarda por la Administración.'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="alumno_tutelado">
                        El alumno se encuentra en régimen de tutela y guarda por la Administración.
                    </label>
                </div>
            </div>
        
        
            <p class="title mt-3">ALERGIAS, PATOLOGÍAS O DIETAS ESPECIALES</p>

            <div class="content">
                <div class="mb-3">
                    <label for="otras_alergias" class="form-label">Otras alergias:</label>
                    <!-- Por algún motivo, los saltos de línea provocan espacios en el comando PHP. -->
                    <textarea class="form-control" name="otras_alergias" id="otras_alergias" rows="3"><?php if(isset($_POST['otras_alergias'])): echo $_POST['otras_alergias']; endif?></textarea>
                </div>
            </div>
        
        
            <p class="title mt-3">DATOS CADÉMICOS DEL ALUMNO o ALUMNA</p>

            <div class="content">
                <p>Seleccione opción (seleccionar 1):</p>

                <label for="ciencias" class="error" style="display:none;">* Please pick an option above</label>


                <div class="form-check">
                    <input class="form-check-input" type="radio" name="ciencias" value="Ciencias de la salud" id="ciencias_de_la_salud" <?php if (isset($_POST['ciencias']) && $_POST['ciencias'] == 'Ciencias de la salud'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="ciencias_de_la_salud">
                        ITINERARIO: CIENCIAS DE LA SALUD.
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="ciencias" value="Ciencias de la tecnología" id="cientifico_tecnologico" <?php if (isset($_POST['ciencias']) && $_POST['ciencias'] == 'Ciencias de la tecnología'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="cientifico_tecnologico">
                        ITINERARIO: CIENTÍFICO-TECNOLÓGICO.
                    </label>
                </div>

                <?php
                if (isset($_POST['bloque_i_list'])) {
                    if (is_array($_POST['bloque_i_list'])) {
                        $selected = '';
                        $num_countries = count($_POST['bloque_i_list']);
                        $current = 0;
                        foreach ($_POST['bloque_i_list'] as $key => $value) {
                            if ($current != $num_countries-1)
                                $selected .= $value.', ';
                            else
                                $selected .= $value.'.';
                            $current++;
                        }
                    }
                    else {
                        $selected = 'Debes seleccionar un país';
                    }

                    echo '<div>Has seleccionado: '.$selected.'</div>';
                }    
                ?>

            <p class="mt-3">Bloque 1 (seleccionar 6) (máximo 6). Se debe seleccionar por preferencia:</p>

            <label for="bloque_i_list" class="error" style="display:none;">* Please pick an option above</label>

                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="lengua_castellana_y_literatura_i" value="Lengua Castellana y Literatura I" 
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Lengua Castellana y Literatura I", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="lengua_castellana_y_literatura_i">
                        Lengua Castellana y Literatura I
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="filosofia" value="Filosofía"
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Filosofía", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="filosofia">
                        Filosofía
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="educacion_fisica" value="Educación Física"
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Educación Física", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="educacion_fisica">
                        Educación Física
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="matematicas_i" value="Matemáticas I"
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Matemáticas I", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="matematicas_i">
                        Matemáticas I
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="fisica_y_quimica" value="Física y Química"
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Física y Química", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="fisica_y_quimica">
                        Física y Química
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input checkbox-subject" type="checkbox" name="bloque_i_list" id="tutoria" value="Tutoría"
                    <?php
                        if(isset($_POST['bloque_i_list'])) {
                            $bloque_i = $_POST['bloque_i_list'];
                            if(in_array("Tutoría", $bloque_i)) {
                                echo "checked='checked'";
                            }
                        }
                    ?>
                    >
                    <label class="form-check-label" for="tutoria">
                        Tutoría
                    </label>
                </div>


                <p class="mt-3">Bloque 2 (seleccionar 1):</p>

                <label for="bloque_ii" class="error" style="display:none;">* Please pick an option above</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_ii" id="ingles_i" value="Primera lengua extranjera (inglés) I" <?php if (isset($_POST['bloque_ii']) && $_POST['bloque_ii'] == 'Primera lengua extranjera (inglés) I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="ingles_i">
                        Primera lengua extranjera (inglés) I
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_ii" id="italiano_i" value="Primera lengua extranjera (italiano) I" <?php if (isset($_POST['bloque_ii']) && $_POST['bloque_ii'] == 'Primera lengua extranjera (italiano) I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="italiano_i">
                        Primera lengua extranjera (italiano) I
                    </label>
                </div>

                <p class="mt-3">Bloque 3 (seleccionar 1):</p>

                <label for="bloque_iii" class="error" style="display:none;">* Please pick an option above</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iii" id="biologia_y_geologia" value="Biología y Geología" <?php if (isset($_POST['bloque_iii']) && $_POST['bloque_iii'] == 'Biología y Geología'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="biologia_y_geologia">
                        Biología y Geología
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iii" id="dibujo_tecnico_i" value="Dibujo Técnico I" <?php if (isset($_POST['bloque_iii']) && $_POST['bloque_iii'] == 'Dibujo Técnico I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="dibujo_tecnico_i">
                        Dibujo Técnico I
                    </label>
                </div>


                <p class="mt-3">Bloque 4 (seleccionar 1):</p>

                <label for="bloque_iv" class="error" style="display:none;">* Please pick an option above</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iv" id="tecnologia_industrial_i" value="Tecnología Industrial I"  <?php if (isset($_POST['bloque_iv']) && $_POST['bloque_iv'] == 'Tecnología Industrial I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="tecnologia_industrial_i">
                        Tecnología Industrial I
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iv" id="cultura_cientifica" value="Cultura Científica" <?php if (isset($_POST['bloque_iv']) && $_POST['bloque_iv'] == 'Cultura Científica'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="cultura_cientifica">
                        Cultura Científica
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iv" id="segunda_lengua_extranjera_ingles_i" value="Segunda lengua extranjera (Inglés) I" <?php if (isset($_POST['bloque_iv']) && $_POST['bloque_iv'] == 'Segunda lengua extranjera (Inglés) I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="segunda_lengua_extranjera_ingles_i">
                        Segunda lengua extranjera (Inglés) I
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iv" id="biologia_y_geologia_e" value="Biología y Gelogía (E)" <?php if (isset($_POST['bloque_iv']) && $_POST['bloque_iv'] == 'Biología y Gelogía (E)'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="biologia_y_geologia_e">
                        Biología y Gelogía (E)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_iv" id="dibujo_tecnico_i_e" value="Dibujo Técnico I (E)" <?php if (isset($_POST['bloque_iv']) && $_POST['bloque_iv'] == 'Dibujo Técnico I (E)'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="dibujo_tecnico_i_e">
                        Dibujo Técnico I (E)
                    </label>
                </div>

                

                <p class="mt-3">Bloque 5 (seleccionar 1):</p>

                <label for="bloque_v" class="error" style="display:none;">* Please pick an option above</label>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_v" id="religion_catolica" value="Religión Católica" <?php if (isset($_POST['bloque_v']) && $_POST['bloque_v'] == 'Religión Católica'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="religion_catolica">
                        Religión Católica
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="bloque_v" id="informacion_y_comunicacion_i" value="Tecnologías de la información y la comunicación I" <?php if (isset($_POST['bloque_v']) && $_POST['bloque_v'] == 'Tecnologías de la información y la comunicación I'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="informacion_y_comunicacion_i">
                        Tecnologías de la información y la comunicación I
                    </label>
                </div>
            </div>
        
        
            <p class="title mt-3">MEDIOS DE DIFUSIÓN</p>

            <div class="content">
                <p class="text-justify">
                    CONSENTIMIENTO INFORMADO TRATAMIENTO DE IMÁGENES/VOZ DEL ALUMNO EN CENTROS DOCENTES DE TITULARIDAD PÚBLICA DE LA CONSEJERÍA DE EDUCACIÓN, UNIVERSIDADES, CULTURA Y DEPORTES.
                </p>
                <p class="text-justify">
                    De acuerdo con el Reglamento General de Protección de Datos y la Ley Orgánica 3 / 2018, de 5 de diciembre, de Protección de Datos Personales y Garantías de los Derechos Digitales, mediante la firma del presente documento se presta voluntariamente el consentimiento inequivoco e informado y se autoriza expresamente al centro docente al "tratamiento de imagen / voz de actividades de los centros de titularidad pública", mediante los siguientes medios (sólo se extenderá la difusión de imágenes / voz) por los medios expresados marcados a continuación.
                </p>

                <div class="form-check representante col-12">
                    <input class="form-check-input" type="radio" name="consiente" id="es_consiente" value="Consiente" <?php if (isset($_POST['consiente']) && $_POST['consiente'] == 'Consiente'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="es_consiente">
                        Consiente
                    </label>
                </div>
                <div class="form-check representante">
                    <input class="form-check-input" type="radio" name="consiente" id="no_consiente" value="No consiente" <?php if (isset($_POST['consiente']) && $_POST['consiente'] == 'No consiente'): ?>checked='checked'<?php endif; ?>>
                    <label class="form-check-label" for="no_consiente">
                        No consiente
                    </label>
                </div>

                <div class="row mt-3">
                    <p class="col-12">Página web del centro docente:</p>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="pagina_consiente" id="pagina_es_consiente" value="Consiente" <?php if (isset($_POST['pagina_consiente']) && $_POST['pagina_consiente'] == 'Consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="pagina_es_consiente">
                            Consiente
                        </label>
                    </div>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="pagina_consiente" id="pagina_no_consiente" value="No consiente" <?php if (isset($_POST['pagina_consiente']) && $_POST['pagina_consiente'] == 'No consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="pagina_no_consiente">
                            No consiente
                        </label>
                    </div>
                </div>

                <div class="row mt-3">
                    <p class="col-12">App de alumnos y familias:</p>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="app_consiente" id="app_es_consiente" value="Consiente" <?php if (isset($_POST['app_consiente']) && $_POST['app_consiente'] == 'Consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="app_es_consiente">
                            Consiente
                        </label>
                    </div>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="app_consiente" id="app_no_consiente" value="No consiente" <?php if (isset($_POST['app_consiente']) && $_POST['app_consiente'] == 'No consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="app_no_consiente">
                            No consiente
                        </label>
                    </div>
                </div>

                <div class="row mt-3">
                    <p class="col-12">Facebook:</p>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="facebook_consiente" id="facebook_es_consiente" value="Consiente" <?php if (isset($_POST['facebook_consiente']) && $_POST['facebook_consiente'] == 'Consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="facebook_es_consiente">
                            Consiente
                        </label>
                    </div>
                    <div class="col-12 col-lg-2 form-check">
                        <input class="form-check-input" type="radio" name="facebook_consiente" id="facebook_no_consiente" value="No consiente" <?php if (isset($_POST['facebook_consiente']) && $_POST['facebook_consiente'] == 'No consiente'): ?>checked='checked'<?php endif; ?>>
                        <label class="form-check-label" for="facebook_no_consiente">
                            No consiente
                        </label>
                    </div>
                </div>

                <p class="text-justify mt-3">
                    El consentimiento aquí otorgado podrá ser revocado en cualquier momento ante el propio centro docente, teniendo en cuenta que dicha revocación no sufrirá efectos retroactivos.
                </p>
            </div>
        
        
            <p class="title mt-3">Documentos Adjuntos</p>

            <div class="alert alert-warning" role="alert">
                <p class="text-justify">Aviso:</p>
                <ul>
                    <li>Los formatos permitidos son <span class="font-weight-bold">jpg, png, txt, odt, pdf, jpeg, doc, docx.</span></li>
                    <li>El tamaño máximo por fichero es de <span class="font-weight-bold">10MB</span>.</li>
                    <li>El nombre de los ficheros no debe incluir carácteres acentuados, carácteres con diéresis, la eñe o carácteres especiales <span class="font-weight-bold">! " # $ & ' * + , . / ; < - > ? @ [ ] ( ) ^ ' { | }</span>.</li>
                </ul>
            </div>

            <p class="subtitule">
                Lista de documentos pendientes:
            </p>
            <div class="content">
                <div class="row">
                    <p class="col-6 font-weight-bold">Documento</p>
                    <p class="col-6 font-weight-bold text-right">Acciones</p>
                </div>

                <div class="row">
                    <p class="col-12 col-lg-7 text-justify">DNI del alumno o alumna (o de los padres, madres o tutores legales de alumnado sin DNI) (SOLO ALUMNADO NUEVO).</p>
                    <div class="col-12 col-lg-5 custom-file">
                        <div class="row">
                            <input type="file" class="col-lg-7 custom-file-input hidden-input" name="archivo_dni" id="archivo_dni">
                            <label class="col-12 col-lg-5 btn btn-primary custom-file-label" for="archivo_dni">Elegir archivo...</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <p class="col-12 col-lg-7 text-justify">Para el alumnado procediente de otros centros, certificación académica del centro de origen en el que se especifique la promoción de curso o la terminación de estudios con propuesta para titulación.</p>
                    <div class="col-12 col-lg-5 custom-file">
                        <div class="row">
                            <input type="file" class="col-lg-7 custom-file-input hidden-input" name="certificado_academico" id="certificado_academico">
                            <label class="col-12 col-lg-5 btn btn-primary custom-file-label" for="certificado_academico">Elegir archivo...</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary mr-9" name="submit">Procesar</button>
                    <button type="cancel" class="btn btn-primary ml-9" name="cancel">Cancelar</button>
                </div>
            </div>
  
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>