<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/CaseLabtest.php' );
    
    // CASELABTEST CONTROLLER
    class CaseLabtestController extends APIController {

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

            $this->resourceObject = new CaseLabtest();
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
                            $response = $this->getCasesLabtestsStatuses();
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

        private function getCasesLabtestsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getCaseLabtest($this->resourceId);
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
            || !isset($this->requestBody['data']['CaseLabtestDate'])
            || !isset($this->requestBody['data']['CaseLabtestNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseLabtestDate = $this->requestBody['data']['CaseLabtestDate'];
            $CaseLabtestNotes = $this->requestBody['data']['CaseLabtestNotes'];
            
            $result = $this->resourceObject->createCaseLabtest( $CaseId, $CaseLabtestDate, $CaseLabtestNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['CaseLabtestId'])
            || !isset($this->requestBody['data']['CaseId'])
            || !isset($this->requestBody['data']['CaseLabtestDate'])
            || !isset($this->requestBody['data']['CaseLabtestNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseLabtestId = $this->requestBody['data']['CaseLabtestId'];
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseLabtestDate = $this->requestBody['data']['CaseLabtestDate'];
            $CaseLabtestNotes = $this->requestBody['data']['CaseLabtestNotes'];

            $result = $this->resourceObject->updateCaseLabtest( $CaseLabtestId, $CaseId, $CaseLabtestDate, $CaseLabtestNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['CaseLabtestId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseLabtestId = $this->requestBody['data']['CaseLabtestId'];

            if ( $this->requestBody['data']['Action'] == 'Cancel' )
                $result = $this->resourceObject->cancelCaseLabtest($CaseLabtestId);
            else if ( $this->requestBody['data']['Action'] == 'Reopen' )
                $result = $this->resourceObject->reopenCaseLabtest($CaseLabtestId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>