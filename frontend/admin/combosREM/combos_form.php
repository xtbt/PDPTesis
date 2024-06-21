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
    define('MODULE_NAME', 'COMBOS-CRUD');

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
            <h1>.: Gesti&#243;n de Combos :.</h1>
            
            <p><a href="<?php echo BASE_DIR; ?>cruds/combos/index.php"><img src="<?php echo BASE_DIR; ?>images/assets/combos-catalog.png" /> Regresar al cat&#225;logo de combos</a></p>
            
            <form id="frm_combos_crud" method="post">
            <fieldset>
                <legend>Informaci&#243;n general del combo</legend>
                <input type="hidden" id="entity" name="entity" value="<?php echo MODULE_NAME; ?>" />
                <p id="FormMessage"></p>
                <label>Id: 
                    <input type="text" name="txt_ComboId" id="txt_ComboId" />
                </label>
                <label>Descuento: 
                    <input type="number" name="num_ComboDiscount" id="num_ComboDiscount" />
                </label>
                <label>GoodStatus: 
                    <input type="text" name="txt_ComboStatus" id="txt_ComboStatus" />
                </label>
                <p>
                    <input type="button" value="Crear" name="btn_create" id="btn_create" />
                    <input type="button" value="Actualizar" name="btn_update" id="btn_update" />
                    <input type="button" value="Desactivar" name="btn_deactivate" id="btn_deactivate" />
                    <input type="button" value="Reactivar" name="btn_reactivate" id="btn_reactivate" />
                </p>
            </fieldset>
            </form>

            <section id="DetailsSection">
                <form id="frm_combos_goods" method="post">
                    <fieldset>
                        <legend>Gesti&#243;n de productos incluidos</legend>
                        <input type="hidden" id="entity" name="entity" value="COMBOS-GOODS" />
                        <p id="DetailsFormMessage"></p>
                        <label for="sel_GoodId">Producto incluido: 
                            <select id="sel_GoodId" name="sel_GoodId">
                                <option value="-1">Selecciona un producto</option>
                            </select>
                        </label>
                        <label for="num_GoodQuantity">Cantidad incluida: 
                            <input type="number" name="num_GoodQuantity" id="num_GoodQuantity" />
                        </label>
                        <p>
                            <input type="button" value="Agregar" name="btn_add" id="btn_add" />
                        </p>
                    </fieldset>
                </form>

                <div class="table_container">
                <table id="tblCombos_Goods">
                    <?php $tbl_cols = 0; ?>
                    <caption>Listado de Productos incluidos</caption>
                    <thead>
                        <tr>
                            <th>Acci&#243;n</th><?php $tbl_cols++; ?>
                            <th>Producto incluido</th><?php $tbl_cols++; ?>
                            <th>Cantidad incluida</th><?php $tbl_cols++; ?>
                            <th>Precio original</th><?php $tbl_cols++; ?>
                            <th>Precio con descuento</th><?php $tbl_cols++; ?>
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
            </section>
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
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>scripts/cruds/combos_form.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>