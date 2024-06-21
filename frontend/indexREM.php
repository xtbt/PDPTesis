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
    define('MODULE_NAME', 'DASHBOARD');

    // Privileges definition ***************************************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    // HERE COMES ALL THE PRIVILEGES ALGORITHMS ********************************
    $accessGranted = true;
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
<?php
    if ($accessGranted) {
?>
            <h1>.: Tablero Principal :.</h1>
<?php
  }
  else {
?>
            <h6 class="error_box">Debes contar con suficientes privilegios para acceder a estos m&#243;dulos especiales de <?php echo PROJECT_NAME; ?>.</h6>
<?php
  };
?>
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
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>frontend/scripts/dashboard.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>