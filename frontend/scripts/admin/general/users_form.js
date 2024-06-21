'use strict';

// Module constants (To change in each module)
const Form = document.getElementById('frm_users_crud');
const IdField = 'UserId';
const StatusField = 'UserStatusId';
const APIEndpoint = BASE_DIR + 'api/v1/index.php/users';

// Form elements and criteria -------------------------------------------------
const txt_UserId = document.getElementById('txt_UserId');
const txt_UserId_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": 0,
    "readOnly": true,
    "maxLength": 2
};

const sel_AreaId = document.getElementById('sel_AreaId');
const sel_AreaId_Criteria = {
    "minIndex": 1
};

const txt_Username = document.getElementById('txt_Username');
const txt_Username_Criteria = {
    "fieldType": "text",
    "required": true,
    "maxLength": 16,
    "minLength": 4
};

const psw_Password = document.getElementById('psw_Password');
const psw_Password_Criteria = {
    "fieldType": "password",
    "required": true,
    "maxLength": 16,
    "minLength": 4
};

const eml_Email = document.getElementById('eml_Email');
const eml_Email_Criteria = {
    "fieldType": "email",
    "required": false,
    "maxLength": 48,
    "minLength": 8
};

const tel_PhoneNumber = document.getElementById('tel_PhoneNumber');
const tel_PhoneNumber_Criteria = {
    "fieldType": "tel",
    "required": false,
    "maxLength": 12,
    "minLength": 10
};

const txt_FirstName = document.getElementById('txt_FirstName');
const txt_FirstName_Criteria = {
    "fieldType": "text",
    "required": true,
    "maxLength": 32,
    "minLength": 3
};

const txt_LastName = document.getElementById('txt_LastName');
const txt_LastName_Criteria = {
    "fieldType": "text",
    "required": true,
    "maxLength": 32,
    "minLength": 3
};

const txt_UserStatus = document.getElementById('txt_UserStatus');
const txt_UserStatus_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": "Nuevo",
    "readOnly": true,
    "maxLength": 8
};
// Form action buttons --------------------------------------------------------
const btn_create = document.getElementById('btn_create');
const btn_update = document.getElementById('btn_update');
const btn_deactivate = document.getElementById('btn_deactivate');
const btn_reactivate = document.getElementById('btn_reactivate');

// Page Data Initialization ---------------------------------------------------
window.addEventListener('load', function() {
    loadAreas()
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return setFormElementsAttributes();
    })
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
        // If and Id is found into the querystring, we get the record data, if not, blank form is displayed
        return getRecordData ();
    })
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : false;
    });
});

function setFormElementsAttributes (isPromise = true) {
    DEBUG_MODE ? console.log('BEGIN: Loading elements attributes function') : true;
    setHTMLAttributes(txt_UserId, txt_UserId_Criteria);
    setHTMLAttributes(sel_AreaId, sel_AreaId_Criteria);
    setHTMLAttributes(txt_Username, txt_Username_Criteria);
    setHTMLAttributes(psw_Password, psw_Password_Criteria);
    setHTMLAttributes(eml_Email, eml_Email_Criteria);
    setHTMLAttributes(tel_PhoneNumber, tel_PhoneNumber_Criteria);
    setHTMLAttributes(txt_FirstName, txt_FirstName_Criteria);
    setHTMLAttributes(txt_LastName, txt_LastName_Criteria);
    setHTMLAttributes(txt_UserStatus, txt_UserStatus_Criteria);
    DEBUG_MODE ? console.log('Loading successful!') : true;
    return isPromise ? Promise.resolve('END: Loading elements attributes function') : true;
};

function getRecordData () {
    return new Promise ( ( Resolve, Reject ) => {
        let Id = null;
        let Status = null;

        getEntityId ( IdField )
        .then ( Response => {
            Id = Response;
            let singleAPIEndpoint = APIEndpoint + '/' + Id;
            return fetchData ( singleAPIEndpoint );
        })
        .then (Response => {
            DEBUG_MODE ? console.log(Response) : true;
            Status = Response.body.data[Id][StatusField];
            setFormElementsValues ( Response.body.data[Id] );
            setFormElementsAvailability ( Status );
            setFormButtonsAttributes ( Status );
            Resolve ( 'Record data initialized' );
        })
        .catch (Error => {
            btn_update.setAttribute('disabled','');
            btn_deactivate.setAttribute('disabled','');
            btn_reactivate.setAttribute('disabled','');
            Reject ( 'Could not get record data, or no Id given' );
        });
    });
};

function setFormElementsValues ( Record ) {
    txt_UserId.value = Record.UserId;
    sel_AreaId.value = Record.AreaId;
    txt_Username.value = Record.Username;
    // ###################### ONLY FOR PASSWORD FIELD #########################
    psw_Password.value = 'DUMMYPASSWORD';
    // ###################### ONLY FOR PASSWORD FIELD #########################
    eml_Email.value = Record.Email;
    tel_PhoneNumber.value = Record.PhoneNumber;
    txt_FirstName.value = Record.FirstName;
    txt_LastName.value = Record.LastName;
    const UserStatusId = Number( Record.UserStatusId );
    switch ( UserStatusId ) {
        case 0:
            txt_UserStatus.value = 'Inactivo';
            break;
        case 1:
            txt_UserStatus.value = 'Activo';
            break;
        default:
            txt_UserStatus.value = 'Desconocido';
    };
};

function setFormElementsAvailability ( Status ) {
    if ( Status == 1 ) {
        sel_AreaId.removeAttribute( 'disabled' );
        txt_Username.removeAttribute( 'readonly' );
    } else {
        sel_AreaId.setAttribute('disabled', '');
        txt_Username.setAttribute('readonly', '');
    };
    // ###################### ONLY FOR PASSWORD FIELD #########################
    psw_Password.setAttribute('disabled', '');
    // ###################### ONLY FOR PASSWORD FIELD #########################
};

// Function to load categories (main) on select component
function loadAreas ( isPromise = true ) {
    return new Promise ( (Resolve, Reject) => {
        DEBUG_MODE ? console.log('BEGIN: Loading categories function') : true;
        let optionItem = null;

        let APIEndpoint = BASE_DIR + 'api/v1/index.php/areas';
        fetchData (APIEndpoint)
        .then (APIResponse => {
            DEBUG_MODE ? console.log('We got API response: ') : true;
            DEBUG_MODE ? console.log(APIResponse) : true;
            APIResponse.body.data.forEach(element => {
                optionItem = document.createElement('option');
                optionItem.value = element.AreaId;
                optionItem.innerHTML = element.AreaName;
                sel_AreaId.appendChild(optionItem);
            });
        })
        .then (Response => {
            DEBUG_MODE ? console.log('Loading successful!') : true;
        })
        .catch (error => {
            DEBUG_MODE ? console.error('* Loading Error') : true;
        })
        .finally (function () {
            return isPromise ? Resolve('END: Loading categories function') : true;
        });
    });
};

// ****************************************************************************
// *************************** FORM FUNCTIONS *********************************
// ****************************************************************************
function postCreateAssignments ( id = 0, isPromise = true ) {
    txt_UserId.value = id; // Returned from API
    txt_UserStatus.value = 'Active'; // Default for recently created records
    // ###################### ONLY FOR PASSWORD FIELD #########################
    psw_Password.setAttribute('disabled', '');
    // ###################### ONLY FOR PASSWORD FIELD #########################
    setFormButtonsAttributes ( 1 ); // Default for recently created record
    window.history.pushState("", "", window.location.href.split('?')[0] + '?UserId=' + id);
    return isPromise ? Promise.resolve('OK: postCreateAssignments finished!') : true;
};

function postDeactivateAssignments ( isPromise = true ) {
    txt_UserStatus.value = 'Inactive'; // Default for recently created records
    setFormElementsAvailability ( 0 ); // Disable all fields to prevent modifications
    setFormButtonsAttributes ( 0 ); // Set only for reactivation
    return isPromise ? Promise.resolve('OK: postDeactivateAssignments finished!') : true;
};

function postReactivateAssignments ( isPromise = true ) {
    txt_UserStatus.value = 'Active'; // Default for recently created records
    setFormElementsAvailability ( 1 ); // Enable all fields for modification
    setFormButtonsAttributes ( 1 ); // Activate all options
    return isPromise ? Promise.resolve('OK: postReactivateAssignments finished!') : true;
};

btn_create.addEventListener('click', function () {
    const APIMethod = 'POST';
    let JSONBody = null;
    let formMessage = '';
    formValidation()
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return FormData2JSONBody ( new FormData( Form ) );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        JSONBody = Response;
        return sendData(APIEndpoint, APIMethod, JSONBody);
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        return postCreateAssignments( Response.body.data.id );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        displayFormMessage( formMessage, 'ok' );
    })
    .catch(Error => {
        DEBUG_MODE ? console.error(Error) : false;
        displayFormMessage( Error, 'error' );
    });
});

btn_update.addEventListener('click', function () {
    const APIMethod = 'PUT';
    let JSONBody = null;
    formValidation()
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return FormData2JSONBody ( new FormData( Form ) );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        JSONBody = Response;
        return sendData( APIEndpoint, APIMethod, JSONBody );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        displayFormMessage( Response.body.msg, 'ok' );
    })
    .catch(error => {
        DEBUG_MODE ? console.error(error) : false;
        displayFormMessage( error, 'error' );
    });
});

btn_deactivate.addEventListener('click', function () {
    const APIMethod = 'PATCH';
    let formMessage = '';
    let JSONBody = {data: {Action: 'Deactivate'}};
    JSONBody.data[IdField] = Number( txt_UserId.value );
    JSONBody = JSON.stringify( JSONBody );
    
    sendData(APIEndpoint, APIMethod, JSONBody)
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        return postDeactivateAssignments( );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        displayFormMessage( formMessage, 'ok' );
    })
    .catch(error => {
        DEBUG_MODE ? console.error(error) : false;
        displayFormMessage( error, 'error' );
    });
});

btn_reactivate.addEventListener('click', function () {
    const APIMethod = 'PATCH';
    let formMessage = '';
    let JSONBody = {data: {Action: 'Reactivate'}};
    JSONBody.data[IdField] = Number( txt_UserId.value );
    JSONBody = JSON.stringify( JSONBody );
    
    sendData(APIEndpoint, APIMethod, JSONBody)
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        return postReactivateAssignments( );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        displayFormMessage( formMessage, 'ok' );
    })
    .catch(error => {
        DEBUG_MODE ? console.error(error) : false;
        displayFormMessage( error, 'error' );
    });
});

function formValidation () {
    return new Promise ((Resolve, Reject) => {
        validateSelect(sel_AreaId, sel_AreaId_Criteria)
        .then(Response => {
            DEBUG_MODE ? console.log('Area field validated') : true;
            return validateText(txt_Username, txt_Username_Criteria)
        })
        .then(Response => {
            DEBUG_MODE ? console.log('Username field validated') : true;
            return validateText(psw_Password, psw_Password_Criteria);
        })
        .then(Response => {
            DEBUG_MODE ? console.log('First name field validated') : true;
            return validateText(txt_FirstName, txt_FirstName_Criteria);
        })
        .then(Response => {
            DEBUG_MODE ? console.log('Last name field validated') : true;
            return validateText(txt_LastName, txt_LastName_Criteria);
        })
        .then(Response => {
            DEBUG_MODE ? console.log('Form validation successful') : true;
            Resolve('OK: Form validation successful');
        })
        .catch (Error => {
            DEBUG_MODE ? console.error(Error) : false;
            Reject('ERROR: Form validation failed');
        });
    });
};