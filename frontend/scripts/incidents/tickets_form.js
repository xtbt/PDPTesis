'use strict'

// Module constants (To change in each module)
const Form = document.getElementById('frm_tickets_crud');
const IdField = 'TicketId';
const StatusField = 'TicketStatusId';
const APIEndpoint = BASE_DIR + 'api/v1/index.php/tickets';

// Form elements and criteria -------------------------------------------------
const txt_TicketId = document.getElementById('txt_TicketId');
const txt_TicketId_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": 0,
    "readOnly": true,
    "maxLength": 4
};

const sel_ProblemCategoryId = document.getElementById('sel_ProblemCategoryId');
const sel_ProblemCategoryId_Criteria = {
    "minIndex": 1
};

const sel_ProblemSubCategoryId = document.getElementById('sel_ProblemSubCategoryId');
const sel_ProblemSubCategoryId_Criteria = {
    "minIndex": 1
};

const txta_TicketDescription = document.getElementById('txta_TicketDescription');
const txta_TicketDescription_Criteria = {
    "fieldType": "text",
    "required": true,
    "cols": 40,
    "rows": 5
};

const txt_TicketStatus = document.getElementById('txt_TicketStatus');
const txt_TicketStatus_Criteria = {
    "fieldType": "text",
    "required": false,
    "defaultValue": "Nuevo",
    "readOnly": true,
    "maxLength": 14
};

// Image elements -------------------------------------------------------------
const mainImageFile = document.getElementById('mainImageFile');
const mainImage = document.getElementById('mainImage');

// Main form action buttons --------------------------------------------------------
const btn_create = document.getElementById('btn_create');
const btn_update = document.getElementById('btn_update');
const btn_deactivate = document.getElementById('btn_deactivate');
const btn_reactivate = document.getElementById('btn_reactivate');
// ------ Image Upload Button -------------------------------------------------
const btn_replaceMainImage = document.getElementById('btn_replaceMainImage');

// Page Data Initialization ---------------------------------------------------
window.addEventListener('load', function() {
    loadCategories()
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return setFormElementsAttributes();
    })
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
        // If an Id is found into the querystring, we get the record data, if not, blank form is displayed
        return getRecordData ();
    })
    .then (Response => {
        DEBUG_MODE ? console.log(Response) : true;
        loadElementsOnTable ();
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : false;
    });
});

function setFormElementsAttributes (isPromise = true) {
    DEBUG_MODE ? console.log('BEGIN: Loading elements attributes function') : true;
    setHTMLAttributes(txt_TicketId, txt_TicketId_Criteria);
    setHTMLAttributes(txta_TicketDescription, txta_TicketDescription_Criteria);
    setHTMLAttributes(txt_TicketStatus, txt_TicketStatus_Criteria);
    // BEGIN: Ticket Update Context -------------------------------------------
    setHTMLAttributes(txta_TicketUpdateNote, txta_TicketUpdateNote_Criteria);
    // END: Ticket Update Context ---------------------------------------------
    // BEGIN: Ticket Close Context -------------------------------------------
    setHTMLAttributes(txta_TicketLastComment, txta_TicketLastComment_Criteria);
    // END: Ticket Close Context ---------------------------------------------
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
            setFormButtonsAttributes ( Status, 'Tickets' );
            Resolve ('Record data initialized');
        })
        .catch (error => {
            DEBUG_MODE ? console.error(error) : true;
            setFormElementsAvailability ( -1 );
            setFormButtonsAttributes ( -1, 'Tickets' );
            Reject ('Could not get record data, or no Id given');
        });
    });
};

function setFormElementsValues ( Record ) {
    txt_TicketId.value = Record.TicketId;
    sel_ProblemCategoryId.value = Record.ProblemCategoryId;
    // BEGIN: Subroutine for selecting current ProblemSubCategory
    loadSubCategories ( Record.ProblemSubCategoryId ); // Async function
    // END: Subroutine for selecting current ProblemSubCategory
    txta_TicketDescription.value = Record.TicketDescription;
    // txt_TicketStatus.value = Record.TicketStatus == 1 ? 'Active' : 'Inactive';
    switch (Number(Record.TicketStatusId)) {
        case 0:
            txt_TicketStatus.value = 'Cancelado';
            break;
        case 1:
            txt_TicketStatus.value = 'Pendiente';
            break;
        case 2:
            txt_TicketStatus.value = 'En Progreso';
            break;
        case 3:
            txt_TicketStatus.value = 'Finalizado';
            lastComment.innerHTML = Record.TicketLastComment;
            break;
        default:
            txt_TicketStatus.value = 'Desconocido';
    };
    // BEGIN: Main Image Context ----------------------------------------------
    if (null != Record.TicketMainImage) {
        refreshImage ( mainImage, Record.TicketMainImage );
    } else {
        mainImage.src = BASE_DIR + 'media/fotos/no_image.png';
    };
    // END: Main Image Context ------------------------------------------------
    // BEGIN: Extra Image Context ---------------------------------------------
    if (null != Record.TicketExtraImage) {
        refreshCollection ( extraImageThumbnailCollection, Record.TicketExtraImage );
    };
    // END: Extra Image Context -----------------------------------------------
};

function setFormElementsAvailability ( Status ) {
    if ( Status == 0 ) {
        sel_ProblemCategoryId.setAttribute('disabled','');
        sel_ProblemSubCategoryId.setAttribute('disabled','');
        txta_TicketDescription.setAttribute('readonly','');

        txta_TicketUpdateNote.setAttribute('readonly','');
        setElementDisplayState(updatesFieldset, 'block');
        setElementDisplayState(extraImagesFieldset, 'block');
        setElementDisplayState(ticketCloseFieldset, 'none');
        setElementDisplayState(lastCommentFieldset, 'none');
    } else if ( Status == 1 ) {
        sel_ProblemCategoryId.removeAttribute( 'disabled' );
        sel_ProblemSubCategoryId.removeAttribute( 'disabled' );
        txta_TicketDescription.removeAttribute( 'readonly' );

        txta_TicketUpdateNote.removeAttribute( 'readonly' );
        setElementDisplayState(updatesFieldset, 'block');
        setElementDisplayState(extraImagesFieldset, 'none');
        setElementDisplayState(ticketCloseFieldset, 'none');
        setElementDisplayState(lastCommentFieldset, 'none');
    } else if ( Status == 2 ) {
        sel_ProblemCategoryId.removeAttribute( 'disabled' );
        sel_ProblemSubCategoryId.removeAttribute( 'disabled' );
        txta_TicketDescription.removeAttribute( 'readonly' );

        txta_TicketUpdateNote.removeAttribute( 'readonly' );
        setElementDisplayState(updatesFieldset, 'block');
        setElementDisplayState(extraImagesFieldset, 'block');
        setElementDisplayState(ticketCloseFieldset, 'block');
        setElementDisplayState(lastCommentFieldset, 'none');
    } else if ( Status == 3 ) {
        sel_ProblemCategoryId.setAttribute('disabled','');
        sel_ProblemSubCategoryId.setAttribute('disabled','');
        txta_TicketDescription.setAttribute('readonly','');

        txta_TicketUpdateNote.setAttribute('readonly','');
        setElementDisplayState(updatesFieldset, 'block');
        setElementDisplayState(extraImagesFieldset, 'block');
        setElementDisplayState(ticketCloseFieldset, 'none');
        setElementDisplayState(lastCommentFieldset, 'block');
    } else {
        sel_ProblemCategoryId.removeAttribute('disabled','');
        sel_ProblemSubCategoryId.removeAttribute('disabled','');
        txta_TicketDescription.removeAttribute('readonly','');

        txta_TicketUpdateNote.setAttribute('readonly','');
        setElementDisplayState(updatesFieldset, 'none');
        setElementDisplayState(extraImagesFieldset, 'none');
        setElementDisplayState(ticketCloseFieldset, 'none');
        setElementDisplayState(lastCommentFieldset, 'none');
    };
};

sel_ProblemCategoryId.addEventListener('change', function () {
    loadSubCategories();
});

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

// Function to load subcategories (secondary) on select component
function loadSubCategories ( ProblemSubCategoryId = -1 ) {
    DEBUG_MODE ? console.log('BEGIN: Loading subcategories function') : true;
    let optionItem = null;

    // INIT Subcategories select ----------------------------------------------
    sel_ProblemSubCategoryId.innerHTML = '';
    optionItem = document.createElement('option');
    optionItem.value = -1;
    optionItem.innerHTML = 'Selecciona una subcategor&#237;a';
    sel_ProblemSubCategoryId.appendChild(optionItem);
    // INIT Subcategories select ----------------------------------------------

    let JSONFilters = {
        "ProblemCategoryId": sel_ProblemCategoryId.options[sel_ProblemCategoryId.selectedIndex].value
    };

    let APIEndpoint = BASE_DIR + 'api/v1/index.php/problems-subcategories';
    fetchData (APIEndpoint, JSONFilters)
    .then (APIResponse => {
        DEBUG_MODE ? console.log('We got API response: ') : true;
        DEBUG_MODE ? console.log(APIResponse) : true;

        APIResponse.body.data.forEach(element => {
            optionItem = document.createElement('option');
            optionItem.value = element.ProblemSubCategoryId;
            optionItem.innerHTML = element.ProblemSubCategoryName;
            sel_ProblemSubCategoryId.appendChild(optionItem);
        });
        DEBUG_MODE ? console.log('Loading successful!') : true;
    })
    .then (Respuesta => {
        sel_ProblemSubCategoryId.value = ProblemSubCategoryId;
    })
    .catch (error => {
        DEBUG_MODE ? console.error('* Loading Error') : true;
    })
    .finally (function () {
        DEBUG_MODE ? console.log('END: Loading subcategories function') : true;
    });
};

// ****************************************************************************
// *************************** FORM FUNCTIONS *********************************
// ****************************************************************************
function postCreateAssignments ( id = 0, isPromise = true ) {
    txt_TicketId.value = id; // Returned from API
    txt_TicketStatus.value = 'Pendiente'; // Default for recently created records
    setFormElementsAvailability ( 1 ); // Enable update fields
    setFormButtonsAttributes ( 1 ); // Default for recently created record
    window.history.pushState("", "", window.location.href.split('?')[0] + '?TicketId=' + id);
    return isPromise ? Promise.resolve('OK: postCreateAssignments finished!') : true;
};

function postDeactivateAssignments ( isPromise = true ) {
    txt_TicketStatus.value = 'Cancelado'; // Default for deactivated records
    setFormElementsAvailability ( 0 ); // Disable all fields to prevent modifications
    setFormButtonsAttributes ( 0 ); // Set only for reactivation
    return isPromise ? Promise.resolve('OK: postDeactivateAssignments finished!') : true;
};

function postReactivateAssignments ( isPromise = true ) {
    txt_TicketStatus.value = 'Pendiente'; // Default for reactivated records
    setFormElementsAvailability ( 1 ); // Enable update fields for modification
    setFormButtonsAttributes ( 1 ); // Activate all options
    return isPromise ? Promise.resolve('OK: postReactivateAssignments finished!') : true;
};

btn_create.addEventListener('click', function () {
    const APIMethod = 'POST';
    let JSONBody = null;
    let formMessage = '';
    let createdId = null;
    formValidation()
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return FormData2JSONBody ( new FormData( Form ) );
    })
    .then(Response => { // Send fields data to API
        DEBUG_MODE ? console.log(Response) : true;
        JSONBody = Response;
        return sendData(APIEndpoint, APIMethod, JSONBody);
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        createdId = Response.body.data.id;
        return postCreateAssignments( createdId );
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        displayFormMessage( formMessage, 'ok' );
        return Promise.resolve('Continue with image upload');
    })
    .then(Response => { // Send main image to API
        DEBUG_MODE ? console.log(Response) : true;
        
        // File Check ---------------------------------------------------------
        const file = mainImageFile.files[0];
        // --------------------------------------------------------------------
        const JSONFilters = {
            "TicketId": createdId,
            "Context": "TicketMainImage"
        };

        if ( file ) 
            return uploadImage(APIEndpoint + '/uploadImage', file, JSONFilters);
        else
            return Promise.resolve('No image selected');
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        mainImageFile.value = '';
        let mainImageMessage = '';
        if (null != Response.body) {
            mainImageMessage = Response.body.msg;
            refreshImage(mainImage, Response.body.TicketMainImage);
        } else {
            mainImageMessage = Response;
            mainImage.src = BASE_DIR + 'media/fotos/no_image.png';
        };
        displayFormMessage( mainImageMessage, 'info', 'mainImageMessage' );
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
    let JSONBody = {data: {Action: 'Cancel'}};
    JSONBody.data[IdField] = Number( txt_TicketId.value );
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
    let JSONBody = {data: {Action: 'Reopen'}};
    JSONBody.data[IdField] = Number( txt_TicketId.value );
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

btn_replaceMainImage.addEventListener('click', function () {
    DEBUG_MODE ? console.log(mainImageFile) : true;
    // File Check ---------------------------------------------------------
    const file = mainImageFile.files[0];
    // --------------------------------------------------------------------
    const JSONFilters = {
        "TicketId": txt_TicketId.value,
        "Context": "TicketMainImage"
    };

    if ( file )
        uploadImage(APIEndpoint + '/uploadImage', file, JSONFilters)
        .then(Response => {
            displayFormMessage( Response.body.msg, 'ok', 'mainImageMessage' );
            refreshImage ( mainImage, Response.body.TicketMainImage );
            mainImageFile.value = '';
        })
        .catch(Error => {
            displayFormMessage( Error, 'error', 'mainImageMessage' );
        });
    else
        displayFormMessage( 'No se selecciono archivo', 'info', 'mainImageMessage' );
});

function formValidation () {
    return new Promise ((Resolve, Reject) => {
        validateSelect(sel_ProblemCategoryId, sel_ProblemCategoryId_Criteria)
        .then(Response => {
            return validateSelect(sel_ProblemSubCategoryId, sel_ProblemSubCategoryId_Criteria)
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

// ############################################################################
// ####################### Ticket Update Context ##############################
// ################# Might be placed in another file???? ######################
// ############################################################################
const updatesFieldset = document.getElementById('updateFieldset');

const txta_TicketUpdateNote = document.getElementById('txta_TicketUpdateNote');
const txta_TicketUpdateNote_Criteria = {
    "fieldType": "text",
    "required": true,
    "cols": 80,
    "rows": 5
};
// Ticket Update form action button -------------------------------------------
const btn_addUpdate = document.getElementById('btn_addUpdate');
// Table Elements -------------------------------------------------------------
const table = document.getElementById('tblTicketUpdates');
const tableDefaultMessage = document.getElementById('table-default_message');

function postUpdateSubmitAssignments ( isPromise = true ) {
    txta_TicketUpdateNote.value = ''; // Clear Note field
    txt_TicketStatus.value = 'En Progreso';
    setFormElementsAvailability ( 2 );
    setFormButtonsAttributes ( 2, 'Tickets' );
    loadElementsOnTable (); // Refresh elements on table
    return isPromise ? Promise.resolve('OK: postUpdateSubmitAssignments finished!') : true;
};

// Ticket Updates Form Button -------------------------------------------------
btn_addUpdate.addEventListener('click', function () {
    const APIMethod = 'POST';
    let formMessage = '';
    let JSONBody = null;
    ticketUpdateValidation()
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return FormData2JSONBody ( new FormData( Form ) );
    })
    .then(Response => { // Send fields data to API
        DEBUG_MODE ? console.log(Response) : true;
        JSONBody = Response;
        return sendData(APIEndpoint + '-updates', APIMethod, JSONBody);
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        displayFormMessage( formMessage, 'ok', 'TicketUpdateMessage' );
        
        if (txt_TicketStatus.value === 'Pendiente') {
            JSONBody = {data: {TicketStatusId: 2}};
            JSONBody.data[IdField] = Number( txt_TicketId.value );
            JSONBody = JSON.stringify( JSONBody );
            return sendData(APIEndpoint + '/changeStatus', 'PATCH', JSONBody);
        };
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return postUpdateSubmitAssignments ();
    })
    .catch(Error => {
        DEBUG_MODE ? console.error(Error) : false;
        displayFormMessage( Error, 'error', 'TicketUpdateMessage' );
    });
});

// Function to validate ticket updates form -----------------------------------
function ticketUpdateValidation () {
    return new Promise ((Resolve, Reject) => {
        validateTextArea(txta_TicketUpdateNote, txta_TicketUpdateNote_Criteria)
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

// Function to fill ticket updates table --------------------------------------
function loadElementsOnTable () {
    let updatesAPIEndpoint = APIEndpoint + '-updates';
    let JSONFilters = {
        "TicketId": txt_TicketId.value,
        "TicketUpdateStatusId": 1
    };

    initTable (table)
    .then (Response => {
        DEBUG_MODE ? console.log('1) Table initialized... ') : true;
        return prepareTable (table, 'Obteniendo datos...');
    })
    .then (Response => {
        DEBUG_MODE ? console.log('2) Table prepared... ') : true;
        return fetchData (updatesAPIEndpoint, JSONFilters);
    })
    .then (APIResponse => {
        DEBUG_MODE ? console.log('3) API data acquired: ') : true;
        DEBUG_MODE ? console.log(APIResponse) : true;

        let tableRow, tableRowCell;
        APIResponse.body.data.forEach(element => {
            DEBUG_MODE ? console.log(element) : true;
            tableRow = table.insertRow(-1);
            // TicketUpdateId
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerHTML = element.TicketUpdateId;
            // TicketUpdateDateTime
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerText = element.TicketUpdateDateTime;
            // TicketUpdateNote
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerText = element.TicketUpdateNote;
            // UserId/Username
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerText = '[' + element.UserId + ']' + element.Username;
        });
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : true;
    })
    .then(() => {
        tableDefaultMessage.innerText = ''; // We get rid of the default message
    });
};
// ############################################################################

// ############################################################################
// ######################## Extra Images Context ##############################
// ################# Might be placed in another file???? ######################
// ############################################################################
const extraImagesFieldset = document.getElementById('extraImagesFieldset');
// Image elements -------------------------------------------------------------
const extraImageFile = document.getElementById('extraImageFile');
const extraImageThumbnailCollection = document.getElementById('extraImageThumbnailCollection');
// ------ Image Upload Button -------------------------------------------------
const btn_addExtraImage = document.getElementById('btn_addExtraImage');

btn_addExtraImage.addEventListener('click', function () {
    DEBUG_MODE ? console.log(extraImageFile) : true;
    // File Check ---------------------------------------------------------
    const file = extraImageFile.files[0];
    // --------------------------------------------------------------------
    const JSONFilters = {
        "TicketId": txt_TicketId.value,
        "Context": "TicketExtraImage"
    };

    if ( file )
        uploadImage(APIEndpoint + '/uploadImage', file, JSONFilters)
        .then(Response => {
            displayFormMessage( Response.body.msg, 'ok', 'extraImageMessage' );
            DEBUG_MODE ? console.log(Response) : true;
            refreshCollection ( extraImageThumbnailCollection, Response.body.TicketExtraImage );
            extraImageFile.value = '';
        })
        .catch(Error => {
            displayFormMessage( Error, 'error', 'extraImageMessage' );
        });
    else
        displayFormMessage( 'No se selecciono archivo', 'info', 'extraImageMessage' );
});
// ############################################################################

// ############################################################################
// ######################## Ticket Close Context ##############################
// ################# Might be placed in another file???? ######################
// ############################################################################
const ticketCloseFieldset = document.getElementById('ticketCloseFieldset');

const lastCommentFieldset = document.getElementById('lastCommentFieldset');
const lastComment = document.getElementById('lastComment');
// ------ Ticket close form fields --------------------------------------------
const txta_TicketLastComment = document.getElementById('txta_TicketLastComment');
const txta_TicketLastComment_Criteria = {
    "fieldType": "text",
    "required": true,
    "cols": 80,
    "rows": 5
};

// Function to post-ticket close instructions ---------------------------------
function postTicketCloseAssignments ( isPromise = true ) {
    txta_TicketLastComment.value = ''; // Clear Note field
    txt_TicketStatus.value = 'Finalizado';
    setFormElementsAvailability ( 3 );
    setFormButtonsAttributes ( 3, 'Tickets' );
    return isPromise ? Promise.resolve('OK: postTicketCloseAssignments finished!') : true;
};

// ------ Ticket close form button --------------------------------------------
const btn_closeTicket = document.getElementById('btn_closeTicket');

btn_closeTicket.addEventListener('click', function () {
    const APIMethod = 'PATCH';
    let formMessage = '';
    let JSONBody = null;
    const lastCommentText = txta_TicketLastComment.value;
    ticketCloseValidation()
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        return FormData2JSONBody ( new FormData( Form ) );
    })
    .then(Response => { // Send fields data to API
        DEBUG_MODE ? console.log(Response) : true;
        JSONBody = Response;
        return sendData(APIEndpoint + '/closeTicket', APIMethod, JSONBody);
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        formMessage = Response.body.msg;
        displayFormMessage( formMessage, 'ok', 'ticketCloseMessage' );
        return postTicketCloseAssignments ();
    })
    .then(Response => {
        DEBUG_MODE ? console.log(Response) : true;
        lastComment.innerHTML = lastCommentText;
    })
    .catch(Error => {
        DEBUG_MODE ? console.error(Error) : false;
        displayFormMessage( Error, 'error', 'ticketCloseMessage' );
    });
});

// Function to validate ticket close form -------------------------------------
function ticketCloseValidation () {
    return new Promise ((Resolve, Reject) => {
        validateTextArea(txta_TicketLastComment, txta_TicketLastComment_Criteria)
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
// ############################################################################