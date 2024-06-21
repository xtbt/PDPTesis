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
    define('MODULE_NAME', 'TICKETS-CRUD');
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
                <h1>.: Gesti&#243;n de Tickets :.</h1>
                
                <p><a href="<?php echo BASE_DIR; ?>frontend/index.php"><img src="<?php echo BASE_DIR; ?>frontend/assets/images/dashboard.png" /> Regresar al Tablero Principal</a></p>
                
                <form id="frm_tickets_crud" method="post">
                <fieldset id="mainFieldset">
                    <legend>Datos principales</legend>
                    <section id="mainFields">
                        <input type="hidden" id="entity" name="entity" value="<?php echo MODULE_NAME; ?>" />
                        <p id="FormMessage"></p>
                        <label for="txt_TicketId">Id: 
                            <input type="text" name="txt_TicketId" id="txt_TicketId" />
                        </label>
                        <label for="sel_ProblemCategoryId">Categor&#237;a de Falla: 
                            <select id="sel_ProblemCategoryId" name="sel_ProblemCategoryId">
                                <option value="-1">Selecciona una categor&#237;a</option>
                            </select>
                        </label>
                        <label for="sel_ProblemSubCategoryId">SubCategor&#237;a de Falla: 
                            <select id="sel_ProblemSubCategoryId" name="sel_ProblemSubCategoryId">
                                <option value="-1">Selecciona una subcategor&#237;a</option>
                            </select>
                        </label>
                        <label for="txta_TicketDescription">Descripci&#243;n: 
                            <textarea name="txta_TicketDescription" id="txta_TicketDescription"></textarea>
                        </label>
                        <label for="txt_TicketStatus">Status: 
                            <input type="text" name="txt_TicketStatus" id="txt_TicketStatus" />
                        </label>
                    </section>
                    <section id="mainImageSection">
                        <p>Foto de portada</p>
                        <p id="mainImageMessage"></p>
                        <img id="mainImage" src="" alt="Sin foto de portada">
                        <input type="file" name="mainImageFile" id="mainImageFile" accept="image/jpg, image/jpeg, image/png" />
                        <button type="button" value="Reemplazar" name="btn_replaceMainImage" id="btn_replaceMainImage">Reemplazar foto</button>
                    </section>
                    <p>
                        <input type="button" value="Crear" name="btn_create" id="btn_create" />
                        <input type="button" value="Actualizar" name="btn_update" id="btn_update" />
                        <input type="button" value="Cancelar" name="btn_deactivate" id="btn_deactivate" />
                        <input type="button" value="Reabrir" name="btn_reactivate" id="btn_reactivate" />
                    </p>
                </fieldset>
                <fieldset id="updateFieldset">
                    <legend>Seguimiento de Tickets</legend>
                    <section id="updateFields">
                        <p id="TicketUpdateMessage"></p>
                        <label for="txta_TicketUpdateNote">Nota de seguimiento: 
                            <textarea name="txta_TicketUpdateNote" id="txta_TicketUpdateNote"></textarea>
                        </label>
                        <p>
                            <input type="button" value="Agregar Seguimiento" name="btn_addUpdate" id="btn_addUpdate" />
                        </p>
                    </section>
                    <div class="table_container">
                    <table id="tblTicketUpdates">
                        <?php $tbl_cols = 0; ?>
                        <caption>Seguimiento de Ticket</caption>
                        <thead>
                            <tr>
                                <th>ID</th><?php $tbl_cols++; ?>
                                <th>Fecha/Hora</th><?php $tbl_cols++; ?>
                                <th>Nota</th><?php $tbl_cols++; ?>
                                <th>Usuario</th><?php $tbl_cols++; ?>
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
                </fieldset>
                <fieldset id="extraImagesFieldset">
                    <legend>Fotografías adicionales</legend>
                    <p id="extraImageMessage"></p>
                    <section id="extraImageThumbnailCollection">
                    </section>
                    <input type="file" name="extraImageFile" id="extraImageFile" accept="image/jpg, image/jpeg, image/png" />
                    <button type="button" value="Agregar Imagen" name="btn_addExtraImage" id="btn_addExtraImage">Agregar Imagen</button>
                </fieldset>
                <fieldset id="ticketCloseFieldset">
                    <legend>Cierre de Ticket</legend>
                    <p id="ticketCloseMessage"></p>
                    <label for="txta_TicketLastComment">Comentario de cierre: 
                        <textarea name="txta_TicketLastComment" id="txta_TicketLastComment"></textarea>
                    </label>
                    <p>
                        <input type="button" value="Cerrar Ticket" name="btn_closeTicket" id="btn_closeTicket" />
                    </p>
                </fieldset>
                <fieldset id="lastCommentFieldset">
                    <legend>Comentario de Cierre de Ticket</legend>
                    <p id="lastComment"></p>
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
    <script type="text/javascript" src="<?php echo BASE_DIR; ?>frontend/scripts/incidents/tickets_form.js"></script>
    <!-- HTML5_BODY_END_SCRIPT_END -->
</body>
<!-- HTML5_BODY_END -->
</html>