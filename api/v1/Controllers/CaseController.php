<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/CaseM.php' );
    
    // CASE CONTROLLER
    class CaseController extends APIController {

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

            $this->resourceObject = new CaseM();
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
                            $response = $this->getCasesStatuses();
                        else
                            $response = $this->getSingleRecord();
                    } else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'uploadImage' === $this->resourceId )
                            $response = $this->uploadImage();
                        else
                            $response = $this->notAcceptableResponse('Incorrect use of resource');
                    } else
                        $response = $this->createRecord();
                    break;
                case 'PUT':
                        $response = $this->updateRecord();
                    break;
                case 'PATCH':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'changeStatus' === $this->resourceId )
                            $response = $this->changeStatus();
                        else if ( 'closeCase' === $this->resourceId )
                            $response = $this->closeCase();
                        else
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

        private function getCasesStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getCase($this->resourceId);
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
            || !isset($this->requestBody['data']['CaseDate'])
            || !isset($this->requestBody['data']['LastMenstrualPeriod'])
            || !isset($this->requestBody['data']['InitialBloodPressure'])
            || !isset($this->requestBody['data']['InitialWeight'])
            || !isset($this->requestBody['data']['InitialSymtoms']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $PatientId = $this->requestBody['data']['PatientId'];
            $CaseDate = $this->requestBody['data']['CaseDate'];
            $LastMenstrualPeriod = $this->requestBody['data']['LastMenstrualPeriod'];
            $InitialBloodPressure = $this->requestBody['data']['InitialBloodPressure'];
            $InitialWeight = $this->requestBody['data']['InitialWeight'];
            $InitialSymtoms = $this->requestBody['data']['InitialSymtoms'];
            
            $result = $this->resourceObject->createCase( $PatientId, $CaseDate, $LastMenstrualPeriod, $InitialBloodPressure, $InitialWeight, $InitialSymtoms );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['CaseId'])
            || !isset($this->requestBody['data']['PatientId'])
            || !isset($this->requestBody['data']['CaseDate'])
            || !isset($this->requestBody['data']['LastMenstrualPeriod'])
            || !isset($this->requestBody['data']['InitialBloodPressure'])
            || !isset($this->requestBody['data']['InitialWeight'])
            || !isset($this->requestBody['data']['InitialSymtoms'])
            || !isset($this->requestBody['data']['CaseNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];
            $PatientId = $this->requestBody['data']['PatientId'];
            $CaseDate = $this->requestBody['data']['CaseDate'];
            $LastMenstrualPeriod = $this->requestBody['data']['LastMenstrualPeriod'];
            $InitialBloodPressure = $this->requestBody['data']['InitialBloodPressure'];
            $InitialWeight = $this->requestBody['data']['InitialWeight'];
            $InitialSymtoms = $this->requestBody['data']['InitialSymtoms'];
            $CaseNotes = $this->requestBody['data']['CaseNotes'];

            $result = $this->resourceObject->updateCase( $CaseId, $PatientId, $CaseDate, $LastMenstrualPeriod, $InitialBloodPressure, $InitialWeight, $InitialSymtoms, $CaseNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['CaseId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];

            if ( $this->requestBody['data']['Action'] == 'Cancel' )
                $result = $this->resourceObject->cancelCase($CaseId);
            else if ( $this->requestBody['data']['Action'] == 'Reopen' )
                $result = $this->resourceObject->reopenCase($CaseId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function changeStatus() {
            if (!isset($this->requestBody['data']['CaseId']) 
            || !isset($this->requestBody['data']['CaseStatusId']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseStatusId = $this->requestBody['data']['CaseStatusId'];

            $result = $this->resourceObject->changeCaseStatus( $CaseId, $CaseStatusId );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function closeCase() {
            if (!isset($this->requestBody['data']['CaseId']) 
            || !isset($this->requestBody['data']['CaseNotes']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->requestBody['data']['CaseId'];
            $CaseNotes = $this->requestBody['data']['CaseNotes'];

            $result = $this->resourceObject->closeCase( $CaseId, $CaseNotes );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function uploadImage() {
            if (!isset($_FILES['image']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $CaseId = $this->queryString['CaseId'];
            $Context = $this->queryString['Context'];
            
            $result = $this->resourceObject->uploadFile( $CaseId, $Context );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }
    }
?>