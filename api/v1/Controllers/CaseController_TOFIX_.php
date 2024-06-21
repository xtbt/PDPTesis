<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Ticket.php' );
    
    // USER CONTROLLER
    class TicketController extends APIController {

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

            $this->resourceObject = new Ticket();
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
                            $response = $this->getTicketsStatuses();
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
                        else if ( 'closeTicket' === $this->resourceId )
                            $response = $this->closeTicket();
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

        private function getTicketsStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getTicket($this->resourceId);
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
            if (!isset($this->requestBody['data']['ProblemCategoryId'])
            || !isset($this->requestBody['data']['ProblemSubCategoryId'])
            || !isset($this->requestBody['data']['TicketDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ProblemCategoryId = $this->requestBody['data']['ProblemCategoryId'];
            $ProblemSubCategoryId = $this->requestBody['data']['ProblemSubCategoryId'];
            $TicketDescription = $this->requestBody['data']['TicketDescription'];
            
            $result = $this->resourceObject->createTicket( $ProblemCategoryId, $ProblemSubCategoryId, $TicketDescription );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['TicketId'])
            || !isset($this->requestBody['data']['ProblemCategoryId'])
            || !isset($this->requestBody['data']['ProblemSubCategoryId'])
            || !isset($this->requestBody['data']['TicketDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->requestBody['data']['TicketId'];
            $ProblemCategoryId = $this->requestBody['data']['ProblemCategoryId'];
            $ProblemSubCategoryId = $this->requestBody['data']['ProblemSubCategoryId'];
            $TicketDescription = $this->requestBody['data']['TicketDescription'];

            $result = $this->resourceObject->updateTicket( $TicketId, $ProblemCategoryId, $ProblemSubCategoryId, $TicketDescription );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function modifyRecord() {
            if (!isset($this->requestBody['data']['TicketId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->requestBody['data']['TicketId'];

            if ( $this->requestBody['data']['Action'] == 'Cancel' )
                $result = $this->resourceObject->cancelTicket($TicketId);
            else if ( $this->requestBody['data']['Action'] == 'Reopen' )
                $result = $this->resourceObject->reopenTicket($TicketId);
            else
                return $this->notAcceptableResponse('Incorrect action');
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function changeStatus() {
            if (!isset($this->requestBody['data']['TicketId']) 
            || !isset($this->requestBody['data']['TicketStatusId']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->requestBody['data']['TicketId'];
            $TicketStatusId = $this->requestBody['data']['TicketStatusId'];

            $result = $this->resourceObject->changeTicketStatus( $TicketId, $TicketStatusId );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function closeTicket() {
            if (!isset($this->requestBody['data']['TicketId']) 
            || !isset($this->requestBody['data']['TicketLastComment']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->requestBody['data']['TicketId'];
            $TicketLastComment = $this->requestBody['data']['TicketLastComment'];

            $result = $this->resourceObject->closeTicket( $TicketId, $TicketLastComment );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function uploadImage() {
            if (!isset($_FILES['image']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->queryString['TicketId'];
            $Context = $this->queryString['Context'];
            
            $result = $this->resourceObject->uploadFile( $TicketId, $Context );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }
    }
?>