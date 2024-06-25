<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Patient_CPField.php' );
    
    // PATIENT_CPFIELD CONTROLLER
    class Patient_CPFieldController extends APIController {

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

            $this->resourceObject = new Patient_CPField();
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
                            $response = $this->getPatients_CPFieldsStatuses();
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

        private function getPatients_CPFieldsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getPatient_CPField($this->resourceId);
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
            if (!isset($this->requestBody['data']['PatientId']) 
            || !isset($this->requestBody['data']['CPFieldId'])
            || !isset($this->requestBody['data']['Patient_CPFieldNote']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $PatientId = $this->requestBody['data']['PatientId'];
            $CPFieldId = $this->requestBody['data']['CPFieldId'];
            $Patient_CPFieldNote = isset($this->requestBody['data']['Patient_CPFieldNote']) ? $this->requestBody['data']['Patient_CPFieldNote'] : NULL;
            
            $result = $this->resourceObject->createPatient_CPField( $PatientId, $CPFieldId, $Patient_CPFieldNote );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['Patient_CPFieldId']) 
            || !isset($this->requestBody['data']['PatientId']) 
            || !isset($this->requestBody['data']['CPFieldId'])
            || !isset($this->requestBody['data']['Patient_CPFieldNote']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $Patient_CPFieldId = $this->requestBody['data']['Patient_CPFieldId'];
            $PatientId = $this->requestBody['data']['PatientId'];
            $CPFieldId = $this->requestBody['data']['CPFieldId'];
            $Patient_CPFieldNote = isset($this->requestBody['data']['Patient_CPFieldNote']) ? $this->requestBody['data']['Patient_CPFieldNote'] : NULL;

            $result = $this->resourceObject->updatePatient_CPField( $Patient_CPFieldId, $PatientId, $CPFieldId, $Patient_CPFieldNote );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['Patient_CPFieldId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $Patient_CPFieldId = $this->requestBody['data']['Patient_CPFieldId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivatePatient_CPField($Patient_CPFieldId);
            else if ( $this->requestBody['data']['Action'] == 'Reactivate' )
                $result = $this->resourceObject->reactivatePatient_CPField($Patient_CPFieldId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>