<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/CaseConsultation.php' );
    
    // CASECONSULTATION CONTROLLER
    class CaseConsultationController extends APIController {

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

            $this->resourceObject = new CaseConsultation();
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
                            $response = $this->getCasesConsultationsStatuses();
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

        private function getCasesConsultationsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getCaseConsultation($this->resourceId);
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
            if (!isset($this->requestBody['data']['CaseId'])
            || !isset($this->requestBody['data']['CaseConsultationDate'])
            || !isset($this->requestBody['data']['CurrentBloodPressure'])
            || !isset($this->requestBody['data']['CurrentWeight'])
            || !isset($this->requestBody['data']['CurrentSymtoms'])
            || !isset($this->requestBody['data']['CaseConsultationNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseConsultationDate = $this->requestBody['data']['CaseConsultationDate'];
            $CurrentBloodPressure = $this->requestBody['data']['CurrentBloodPressure'];
            $CurrentWeight = $this->requestBody['data']['CurrentWeight'];
            $CurrentSymtoms = $this->requestBody['data']['CurrentSymtoms'];
            $CaseConsultationNotes = $this->requestBody['data']['CaseConsultationNotes'];
            
            $result = $this->resourceObject->createCaseConsultation( $CaseId, $CaseConsultationDate, $CurrentBloodPressure, $CurrentWeight, $CurrentSymtoms, $CaseConsultationNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['CaseConsultationId'])
            || !isset($this->requestBody['data']['CaseId'])
            || !isset($this->requestBody['data']['CaseConsultationDate'])
            || !isset($this->requestBody['data']['CurrentBloodPressure'])
            || !isset($this->requestBody['data']['CurrentWeight'])
            || !isset($this->requestBody['data']['CurrentSymtoms'])
            || !isset($this->requestBody['data']['CaseConsultationNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseConsultationId = $this->requestBody['data']['CaseConsultationId'];
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseConsultationDate = $this->requestBody['data']['CaseConsultationDate'];
            $CurrentBloodPressure = $this->requestBody['data']['CurrentBloodPressure'];
            $CurrentWeight = $this->requestBody['data']['CurrentWeight'];
            $CurrentSymtoms = $this->requestBody['data']['CurrentSymtoms'];
            $CaseConsultationNotes = $this->requestBody['data']['CaseConsultationNotes'];

            $result = $this->resourceObject->updateCaseConsultation( $CaseConsultationId, $CaseId, $CaseConsultationDate, $CurrentBloodPressure, $CurrentWeight, $CurrentSymtoms, $CaseConsultationNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['CaseConsultationId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseConsultationId = $this->requestBody['data']['CaseConsultationId'];

            if ( $this->requestBody['data']['Action'] == 'Cancel' )
                $result = $this->resourceObject->cancelCaseConsultation($CaseConsultationId);
            else if ( $this->requestBody['data']['Action'] == 'Reopen' )
                $result = $this->resourceObject->reopenCaseConsultation($CaseConsultationId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>