'use strict';

// Module constants (To change in each module)
const Form = document.getElementById('frm_areas_crud');
const IdField = 'AreaId';
const StatusField = 'AreaStatusId';
const APIEndpoint = BASE_DIR + 'api/v1/index.php/areas';

// Form elements and criteria -------------------------------------------------
const txt_AreaId = document.getElementById('txt_AreaId');
const txt_AreaId_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": 0,
    "readOnly": true,
    "maxLength": 2
};

const txt_AreaName = document.getElementById('txt_AreaName');
const txt_AreaName_Criteria = {
    "fieldType": "text",
    "required": true,
    "maxLength": 48,
    "minLength": 4
};

const txt_AreaStatus = document.getElementById('txt_AreaStatus');
const txt_AreaStatus_Criteria = {
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
    setFormElementsAttributes()
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
    setHTMLAttributes(txt_AreaId, txt_AreaId_Criteria);
    setHTMLAttributes(txt_AreaName, txt_AreaName_Criteria);
    setHTMLAttributes(txt_AreaStatus, txt_AreaStatus_Criteria);
    DEBUG_MODE ? console.log('Loading successful!') : true;
    return isPromise ? Promise.resolve('END: Loading elements attributes function') : true;
};

function getRecordData () {
    return new Promise ( (Resolve, Reject) => {
        let Id = null;
        let Status = null;

        getEntityId ( IdField )
        .then (Response => {
            Id = Response;
            let singleAPIEndpoint = APIEndpoint + '/' + Id;
            return fetchData (singleAPIEndpoint);
        })
        .then (Response => {
            DEBUG_MODE ? console.log(Response) : true;
            Status = Response.body.data[Id][StatusField];
            setFormElementsValues ( Response.body.data[Id] );
            setFormElementsAvailability ( Status );
            setFormButtonsAttributes ( Status );
            Resolve ('Record data initialized');
        })
        .catch (error => {
            btn_update.setAttribute('disabled','');
            btn_deactivate.setAttribute('disabled','');
            btn_reactivate.setAttribute('disabled','');
            Reject ('Could not get record data, or no Id given');
        });
    });
};

function setFormElementsValues ( Record ) {
    txt_AreaId.value = Record.AreaId;
    txt_AreaName.value = Record.AreaName;
    const AreaStatusId = Number(Record.AreaStatusId);
    switch (AreaStatusId) {
        case 0:
            txt_AreaStatus.value = 'Inactivo';
            break;
        case 1:
            txt_AreaStatus.value = 'Activo';
            break;
        default:
            txt_AreaStatus.value = 'Desconocido';
    };
};

function setFormElementsAvailability ( Status ) {
    if ( Status == 1 ) {
        txt_AreaName.removeAttribute( 'readonly' );
    } else {
        txt_AreaName.setAttribute('readonly','');
    };
};

// ****************************************************************************
// *************************** FORM FUNCTIONS *********************************
// ****************************************************************************
function postCreateAssignments ( id = 0, isPromise = true ) {
    txt_AreaId.value = id; // Returned from API
    txt_AreaStatus.value = 'Active'; // Default for recently created records
    setFormButtonsAttributes ( 1 ); // Default for recently created record
    window.history.pushState("", "", window.location.href.split('?')[0] + '?AreaId=' + id);
    return isPromise ? Promise.resolve('OK: postCreateAssignments finished!') : true;
};

function postDeactivateAssignments ( isPromise = true ) {
    txt_AreaStatus.value = 'Inactive'; // Default for recently created records
    setFormElementsAvailability ( 0 ); // Disable all fields to prevent modifications
    setFormButtonsAttributes ( 0 ); // Set only for reactivation
    return isPromise ? Promise.resolve('OK: postDeactivateAssignments finished!') : true;
};

function postReactivateAssignments ( isPromise = true ) {
    txt_AreaStatus.value = 'Active'; // Default for recently created records
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
        return sendData(APIEndpoint, APIMethod, JSONBody);
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
    JSONBody.data[IdField] = Number( txt_AreaId.value );
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
    JSONBody.data[IdField] = Number( txt_AreaId.value );
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
        validateText(txt_AreaName, txt_AreaName_Criteria)
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