// ****************************************************************************
// ************************ AUTHORIZATION FUNCTIONS ***************************
// ****************************************************************************

// INITIAL AUTHORIZATION BLOCK ################################################
window.addEventListener('load', () => {
    prepareLoggedInContext();
    prepareLoggedOutContext();
    let APIEndpoint = BASE_DIR + 'api/v1/index.php/users/verifyToken';
    fetchData (APIEndpoint)
    .then (APIResponse => {
        DEBUG_MODE ? console.log('We got API response: ') : true;
        DEBUG_MODE ? console.log(APIResponse) : true;
        showLoggedInContext();
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : true;
        showLoggedOutContext();
    });
});

// LOGGED OUT CONTEXT #########################################################
function showForbiddenContext() {
    document.getElementById('mainUI').style.display = 'none';
    document.getElementById('sessionInfo').style.display = 'block';
    document.getElementById('adminArea').style.display = 'none';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('forbiddenMessage').style.display = 'block';
};

// LOGGED IN CONTEXT ##########################################################
function showLoggedInContext() {
    document.getElementById('mainUI').style.display = 'block';
    document.getElementById('sessionInfo').style.display = 'block';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('forbiddenMessage').style.display = 'none';
    // TODO: IF ADMIN THEN SHOW ADMIN OPTIONS
    if (sessionStorage.getItem('appAreaId') == 1) {
        document.getElementById('adminArea').style.display = 'block';
    };
    // TODO: IF ADMIN THEN SHOW ADMIN OPTIONS
};

function prepareLoggedInContext() {
    document.getElementById('logoutLink').addEventListener('click', (e) => {
        e.preventDefault();
        sessionStorage.removeItem('appUserId');
        sessionStorage.removeItem('appAreaId');
        sessionStorage.removeItem('appBearerToken');
        showLoggedOutContext();
    });
};

// LOGGED OUT CONTEXT #########################################################
function showLoggedOutContext() {
    document.getElementById('mainUI').style.display = 'none';
    document.getElementById('sessionInfo').style.display = 'none';
    document.getElementById('adminArea').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('forbiddenMessage').style.display = 'block';
};

function prepareLoggedOutContext() {
    const txtAuth_Username = document.getElementById('txtAuth_Username');
    const txtAuth_Username_Criteria = {
        "fieldType": "text",
        "required": true,
        "maxLength": 16,
        "minLength": 4
    };

    const pswAuth_Password = document.getElementById('pswAuth_Password');
    const pswAuth_Password_Criteria = {
        "fieldType": "password",
        "required": true,
        "maxLength": 16,
        "minLength": 4
    };

    const btn_Login = document.getElementById('btn_Login');

    setHTMLAttributes(txtAuth_Username, txtAuth_Username_Criteria);
    setHTMLAttributes(pswAuth_Password, pswAuth_Password_Criteria);

    btn_Login.addEventListener('click', function () {
        const APIMethod = 'POST';
        let JSONBody = null;
        let loginFormMessage = '';
        loginFormValidation()
        .then(Response => {
            DEBUG_MODE ? console.log(Response) : true;
            return FormData2JSONBody ( new FormData( loginForm ) );
        })
        .then(Response => {
            DEBUG_MODE ? console.log(Response) : true;
            JSONBody = Response;
            return sendData(BASE_DIR + 'api/v1/index.php/users/doLogin', APIMethod, JSONBody);
        })
        .then(Response => {
            DEBUG_MODE ? console.log(Response) : true;
            loginFormMessage = Response.body.msg;
            displayFormMessage( loginFormMessage, 'ok', 'loginFormMessage' );
            return Promise.resolve(Response);
        })
        .then(Response => {
            DEBUG_MODE ? console.log('UserId: ' + Response.body.Auth.UserId) : true;
            DEBUG_MODE ? console.log('Token: ' + Response.body.Auth.Token) : true;
            sessionStorage.setItem('appUserId', Response.body.Auth.UserId);
            sessionStorage.setItem('appAreaId', Response.body.Auth.AreaId);
            sessionStorage.setItem('appBearerToken', Response.body.Auth.Token);
            return Promise.resolve('Ok: MainUI display');
        })
        .then(Response => {
            DEBUG_MODE ? console.log(Response) : true;
            DEBUG_MODE ? console.log(sessionStorage) : true;
            document.location.reload(); // Refresh page with logged in credentials
        })
        .catch(Error => {
            DEBUG_MODE ? console.error(Error) : false;
            displayFormMessage( Error, 'error', 'loginFormMessage' );
        });
    });

    const loginFormValidation = () => {
        return new Promise ((Resolve, Reject) => {
            validateText(txtAuth_Username, txtAuth_Username_Criteria)
            .then(Response => {
                DEBUG_MODE ? console.log('Username input validation successful') : true;
                return validateText(pswAuth_Password, pswAuth_Password_Criteria);
            })
            .then(Response => {
                DEBUG_MODE ? console.log('Password input validation successful') : true;
                Resolve('OK: Form input validation successful');
            })
            .catch (Error => {
                DEBUG_MODE ? console.error(Error) : false;
                Reject('ERROR: Form input validation failed');
            });
        });
    };
};