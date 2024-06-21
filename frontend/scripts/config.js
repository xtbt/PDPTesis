// ****************************************************************************
// ********************* CONTANTES/VARIABLES GLOBALES *************************
// ****************************************************************************

// DEBUGGING constants --------------------------------------------------------
const DEBUG_MODE = true;

// Constantes de sistema ------------------------------------------------------
const ID_DOMINIO = 0; // 0: DEMO, 1: HOSTING
let BASE_DIR = '/unitickets/';
const ITEMS_PER_PAGE = 30;

// API PARAMETERS CONFIGURATION -----------------------------------------------
const API_HOST = 'localhost';
const API_PORT = '80';

// Definición de variables de sistema #########################################
let WORKING_DOMAIN = 'UNITICKETS: DEMO';
let IS_SATURDAY_NON_WORKING = true;
let IS_SUNDAY_NON_WORKING = true;
let ENABLE_HOLIDAYS = true;
// Definición de variables de sistema #########################################

if (ID_DOMINIO == 1) {
    WORKING_DOMAIN = 'UNITICKETS: TIJUANA';
    IS_SATURDAY_NON_WORKING = true;
    IS_SUNDAY_NON_WORKING = true;
    ENABLE_HOLIDAYS = true;
}
else {
    BASE_DIR = '/unitickets/';
    WORKING_DOMAIN = 'UNITICKETS: DEMO';
};