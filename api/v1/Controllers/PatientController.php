<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Patient.php' );
    
    // USER CONTROLLER
    class PatientController extends APIController {

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

            $this->resourceObject = new Patient();
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
                            $response = $this->getPatientsStatuses();
                        else
                            $response = $this->getSingleRecord();
                    } else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                    if ( NULL !== $this->resourceId ) {
                        $response = $this->notAcceptableResponse('Incorrect use of resource');
                    } else
                        $response = $this->createRecord();
                    break;
                case 'PUT':
                    $response = $this->updateRecord();
                    break;
                case 'PATCH':
                    if ( NULL !== $this->resourceId ) {
                        $response = $this->notAcceptableResponse('Incorrect use of resource');
                    } else
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

        private function getPatientsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getPatient($this->resourceId);
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
            if (!isset($this->requestBody['data']['PatientAffiliationId']) 
            || !isset($this->requestBody['data']['PatientFirstName']) 
            || !isset($this->requestBody['data']['PatientLastName']) 
            || !isset($this->requestBody['data']['PatientBirthDate']) 
            || !isset($this->requestBody['data']['PatientBloodType']) 
            || !isset($this->requestBody['data']['PatientObservations']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $Patientname = $this->requestBody['data']['Patientname'];
            $Password = $this->requestBody['data']['Password'];
            $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
            $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];
            $AreaId = $this->requestBody['data']['AreaId'];

            $result = $this->resourceObject->createPatient( $Patientname, $Password, $Email, $PhoneNumber, $FirstName, $LastName, $AreaId );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['PatientId']) || !isset($this->requestBody['data']['Patientname']) || 
            !isset($this->requestBody['data']['Email']) || !isset($this->requestBody['data']['PhoneNumber']) || 
            !isset($this->requestBody['data']['FirstName']) || !isset($this->requestBody['data']['LastName']) || 
            !isset($this->requestBody['data']['AreaId']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $PatientId = $this->requestBody['data']['PatientId'];
            $Patientname = $this->requestBody['data']['Patientname'];
            $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
            $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];
            $AreaId = $this->requestBody['data']['AreaId'];

            $result = $this->resourceObject->updatePatient( $PatientId, $Patientname, $Email, $PhoneNumber, $FirstName, $LastName, $AreaId );
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['PatientId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $PatientId = $this->requestBody['data']['PatientId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivatePatient($PatientId);
            else if ( $this->requestBody['data']['Action'] == 'Reactivate' )
                $result = $this->resourceObject->reactivatePatient($PatientId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>