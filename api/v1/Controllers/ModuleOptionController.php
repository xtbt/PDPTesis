<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/ModuleOption.php' );
    
    // MODULEOPTION CONTROLLER
    class ModuleOptionController extends APIController {

        private $requestMethod = NULL;
        private $resourceId = NULL;
        private $queryString = NULL;
        private $requestBody = NULL;
        private $response = NULL;

        private $resourceObject = NULL;

        public function __construct( $requestMethod, $resourceId, $queryString, $requestBody ) {
            $this->requestMethod = $requestMethod;
            $this->resourceId = $resourceId;
            $this->queryString = $queryString;
            $this->requestBody = $requestBody;

            $this->resourceObject = new ModuleOption();
        }

        public function processRequest() {
            // TOKEN VALIDATION BLOCK #########################################
            $response = $this->resourceObject->isValidToken($this->resourceId);
            if (isset($response['error']))
                return $this->unauthorizedResponse($response);
            // TOKEN VALIDATION BLOCK #########################################
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getModulesOptionsStatuses();
                        else
                            $response = $this->getSingleRecord();
                    } else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                        $response = $this->createRecord();
                    break;
                case 'PUT':
                        $response = $this->updateRecord();
                    break;
                case 'PATCH':
                        $response = $this->modifyRecord();
                    break;
                case 'DELETE':
                        $response = $this->deleteRecord(); // TODO: DELETE Implementation
                    break;
                case 'OPTIONS':
                        $response = $this->noContentResponse();
                    break;
                default:
                    $response = $this->methodNotAllowedResponse();
            };
            return $response;
        }

        private function getModulesOptionsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getModuleOption($this->resourceId);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getAllRecords() {
            $result = $this->resourceObject->getAll($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function createRecord() {
            if (!isset($this->requestBody['data']['ModuleId']) 
            || !isset($this->requestBody['data']['ModuleOptionName']) 
            || !isset($this->requestBody['data']['ModuleOptionDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ModuleId = $this->requestBody['data']['ModuleId'];
            $ModuleOptionName = $this->requestBody['data']['ModuleOptionName'];
            $ModuleOptionDescription = $this->requestBody['data']['ModuleOptionDescription'];
            
            $result = $this->resourceObject->createModuleOption($ModuleId, $ModuleOptionName, $ModuleOptionDescription);
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['ModuleOptionId']) 
            || !isset($this->requestBody['data']['ModuleId']) 
            || !isset($this->requestBody['data']['ModuleOptionName'])
            || !isset($this->requestBody['data']['ModuleOptionDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ModuleOptionId = $this->requestBody['data']['ModuleOptionId'];
            $ModuleId = $this->requestBody['data']['ModuleId'];
            $ModuleOptionName = $this->requestBody['data']['ModuleOptionName'];
            $ModuleOptionDescription = $this->requestBody['data']['ModuleOptionDescription'];

            $result = $this->resourceObject->updateModuleOption($ModuleOptionId, $ModuleId, $ModuleOptionName, $ModuleOptionDescription);
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['ModuleOptionId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ModuleOptionId = $this->requestBody['data']['ModuleOptionId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateModuleOption($ModuleOptionId);
            else if ( $this->requestBody['data']['Action'] == 'Reactivate' )
                $result = $this->resourceObject->reactivateModuleOption($ModuleOptionId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>