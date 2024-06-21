'use strict';

// Module constants (To change in each module)
const Form = document.getElementById('frm_problems_subcategories_crud');
const IdField = 'ProblemSubCategoryId';
const StatusField = 'ProblemSubCategoryStatusId';
const APIEndpoint = BASE_DIR + 'api/v1/index.php/problems-subcategories';

// Form elements and criteria -------------------------------------------------
const txt_ProblemSubCategoryId = document.getElementById('txt_ProblemSubCategoryId');
const txt_ProblemSubCategoryId_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": 0,
    "readOnly": true,
    "maxLength": 2
};

const sel_ProblemCategoryId = document.getElementById('sel_ProblemCategoryId');
const sel_ProblemCategoryId_Criteria = {
    "minIndex": 1
};

const txt_ProblemSubCategoryName = document.getElementById('txt_ProblemSubCategoryName');
const txt_ProblemSubCategoryName_Criteria = {
    "fieldType": "text",
    "required": true,
    "maxLength": 48,
    "minLength": 4
};

const txt_ProblemSubCategoryStatus = document.getElementById('txt_ProblemSubCategoryStatus');
const txt_ProblemSubCategoryStatus_Criteria = {
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
    loadCategories()
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
    setHTMLAttributes(txt_ProblemSubCategoryId, txt_ProblemSubCategoryId_Criteria);
    setHTMLAttributes(sel_ProblemCategoryId, sel_ProblemCategoryId_Criteria);
    setHTMLAttributes(txt_ProblemSubCategoryName, txt_ProblemSubCategoryName_Criteria);
    setHTMLAttributes(txt_ProblemSubCategoryStatus, txt_ProblemSubCategoryStatus_Criteria);
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
    txt_ProblemSubCategoryId.value = Record.ProblemSubCategoryId;
    sel_ProblemCategoryId.value = Record.ProblemCategoryId;
    txt_ProblemSubCategoryName.value = Record.ProblemSubCategoryName;
    const ProblemSubCategoryStatusId = Number(Record.ProblemSubCategoryStatusId);
    switch (ProblemSubCategoryStatusId) {
        case 0:
            txt_ProblemSubCategoryStatus.value = 'Inactivo';
            break;
        case 1:
            txt_ProblemSubCategoryStatus.value = 'Activo';
            break;
        default:
            txt_ProblemSubCategoryStatus.value = 'Desconocido';
    };
};

function setFormElementsAvailability ( Status ) {
    if ( Status == 1 ) {
        sel_ProblemCategoryId.removeAttribute( 'disabled' );
        txt_ProblemSubCategoryName.removeAttribute( 'readonly' );
    } else {
        sel_ProblemCategoryId.setAttribute('disabled', '');
        txt_ProblemSubCategoryName.setAttribute('readonly', '');
    };
};

// Function to load categories (main) on select component
function loadCategories (isPromise = true) {
    return new Promise ( (Resolve, Reject) => {
        DEBUG_MODE ? console.log('BEGIN: Loading categories function') : true;
        let optionItem = null;

        let APIEndpoint = BASE_DIR + 'api/v1/index.php/problems-categories';
        fetchData (APIEndpoint)
        .then (APIResponse => {
            DEBUG_MODE ? console.log('We got API response: ') : true;
            DEBUG_MODE ? console.log(APIResponse) : true;
            APIResponse.body.data.forEach(element => {
                optionItem = document.createElement('option');
                optionItem.value = element.ProblemCategoryId;
                optionItem.innerHTML = element.ProblemCategoryName;
                sel_ProblemCategoryId.appendChild(optionItem);
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
    txt_ProblemSubCategoryId.value = id; // Returned from API
    txt_ProblemSubCategoryStatus.value = 'Active'; // Default for recently created records
    setFormButtonsAttributes ( 1 ); // Default for recently created record
    window.history.pushState("", "", window.location.href.split('?')[0] + '?ProblemSubCategoryId=' + id);
    return isPromise ? Promise.resolve('OK: postCreateAssignments finished!') : true;
};

function postDeactivateAssignments ( isPromise = true ) {
    txt_ProblemSubCategoryStatus.value = 'Inactive'; // Default for recently created records
    setFormElementsAvailability ( 0 ); // Disable all fields to prevent modifications
    setFormButtonsAttributes ( 0 ); // Set only for reactivation
    return isPromise ? Promise.resolve('OK: postDeactivateAssignments finished!') : true;
};

function postReactivateAssignments ( isPromise = true ) {
    txt_ProblemSubCategoryStatus.value = 'Active'; // Default for recently created records
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
    JSONBody.data[IdField] = Number( txt_ProblemSubCategoryId.value );
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
    JSONBody.data[IdField] = Number( txt_ProblemSubCategoryId.value );
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
        validateSelect(sel_ProblemCategoryId, sel_ProblemCategoryId_Criteria)
        .then(Response => {
            DEBUG_MODE ? console.log('Category field validation successful') : true;
            validateText(txt_ProblemSubCategoryName, txt_ProblemSubCategoryName_Criteria)
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