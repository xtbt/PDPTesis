            <section id="sessionArea">
                <h6 id="forbiddenMessage" class="error_box">Debes contar con suficientes privilegios para acceder a estos m&#243;dulos especiales de <?php echo PROJECT_NAME; ?>.</h6>
                <form id="loginForm" method="post">
                <fieldset>
                    <p id="loginFormMessage"></p>
                    <label for="txtAuth_Username">Username: 
                        <input type="text" id="txtAuth_Username" name="txtAuth_Username" placeholder="Username" value="" />
                    </label>
                    <label for="pswAuth_Password">Password: 
                        <input type="password" name="pswAuth_Password" id="pswAuth_Password" placeholder="Password" value="" />
                    </label>
                    <p>
                        <input type="button" value="Login" name="btn_Login" id="btn_Login" />
                    </p>
                </fieldset>
                </form>
                <section id="sessionInfo">
                    <p><a id="logoutLink" href="#">Logout</a></p>
                </section>
                <section id="adminArea">
                    <p><a href="<?php echo BASE_DIR; ?>frontend/admin/index.php">M&#243;dulos de Administraci&#243;n</a></p>
                </section>
            </section>
            
            <section id="copyright">
                <span>Unitickets v0.5 Beta 2 (25-Octubre-2023)  :: Elaborado por IP.20: Ingenier&#237;a, Productos y Tecnolog&#237;as de la Informaci&#243;n</span>
            </section>