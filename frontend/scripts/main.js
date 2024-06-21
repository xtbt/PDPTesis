// ****************************************************************************
// **************************** XHR FUNCTIONS *********************************
// ****************************************************************************
const globalIntervals =  new Array();
// Function to create a new XHR object in compatibililty mode -----------------
function createXMLHttpRequest() {
    let XHR = null;
    if (window.XMLHttpRequest)
        XHR = new XMLHttpRequest();
    else if (window.ActiveXObject)
        XHR = new ActiveXObject("Microsoft.XMLHTTP");
    else
        XHR = null;
    return XHR;
};

// Function to build a JSONCriteriaQueryString from a JSONCriteria ------------------------
function buildJSONCriteriaQueryString(JSONCriteria) {
    let JSONCriteriaQueryString = '';
    for (const key in JSONCriteria) {
        if (key.includes('Status') && JSONCriteria[key] < 0)
            continue;
        else if (key.includes('Criteria') && JSONCriteria[key] == '')
            continue;
        else
            JSONCriteriaQueryString += ('' != JSONCriteriaQueryString ? '&' : '?') + key + '=' + JSONCriteria[key];
    };
    // ##################### AUTHORIZATION CONTEXT ############################
    const appUserId = sessionStorage.getItem('appUserId');
    JSONCriteriaQueryString += ('' != JSONCriteriaQueryString ? '&' : '?') + 'appUserId=' + appUserId;
    // ##################### AUTHORIZATION CONTEXT ############################
    return JSONCriteriaQueryString;
};

// Function to remove prefix from HTML fields ---------------------------------
function removeHTMLPrefix ( elementName ) {
    if ( !isNullText( elementName ) ) {
        let index = 0;
        index = elementName.indexOf('_') + 1;
        elementName = elementName.substring(index);
    }
    return elementName;
};

// Function to build JSONBody from formData object ----------------------------
function FormData2JSONBody ( formDataObject ) {
    return new Promise ((Resolve, Reject) => {
        let JSONBody = {data:{}};
        if (null != formDataObject) {
            formDataObject.forEach( function (value, key) {
                key = removeHTMLPrefix( key );
                JSONBody.data[key] = value;
            });
            Resolve(JSON.stringify(JSONBody));
        } else {
            Reject('ERROR: Invalid FormData');
        };
    });
};

// Function to get DATA from an API -------------------------------------------
function fetchData (APIEndpoint, JSONFilters = null) {
    const XHR = createXMLHttpRequest();
    let JSONResponse = null;

    //JSONCriteriaQueryString = null != JSONFilters ? buildJSONCriteriaQueryString(JSONFilters) : '';
    JSONCriteriaQueryString = buildJSONCriteriaQueryString(JSONFilters);
    DEBUG_MODE ? console.log('queryString: ' + JSONCriteriaQueryString) : true;

    // ##################### AUTHORIZATION CONTEXT ############################
    const appBearerToken = sessionStorage.getItem('appBearerToken');
    // ##################### AUTHORIZATION CONTEXT ############################
    
    return new Promise ((Resolve, Reject) => {
        XHR.onload = function() {
            // ##################### AUTHORIZATION CONTEXT ############################
            //DEBUG_MODE ? console.log(XHR.response) : true;
            if ('Auth' in XHR.response.body)
                sessionStorage.setItem('appBearerToken', XHR.response.body.Auth.Token);
            // ##################### AUTHORIZATION CONTEXT ############################

            if (XHR.status == 200) {
                JSONResponse = XHR.response;
                Resolve(JSONResponse);
            } else {
                DEBUG_MODE ? console.log(XHR.response) : true;
                Reject('Not 200 response: ' + APIEndpoint);
            };
        };
        XHR.onerror = function() {
            Reject('We got an API error: ' + APIEndpoint);
        };
        /* Sólo para sitios con certificado SSL -------------------
        //url.searchParams.set("valor",criterioBusqueda); // GET
        //url.searchParams.set("campo",json_criterios.campoBD); // GET
        //url.searchParams.set("tabla",json_criterios.tablaBD); // GET
        //XHR.open("GET", url, true);
        -------------------------------------------------------- */
        XHR.open("GET", APIEndpoint + ('' != JSONCriteriaQueryString ? JSONCriteriaQueryString : ''), true); // TEMPORAL METHOD (NOT SSL)
        XHR.responseType = "json";
        XHR.timeout = 5000;
        XHR.setRequestHeader("Authorization", "Bearer " + appBearerToken); // AUTHORIZATION CONTEXT
        XHR.send(null);
    });
};

// Function to send DATA to an API --------------------------------------------
function sendData (APIEndpoint, APIMethod, JSONBody, JSONFilters = null) {
    const XHR = createXMLHttpRequest();
    let JSONResponse = null;

    //JSONCriteriaQueryString = null != JSONFilters ? buildJSONCriteriaQueryString(JSONFilters) : '';
    JSONCriteriaQueryString = buildJSONCriteriaQueryString(JSONFilters);
    DEBUG_MODE ? console.log('queryString: ' + JSONCriteriaQueryString) : true;

    // ##################### AUTHORIZATION CONTEXT ############################
    const appBearerToken = sessionStorage.getItem('appBearerToken');
    // ##################### AUTHORIZATION CONTEXT ############################
    
    return new Promise ((Resolve, Reject) => {
        XHR.onload = function() {
            // ##################### AUTHORIZATION CONTEXT ############################
            if ('Auth' in XHR.response.body)
                sessionStorage.setItem('appBearerToken', XHR.response.body.Auth.Token);
            // ##################### AUTHORIZATION CONTEXT ############################

            if (XHR.status == 200) {
                JSONResponse = XHR.response;
                Resolve(JSONResponse);
            } else {
                Reject(XHR.response.body.error);
            };
        };
        XHR.onerror = function() {
            // DEBUG_MODE ? console.log('We got an API error: ' + APIEndpoint) : true;
            Reject('We got an API error: ' + APIEndpoint);
        };
        /* Sólo para sitios con certificado SSL -------------------
        //url.searchParams.set("valor",criterioBusqueda); // GET
        //url.searchParams.set("campo",json_criterios.campoBD); // GET
        //url.searchParams.set("tabla",json_criterios.tablaBD); // GET
        //XHR.open("GET", url, true);
        -------------------------------------------------------- */
        XHR.open(APIMethod, APIEndpoint + ('' != JSONCriteriaQueryString ? JSONCriteriaQueryString : ''), true); // TEMPORAL METHOD (NOT SSL)
        XHR.responseType = "json";
        XHR.timeout = 5000;
        //XHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // POST
        XHR.setRequestHeader("Authorization", "Bearer " + appBearerToken); // AUTHORIZATION CONTEXT
        XHR.send(JSONBody);
    });
};

function uploadImage (APIEndpoint, file, JSONFilters = null) {
    const XHR = createXMLHttpRequest();
    let JSONResponse = null;

    DEBUG_MODE ? console.log(file) : true;
    JSONCriteriaQueryString = buildJSONCriteriaQueryString(JSONFilters);
    DEBUG_MODE ? console.log('queryString: ' + JSONCriteriaQueryString) : true;

    // ##################### AUTHORIZATION CONTEXT ############################
    const appBearerToken = sessionStorage.getItem('appBearerToken');
    // ##################### AUTHORIZATION CONTEXT ############################
    
    return new Promise ((Resolve, Reject) => {
            const imageObject = new FormData();
            imageObject.append('image', file);

            XHR.onload = function() {
                // ##################### AUTHORIZATION CONTEXT ############################
                // if ('Auth' in XHR.response.body)
                //     sessionStorage.setItem('appBearerToken', XHR.response.body.Auth.Token);
                // ##################### AUTHORIZATION CONTEXT ############################

                if (XHR.status == 200) {
                    JSONResponse = XHR.response;
                    //DEBUG_MODE ? console.log(XHR.response) : true;
                    Resolve(JSONResponse);
                } else {
                    //DEBUG_MODE ? console.log(XHR.response) : true;
                    Reject(XHR.response.body.msg);
                };
            };

            XHR.onerror = function() {
                Reject('We got an API error: ' + APIEndpoint);
            };
            
            XHR.open('POST', APIEndpoint + ('' != JSONCriteriaQueryString ? JSONCriteriaQueryString : ''), true); // TEMPORAL METHOD (NOT SSL)
            XHR.responseType = "json";
            XHR.timeout = 10000;
            //XHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // POST
            XHR.setRequestHeader("Authorization", "Bearer " + appBearerToken); // AUTHORIZATION CONTEXT
            XHR.setRequestHeader("Cache-Control", "no-cache, max-age=0, must-revalidate, no-store"); // FOR IMAGE REFRESHING
            XHR.send(imageObject);
    });
}

// *****************************************************************************
// *********************** TEXT/STRING FUNCTIONS *******************************
// *****************************************************************************
function isNullText(value) {
    if (value.match(/^\s*$/) || value==null || value=="") {
      return true;
    }
    else {
      return false;
    };
};

// *****************************************************************************
// *********************** PAGINATION FUNCTIONS ********************************
// *****************************************************************************
function initPaginationData () {
    const paginationTotalItems = document.getElementById('pagination-total_items');
    const paginationItemsPerPage = document.getElementById('pagination-items_per_page');
    const paginationPageList = document.getElementById('pagination-page_list');
    
    paginationTotalItems.innerText = '';
    paginationItemsPerPage.innerText = '';
    paginationPageList.innerHTML = '';
};

function getCurrentPage (isPromise = true) {
    let paramString = window.location.href.split('?')[1];
    let queryString = new URLSearchParams(paramString);
    for (let pair of queryString.entries()) {
        if (pair[0] == 'Page') {
            return isPromise ? Promise.resolve(pair[1]) : pair[1];
        };
    };
    return isPromise ? Promise.resolve(1) : 1;
};

function buildCurrentPageURL (pageNumber, JSONFilters) {
    let URL = window.location.href.split('?')[0];

    // Delete current limit query param from URL ------------------------------
    delete JSONFilters['limit'];
    JSONFilters.Page = pageNumber;

    let queryString = buildJSONCriteriaQueryString(JSONFilters);

    let currentPageURL = URL + queryString;

    return currentPageURL;
}

function displayPaginationData ( globalCount, JSONFilters ) {
    // Pagination Elements --------------------------------------------------------
    const paginationTotalItems = document.getElementById('pagination-total_items');
    const paginationItemsPerPage = document.getElementById('pagination-items_per_page');
    const paginationPageList = document.getElementById('pagination-page_list');
    
    paginationTotalItems.innerText = globalCount;
    paginationItemsPerPage.innerText = ITEMS_PER_PAGE;
    paginationPageList.innerHTML = '';

    if (globalCount > 0) {
        const pageQuantity = Math.ceil(globalCount / ITEMS_PER_PAGE);
        for (let i = 0 ; i < pageQuantity ; i ++) {
            anchor = document.createElement('a');
                anchor.href = buildCurrentPageURL( i+1, JSONFilters);
                anchor.innerHTML = i+1;
            paginationPageList.appendChild(anchor);
        }
    };
};

// *****************************************************************************
// ************************** TABLE FUNCTIONS **********************************
// *****************************************************************************
function initTable (table, isPromise = true) {
    if (null != table && table.nodeName == 'TABLE') {
        const tableLength = table.rows.length;
        for (let i = 2 ; i<tableLength ; i++)
        table.deleteRow(2); // Leave only the headers
        return isPromise ? Promise.resolve('OK') : true;
    }
    else
        return isPromise ? Promise.reject('ERROR') : false;
};

function prepareTable (table, prepareMessage = 'Gathering data...', isPromise = true) {
    if (null != table && table.nodeName == 'TABLE') {
        // let table_row = table.insertRow(-1);
        // let table_cell = table_row.insertCell(-1);
        // table_cell.colSpan = table.rows[0].cells.length;
        // table_cell.innerText = prepareMessage;
        const messageRow = document.getElementById('table-default_message');
        messageRow.innerText = prepareMessage;
        return isPromise ? Promise.resolve('OK') : true;
    }
    else {
        return isPromise ? Promise.reject('ERROR') : false;
    };
};

// *****************************************************************************
// ***************************** DATE FUNCTIONS ********************************
// *****************************************************************************

// Format date (Mostly used for Safari Browser Compatibility) -----------------
function formatDate(date, dateFormat) {
    let formattedDate = date.toString();
    if (dateFormat == "YYYY-MM-DD") {
        formattedDate = date.getFullYear() + "-" + ("0" + (date.getMonth()+1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2);
    }
    else if (dateFormat == "DD/MM/YYYY") {
        formattedDate = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth()+1)).slice(-2) + "/" + date.getFullYear();
    }
    return formattedDate;
};

// *****************************************************************************
// *************** FORM INITIALIZATION/VALIDATION FUNCTIONS ********************
// *****************************************************************************

// Function to set display state ----------------------------------------------
function setElementDisplayState (element, displayState) {
    element.style.display = displayState;
};

// Function to initialize Form Elements ---------------------------------------
function setHTMLAttributes(element, JSONAttributes) {
    if ( null != JSONAttributes.required )
        element.setAttribute('required',''); // Set required attribute
    if ( null != JSONAttributes.defaultValue )
        element.value = JSONAttributes.defaultValue; // Set default value
    if ( null != JSONAttributes.readOnly )
        element.setAttribute('readonly',''); // Set readonly attibute
    if ( null != JSONAttributes.maxLength && ( !Number.isNaN( Number(JSONAttributes.maxLength) ) ) ) {
        if ( element.nodeName == 'INPUT' && (
        element.type == 'text' || 
        element.type == 'password' || 
        element.type == 'email' || 
        element.type == 'tel') ) {
            element.maxLength = JSONAttributes.maxLength;
            element.size = JSONAttributes.maxLength + 2;
        };
    };
    if ( ( null != JSONAttributes.cols ) && ( !Number.isNaN( Number(JSONAttributes.cols) ) ) ) {
        if ( element.nodeName == 'TEXTAREA' ) {
            element.cols = JSONAttributes.cols;
        };
    };
    if ( ( null != JSONAttributes.rows ) && ( !Number.isNaN( Number(JSONAttributes.rows) ) ) ) {
        if ( element.nodeName == 'TEXTAREA' ) {
            element.rows = JSONAttributes.rows;
        };
    };
    if ( ( null != JSONAttributes.minValue ) && ( !Number.isNaN( Number(JSONAttributes.minValue) ) ) ) {
        if ( element.nodeName == 'INPUT' && element.type == 'number' ) {
            element.min = JSONAttributes.minValue;
        };
    };
    if ( ( null != JSONAttributes.maxValue ) && ( !Number.isNaN( Number(JSONAttributes.maxValue) ) ) ) {
        if ( element.nodeName == 'INPUT' && element.type == 'number' ) {
            element.max = JSONAttributes.maxValue;
        };
    };
    if ( ( null != JSONAttributes.valueStep ) && ( !Number.isNaN( Number(JSONAttributes.valueStep) ) ) ) {
        if ( element.nodeName == 'INPUT' && element.type == 'number' ) {
            element.step = JSONAttributes.valueStep;
        };
    };
    DEBUG_MODE ? console.log('Attributes configured for ' + element.name) : true;
};

function setFormButtonsAttributes ( Status, Context = null ) {
    if (Status == 0) {
        btn_create.setAttribute('disabled', '');
        btn_deactivate.setAttribute('disabled', '');
        btn_update.setAttribute('disabled', '');
        btn_reactivate.removeAttribute('disabled', '');
        if (Context == 'Tickets') {
            mainImageFile.setAttribute('disabled', '');
            btn_replaceMainImage.setAttribute('disabled', '');
            btn_addUpdate.setAttribute('disabled', '');
            extraImageFile.setAttribute('disabled', '');
            btn_addExtraImage.setAttribute('disabled', '');
            btn_closeTicket.setAttribute('disabled', '');
        };
    } else if (Status == 1) {
        btn_create.setAttribute('disabled', '');
        btn_reactivate.setAttribute('disabled', '');
        btn_deactivate.removeAttribute('disabled', '');
        btn_update.removeAttribute('disabled', '');
        if (Context == 'Tickets') {
            mainImageFile.removeAttribute('disabled', '');
            btn_replaceMainImage.removeAttribute('disabled', '');
            btn_addUpdate.removeAttribute('disabled', '');
            extraImageFile.setAttribute('disabled', '');
            btn_addExtraImage.setAttribute('disabled', '');
            btn_closeTicket.setAttribute('disabled', '');
        };
    } else if (Status == 2) {
        btn_create.setAttribute('disabled', '');
        btn_reactivate.setAttribute('disabled', '');
        btn_deactivate.removeAttribute('disabled', '');
        btn_update.setAttribute('disabled', '');
        if (Context == 'Tickets') {
            mainImageFile.setAttribute('disabled', '');
            btn_replaceMainImage.setAttribute('disabled', '');
            btn_addUpdate.removeAttribute('disabled', '');
            extraImageFile.removeAttribute('disabled', '');
            btn_addExtraImage.removeAttribute('disabled', '');
            btn_closeTicket.removeAttribute('disabled', '');
        };
    } else if (Status == 3) {
        btn_create.setAttribute('disabled', '');
        btn_reactivate.removeAttribute('disabled', '');
        btn_deactivate.setAttribute('disabled', '');
        btn_update.setAttribute('disabled', '');
        if (Context == 'Tickets') {
            mainImageFile.setAttribute('disabled', '');
            btn_replaceMainImage.setAttribute('disabled', '');
            btn_addUpdate.setAttribute('disabled', '');
            extraImageFile.setAttribute('disabled', '');
            btn_addExtraImage.setAttribute('disabled', '');
            btn_closeTicket.setAttribute('disabled', '');
        };
    } else { // New Record
        // btn_create.removeAttribute('disabled', '');
        btn_deactivate.setAttribute('disabled', '');
        btn_update.setAttribute('disabled', '');
        btn_reactivate.setAttribute('disabled', '');
        if (Context == 'Tickets') {
            mainImageFile.removeAttribute('disabled', '');
            btn_replaceMainImage.setAttribute('disabled', '');
        };
    };
};

// Function to validate a Select Element --------------------------------------
function validateSelect(element, JSONCriteria = null, isPromise = true) {
    const selectedIndex = element.selectedIndex;
    if (null != JSONCriteria && selectedIndex >= JSONCriteria.minIndex)
        return isPromise ? Promise.resolve('OK') : true;
    else if (selectedIndex > 0)
        return isPromise ? Promise.resolve('OK') : true;
    else
        return isPromise ? Promise.reject('ERROR: Invalid Option in '+element.name) : false;
};

// Function to validate a Input:Text Element ----------------------------------
function validateText(element, JSONCriteria = null, isPromise = true) {
    const value = element.value;
    if (null != JSONCriteria) {
        // "Required" field validation -----------------------------------------
        if ( JSONCriteria.required ) {
            if ( isNullText( value ) )
                return isPromise ? Promise.reject('Error: Field required and empty for ' + element.name) : false;
        };
        // "MinLength" field validation ---------------------------------------
        if ( ( null != JSONCriteria.minLength ) && ( !Number.isNaN( Number(JSONCriteria.minLength) ) ) ) {
            if ( value.length < JSONCriteria.minLength )
                return isPromise ? Promise.reject('Error: Field minimum lenght not fulfilled for ' + element.name) : false;
        };
        // If everything is OK ------------------------------------------------
        return isPromise ? Promise.resolve('OK: Field successfully validated: ' + element.name) : true;
    } else {
        return isPromise ? Promise.resolve('OK: No criteria defined for ' + element.name) : false;
    };
};

function validateTextArea(element, JSONCriteria = null, isPromise = true) {
    const value = element.value;
    if (null != JSONCriteria) {
        // "Required" field validation -----------------------------------------
        if ( JSONCriteria.required ) {
            if ( isNullText( value ) )
                return isPromise ? Promise.reject('Error: Field required and empty for ' + element.name) : false;
        };
        // If everything is OK ------------------------------------------------
        return isPromise ? Promise.resolve('OK: Field successfully validated: ' + element.name) : true;
    } else {
        return isPromise ? Promise.resolve('OK: No criteria defined for ' + element.name) : false;
    };
};

// Function to validate a Input:Number Element --------------------------------
function validateNumber(element, JSONCriteria = null, isPromise = true) {
    const value = Number(element.value);
    if (null != JSONCriteria) {
        // "Required" field validation -----------------------------------------
        if ( JSONCriteria.required ) {
            if ( isNullText( element.value ) ) // We check it first as pure TEXT
                return isPromise ? Promise.reject('Error: Field required and empty for ' + element.name) : false;
        };
        // Value integrity validation -----------------------------------------
        if ( Number.isNaN( value ) ) {
            return isPromise ? Promise.reject('Error: Invalid input for ' + element.name) : false;
        };
        if ( ( null != JSONCriteria.minValue ) && ( !Number.isNaN( Number(JSONCriteria.minValue) ) ) && !Number.isNaN( value ) && value < JSONCriteria.minValue ) {
            return isPromise ? Promise.reject('Error: Field minimum value not fulfilled for ' + element.name) : false;
        };
        if ( ( null != JSONCriteria.maxValue ) && ( !Number.isNaN( Number(JSONCriteria.maxValue) ) ) && !Number.isNaN( value ) && value > JSONCriteria.maxValue ) {
            return isPromise ? Promise.reject('Error: Field maximum value not fulfilled for ' + element.name) : false;
        };
        // If everything is OK ------------------------------------------------
        return isPromise ? Promise.resolve('OK: Field successfully validated: ' + element.name) : true;
    } else {
        return isPromise ? Promise.resolve('OK: No criteria defined for ' + element.name) : false;
    };
};

// *****************************************************************************
// ******************* FORM DATA INITIALIZATION FUNCTIONS **********************
// *****************************************************************************

// Function to get the current entity Main Id ----------------------------------
function getEntityId (IdField = null, isPromise = true) {
        //if ( IdField == null ) Reject ('Error: No Id Field provided');

        let paramString = window.location.href.split('?')[1];
        let queryString = new URLSearchParams(paramString);
        for (let pair of queryString.entries()) {
            if (pair[0] == IdField) {
                return isPromise ? Promise.resolve ( pair[1] ) : pair[1];
            };
        };
        return isPromise ? Promise.reject ('Error: Id Field not provided or no Id present') : false;
};

// *****************************************************************************
// *********************** DYNAMIC MESSAGES FUNCTIONS **************************
// *****************************************************************************

// Function to display a message after a form action --------------------------
function displayFormMessage ( message, msgType, msgElement='FormMessage' ) {
    if ( message == '' ) return false;
    
    const FormMessage = document.getElementById(msgElement);
    switch (msgType) {
        case 'ok':
            FormMessage.className = 'okMessage';
            break;
        case 'error':
            FormMessage.className = 'errorMessage';
            break;
        default:
            FormMessage.className = 'infoMessage';
    };
    FormMessage.innerText = message;
    FormMessage.style.display = 'block';
    setTimeout( () => {
        FormMessage.style.display = 'none';
    }, 5000);
};

// *****************************************************************************
// ***************************** IMAGE FUNCTIONS *******************************
// *****************************************************************************

// Function to refresh replaced images ----------------------------------------
function refreshImage ( imageElement, imageURL ) {
    imageElement.src = imageURL + '?' + new Date().getTime();
    DEBUG_MODE ? console.log(imageElement.src) : true;
};

// Function to refresh an image collection ------------------------------------
function refreshCollection ( collectionElement, collectionArray ) {
    // Remove collection elements
    while(collectionElement.firstChild && collectionElement.removeChild(collectionElement.firstChild));
    // Get all image collection elements again
    collectionArray.forEach(url => {
        DEBUG_MODE ? console.log(url) : true;
        let extraImage = document.createElement('img');
        extraImage.src = url;
        extraImage.className = "extraImage";
        extraImage.alt = 'N/A';
        collectionElement.appendChild(extraImage);
    });
};

// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^