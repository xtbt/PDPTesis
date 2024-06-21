<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/TicketUpdate.php' );
    
    // USER CONTROLLER
    class TicketUpdateController extends APIController {

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

            $this->resourceObject = new TicketUpdate();
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
                            $response = $this->getTicketUpdatesStatuses();
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

        private function getTicketUpdatesStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getTicketUpdate($this->resourceId);
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
            if (!isset($this->requestBody['data']['TicketId']) 
            || !isset($this->requestBody['data']['TicketUpdateNote']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TicketId = $this->requestBody['data']['TicketId'];
            $TicketUpdateNote = $this->requestBody['data']['TicketUpdateNote'];
            
            $result = $this->resourceObject->createTicketUpdate( $TicketId, $TicketUpdateNote );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
               return $this->okResponse($result);
        }

        private function updateRecord() {
            // if (!isset($this->requestBody['data']['TicketUpdateId']) 
            // || !isset($this->requestBody['data']['TicketUpdateNote']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // // Required fields ------------------------------------------------
            // $TicketUpdateId = $this->requestBody['data']['TicketUpdateId'];
            // $TicketUpdateNote = $this->requestBody['data']['TicketUpdateNote'];

            // $result = $this->resourceObject->updateTicketUpdate( $TicketUpdateId, $TicketUpdateNote );
            // if ( $result['count'] < 1 )
            //     return $this->unprocessableEntityResponse($result);
            // else
            //    return $this->okResponse($result);
        }

        private function modifyRecord() {
            // if (!isset($this->requestBody['data']['TicketUpdateId']) 
            // || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // // Required fields ------------------------------------------------
            // $TicketUpdateId = $this->requestBody['data']['TicketUpdateId'];

            // if ( $this->requestBody['data']['Action'] == 'Deactivate' )
            //     $result = $this->resourceObject->deactivateTicketUpdate($TicketUpdateId);
            // else if ( $this->requestBody['data']['Action'] == 'Reactivate' )
            //     $result = $this->resourceObject->reactivateTicketUpdate($TicketUpdateId);
            // else
            //     return $this->notAcceptableResponse('Incorrect action');
            
            // if ( $result['count'] < 1 )
            //     return $this->unprocessableEntityResponse($result);
            // else
            //     return $this->okResponse($result);
        }
    }
?>