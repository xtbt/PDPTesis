'use strict';

// Module constants (To change in each module)
const CrudFormURL = BASE_DIR + 'frontend/admin/general/areas/crud_form.php'; // Used to change the URL with the corresponding queryString (Record Id)
const BaseAPIEndpoint = BASE_DIR + 'api/v1/index.php/areas';

// Filter Form Elements -------------------------------------------------------
const frm_filter = document.getElementById('frm_filter');
const txt_criteria_filter = document.getElementById('txt_criteria_filter');
const sel_statuses_filter = document.getElementById('sel_statuses_filter');
const btn_filter = document.getElementById('btn_filter');

// Table Elements -------------------------------------------------------------
const table = document.getElementById('tblAreas');
const tableDefaultMessage = document.getElementById('table-default_message');

// Page Data Initialization ---------------------------------------------------
window.addEventListener('load', function() {
    loadStatusesOnList();
    loadElementsOnTable();
});

// Form Action Management -----------------------------------------------------
frm_filter.addEventListener('submit', function (event) {
    event.preventDefault(); // Ignore Enter key default action
    btn_filter.click();
});

// Button Listeners -----------------------------------------------------------
btn_filter.addEventListener('click', function(event) {
    DEBUG_MODE ? console.log('Button Clicked: ' + event.target.value) : true;
    loadElementsOnTable();
});

// Function to load goods areas statuses to filter the list
function loadStatusesOnList () {
    let optionItem = null;

    let APIEndpoint = BaseAPIEndpoint + '/statuses';
    fetchData (APIEndpoint)
    .then (APIResponse => {
        DEBUG_MODE ? console.log('We got API response: ') : true;
        DEBUG_MODE ? console.log(APIResponse) : true;
        APIResponse.body.data.forEach(element => {
            optionItem = document.createElement('option');
            optionItem.value = element.AreaStatusId;
            optionItem.innerHTML = element.AreaStatusValue;
            sel_statuses_filter.appendChild(optionItem);
        });
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : true;
    });
}

// Function to fill table using current filters
function loadElementsOnTable () {
    let APIEndpoint = BaseAPIEndpoint;
    let JSONFilters = {
        "AreaStatusId": sel_statuses_filter.value,
        "SearchCriteria": txt_criteria_filter.value
    };

    getCurrentPage()
    .then (Response => {
        DEBUG_MODE ? console.log('1) Get current page: ' + Response) : true;
        JSONFilters.limit = ITEMS_PER_PAGE * (Response - 1) + ',' + ITEMS_PER_PAGE;
        return initTable (table);
    })
    .then (Response => {
        DEBUG_MODE ? console.log('2) Table initialized... ') : true;
        return prepareTable (table, 'Obteniendo datos...');
    })
    .then (Response => {
        DEBUG_MODE ? console.log('3) Table prepared... ') : true;
        return initPaginationData ();
    })
    .then (Response => {
        DEBUG_MODE ? console.log('4) Pagination Data Initialized... ') : true;
        return fetchData (APIEndpoint, JSONFilters);
    })
    .then (APIResponse => {
        DEBUG_MODE ? console.log('5) API data acquired: ') : true;
        DEBUG_MODE ? console.log(APIResponse) : true;

        displayPaginationData ( APIResponse.body.globalCount, JSONFilters );

        let tableRow, tableRowCell;
        APIResponse.body.data.forEach(element => {
            DEBUG_MODE ? console.log(element) : true;
            tableRow = table.insertRow(-1);
            // AreaId
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerHTML = '<a href="'+ CrudFormURL + '?AreaId=' + element.AreaId + '">' + element.AreaId + '</a>';
            // AreaName
            tableRowCell = tableRow.insertCell(-1);
            tableRowCell.innerText = element.AreaName;
            // AreaStatusId (Manual procedure)
            tableRowCell = tableRow.insertCell(-1);
            const AreaStatusId = Number(element.AreaStatusId);
            switch (AreaStatusId) {
                case 0:
                    tableRowCell.innerText = 'Inactivo';
                    break;
                case 1:
                    tableRowCell.innerText = 'Activo';
                    break;
                default:
                    tableRowCell.innerText = 'Desconocido';
            };
        });
    })
    .catch (error => {
        DEBUG_MODE ? console.error(error) : true;
    })
    .then(() => {
        tableDefaultMessage.innerText = ''; // We get rid of the default message
    });
}