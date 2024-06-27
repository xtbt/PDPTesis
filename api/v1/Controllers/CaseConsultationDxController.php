<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/CaseConsultationDx.php' );
    
    // CASECONSULTATION CONTROLLER
    class CaseConsultationDxController extends APIController {

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

            $this->resourceObject = new CaseConsultationDx();
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
                            $response = $this->getCasesConsultationsDxsStatuses();
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

        private function getCasesConsultationsDxsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getCaseConsultationDx($this->resourceId);
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
            if (!isset($this->requestBody['data']['CaseConsultationId'])
            || !isset($this->requestBody['data']['CaseConsultationDxCIE11'])
            || !isset($this->requestBody['data']['CaseConsultationDxDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseConsultationId = $this->requestBody['data']['CaseConsultationId'];
            $CaseConsultationDxCIE11 = $this->requestBody['data']['CaseConsultationDxCIE11'];
            $CaseConsultationDxDescription = $this->requestBody['data']['CaseConsultationDxDescription'];
            
            $result = $this->resourceObject->createCaseConsultationDx( $CaseConsultationId, $CaseConsultationDxCIE11, $CaseConsultationDxDescription );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['CaseConsultationDxId'])
            || !isset($this->requestBody['data']['CaseConsultationId'])
            || !isset($this->requestBody['data']['CaseConsultationDxCIE11'])
            || !isset($this->requestBody['data']['CaseConsultationDxDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseConsultationDxId = $this->requestBody['data']['CaseConsultationDxId'];
            $CaseConsultationId = $this->requestBody['data']['CaseConsultationId'];
            $CaseConsultationDxCIE11 = $this->requestBody['data']['CaseConsultationDxCIE11'];
            $CaseConsultationDxDescription = $this->requestBody['data']['CaseConsultationDxDescription'];

            $result = $this->resourceObject->updateCaseConsultationDx( $CaseConsultationDxId, $CaseConsultationId, $CaseConsultationDxCIE11, $CaseConsultationDxDescription );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['CaseConsultationDxId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseConsultationDxId = $this->requestBody['data']['CaseConsultationDxId'];

            if ( $this->requestBody['data']['Action'] == 'Cancel' )
                $result = $this->resourceObject->cancelCaseConsultationDx($CaseConsultationDxId);
            else if ( $this->requestBody['data']['Action'] == 'Reopen' )
                $result = $this->resourceObject->reopenCaseConsultationDx($CaseConsultationDxId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>