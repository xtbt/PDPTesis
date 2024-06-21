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
    define('MODULE_NAME', 'PROBLEMS-SUBCATEGORIES-LIST');
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
                <h1>.: Cat&#225;logo de subcategor&#237;as de fallas :.</h1>
                
                <p><a href="<?php echo BASE_DIR; ?>frontend/admin/index.php"><img src="<?php echo BASE_DIR; ?>frontend/assets/images/admin.png" /> Regresar a la administraci&#243;n</a></p>

                <p><a href="<?php echo BASE_DIR; ?>frontend/admin/problems/subcategories/crud_form.php"><img src="<?php echo BASE_DIR; ?>frontend/assets/images/problems-subcategories-new.png" /> Nueva subcategor&#237;a de fallas</a></p>
                
                <form id="frm_filter" method="post">
                <fieldset>
                    <input type="hidden" id="hdn_entity" name="hdn_entity" value="<?php echo MODULE_NAME; ?>" />
                    <input type="text" id="txt_criteria_filter" name="txt_criteria_filter" placeholder="Nombre" value="" />
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
                <table id="tblProblemsSubCategories">
                    <?php $tbl_cols = 0; ?>
                    <caption>Listado de Subcategor&#237;as de Fallas</caption>
                    <thead>
                        <tr>
                            <th>ID</th><?php $tbl_cols++; ?>
                            <th>Nombre Categor&#237;a</th><?php $tbl_cols++; ?>
                            <th>Nombre Subcategor&#237;a</th><?php $tbl_cols++; ?>
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
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>frontend/scripts/admin/problems/subcategories_list.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>