<?php
    // vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
    // IMPERATIVE DECLARATIONS vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
    // vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
    // BEGIN: Base directory recognition algorithm ****************************
    $dir = explode("/", str_replace($_SERVER['DOCUMENT_ROOT'], "", 
    ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 
    str_replace("\\", "/", dirname(realpath(__FILE__))) : 
    dirname(realpath(__FILE__)))));
    define('BASE_ROOT', $_SERVER['DOCUMENT_ROOT'].($dir[1] == '' ? '/' : '/'.$dir[1].'/')); // FOR PHP
    define('BASE_DIR', ($dir[1] == '' ? '/' : '/'.$dir[1].'/')); // FOR HTML
    // END: Base directory recognition algorithm ******************************
    
    // NEEDED MODULES
    require_once(BASE_ROOT."frontend/config.php");

    // SECTION IDENTIFICATION
    define('MODULE_NAME', 'USERS-CRUD');
    // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // FIN DE DECLARACIONES OBLIGATORIAS ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
?>

<!-- HTML5_PROPERTIES_START -->
<!DOCTYPE html>
<html lang="es">
<!-- HTML5_PROPERTIES_END -->

<!-- HTML5_HEAD_START -->
<head>

<?php require(BASE_ROOT."frontend/html-includes/HTML5_HEAD.PHP"); ?>
    <!-- HTML5_HEAD_ADITIONAL_CSS_JS_START -->
    <link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_DIR; ?>frontend/styles/dummy.css" />
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>frontend/scripts/dummy.js"></script>
    <!-- HTML5_HEAD_ADITIONAL_CSS_JS_END -->

</head>
<!-- HTML5_HEAD_END -->

<!-- HTML5_BODY_START -->
<body>
    <!-- HTML5_BODY_CONTAINER_START -->
    <div id="container">
        <!-- HTML5_BODY_CONTAINER_HEADER_START -->
        <header>

<?php require(BASE_ROOT."frontend/html-includes/HTML5_BODY_CONTAINER_HEADER.PHP"); ?>

        </header>
        <!-- HTML5_BODY_CONTAINER_HEADER_END -->
        <hr />
        <!-- HTML5_BODY_CONTAINER_MAIN_START -->
        <main>
            <section id="mainUI">
                <h1>.: Gesti&#243;n de Usuarios :.</h1>
                
                <p><a href="<?php echo BASE_DIR; ?>frontend/admin/general/users/index.php"><img src="<?php echo BASE_DIR; ?>frontend/assets/images/users.png" /> Regresar al cat&#225;logo de usuarios</a></p>
                
                <form id="frm_users_crud" method="post">
                <fieldset>
                    <input type="hidden" id="entity" name="entity" value="<?php echo MODULE_NAME; ?>" />
                    <p id="FormMessage"></p>
                    <label>Id: 
                        <input type="text" name="txt_UserId" id="txt_UserId" />
                    </label>
                    <label>&#193;rea: 
                        <select id="sel_AreaId" name="sel_AreaId">
                            <option value="-1">Selecciona un &#225;rea</option>
                        </select>
                    </label>
                    <label>Nombre de usuario: 
                        <input type="text" name="txt_Username" id="txt_Username" />
                    </label>
                    <label>Contraseña: 
                        <input type="password" name="psw_Password" id="psw_Password" />
                    </label>
                    <label>Email: 
                        <input type="email" name="eml_Email" id="eml_Email" />
                    </label>
                    <label>No. Tel&#233;fono: 
                        <input type="tel" name="tel_PhoneNumber" id="tel_PhoneNumber" />
                    </label>
                    <label>Nombre(s): 
                        <input type="text" name="txt_FirstName" id="txt_FirstName" />
                    </label>
                    <label>Apellido(s): 
                        <input type="text" name="txt_LastName" id="txt_LastName" />
                    </label>
                    <label>Status: 
                        <input type="text" name="txt_UserStatus" id="txt_UserStatus" />
                    </label>
                    <p>
                        <input type="button" value="Crear" name="btn_create" id="btn_create" />
                        <input type="button" value="Actualizar" name="btn_update" id="btn_update" />
                        <input type="button" value="Desactivar" name="btn_deactivate" id="btn_deactivate" />
                        <input type="button" value="Reactivar" name="btn_reactivate" id="btn_reactivate" />
                    </p>
                </fieldset>
                </form>
            </section>
        </main>
        <!-- HTML5_BODY_CONTAINER_MAIN_END -->
        <hr />
        <!-- HTML5_BODY_CONTAINER_FOOTER_START -->
        <footer>

<?php require(BASE_ROOT."frontend/html-includes/HTML5_BODY_CONTAINER_FOOTER.PHP"); ?>

        </footer>
        <!-- HTML5_BODY_CONTAINER_FOOTER_END -->
    </div>
    <!-- HTML5_BODY_CONTAINER_END -->
    <!-- HTML5_BODY_END_SCRIPT_START -->
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>frontend/scripts/admin/general/users_form.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>