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
    require_once(BASE_ROOT."config.php");

    // SECTION IDENTIFICATION
    define('MODULE_NAME', 'COMBOS-LIST');

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

<?php require(BASE_ROOT."html-includes/HTML5_HEAD.PHP"); ?>
    <!-- HTML5_HEAD_ADITIONAL_CSS_JS_START -->
    <link rel="stylesheet" media="all" type="text/css" href="<?php echo BASE_DIR; ?>styles/dummy.css" />
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>scripts/dummy.js"></script>
    <!-- HTML5_HEAD_ADITIONAL_CSS_JS_END -->

</head>
<!-- HTML5_HEAD_END -->

<!-- HTML5_BODY_START -->
<body>
    <!-- HTML5_BODY_CONTAINER_START -->
    <div id="container">
        <!-- HTML5_BODY_CONTAINER_HEADER_START -->
        <header>

<?php require(BASE_ROOT."html-includes/HTML5_BODY_CONTAINER_HEADER.PHP"); ?>

        </header>
        <!-- HTML5_BODY_CONTAINER_HEADER_END -->
        <hr />
        <!-- HTML5_BODY_CONTAINER_MAIN_START -->
        <main>
<?php
    if ($accessGranted) {
?>
            <h1>.: Cat&#225;logo de combos :.</h1>
            
            <p><a href="<?php echo BASE_DIR; ?>cruds/index.php"><img src="<?php echo BASE_DIR; ?>images/assets/goods.png" /> Regresar al cat&#225;logo general</a></p>

            <p><a href="<?php echo BASE_DIR; ?>cruds/combos/combos_form.php"><img src="<?php echo BASE_DIR; ?>images/assets/combos-crud.png" /> Nuevo combo</a></p>
            
            <form id="frm_filter" method="post">
            <fieldset>
                <input type="hidden" id="hdn_entity" name="hdn_entity" value="<?php echo MODULE_NAME; ?>" />
                <input type="text" id="txt_criteria_filter" name="txt_criteria_filter" placeholder="Id/Descuento/Producto" value="" />
                <select id="sel_statuses_filter" name="sel_statuses_filter">
                    <option value="-1">Todos los estados</option>
                </select>
                <p>
                    Total de registros: <span id="pagination-total_items"></span> Registros por p&#225;gina: <span id="pagination-items_per_page"></span>
                </p>
                    <span>P&#225;gina: </span>
                <p id="pagination-page_list">
                </p>
                <p>
                    <input type="button" value="Filtrar" name="btn_filter" id="btn_filter" />
                </p>
            </fieldset>
            </form>

            <div class="table_container">
            <table id="tblCombos">
                <?php $tbl_cols = 0; ?>
                <caption>Listado de Combos</caption>
                <thead>
                    <tr>
                        <th>ID</th><?php $tbl_cols++; ?>
                        <th>Producto ligado</th><?php $tbl_cols++; ?>
                        <th>Descuento de combo</th><?php $tbl_cols++; ?>
                        <th>Estado</th><?php $tbl_cols++; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="table-default_message" colspan="<?php echo $tbl_cols; ?>"></td>
                    </tr>
                </tbody>
                <tfoot>
                </tfoot>
            </table>
            </div>
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

<?php require(BASE_ROOT."html-includes/HTML5_BODY_CONTAINER_FOOTER.PHP"); ?>

        </footer>
        <!-- HTML5_BODY_CONTAINER_FOOTER_END -->
    </div>
    <!-- HTML5_BODY_CONTAINER_END -->
    <!-- HTML5_BODY_END_SCRIPT_START -->
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>scripts/cruds/combos_list.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>