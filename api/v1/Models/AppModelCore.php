<?php
    class AppModelCore {
        // DataBase properties
        protected $DB_Connector;
        protected $SQL_Tables;
        protected $SQL_Conditions;
        protected $SQL_Order;
        protected $SQL_Limit;
        protected $SQL_Params;
        protected $SQL_Sentence;

        // Authorization Properties
        protected $appBearerToken = NULL;
        protected $appUserId = NULL;
        protected $currentToken = NULL;
        protected $currentTokenExpiryDateTime = NULL;

        // Response Array *****************************************************
        protected $response = [
            'globalCount'   => -1, 
            'count'         => -1, 
            'data'          => NULL, 
            'msg'           => NULL
        ]; // Always return an Array, even on ERROR ***************************

        // INIT Response Array information ------------------------------------
        protected function initResponseData() {
            $this->response['globalCount'] = -1;
            $this->response['count'] = -1;
            $this->response['data'] = NULL;
            $this->response['msg'] = NULL;
        }

        /**
         * Return ecoded JSON object
         * @return object
         */
        protected function buildSQLCriteria ( $queryString, $SearchCriteriaFieldsString ) {
            if (!empty($queryString)) {
                $SQLCriteria = [];
                try {
                    // BEGIN: Step 1 - Process queryString ----------------------------
                    foreach ($queryString AS $key => $value) {
                        if ($key == 'order')
                            $this->SQL_Order = $value;
                        else if ($key == 'limit')
                            $this->SQL_Limit = $value;
                        else if ($key == 'SearchCriteria') {
                            $SQLCriteria['conditions'][$key] = [
                                'type'      => 'AND', 
                                'field'     => $SearchCriteriaFieldsString, 
                                'operator'  => 'LIKE',
                                'value'     => '%'.$value.'%'
                            ];
                        } else {
                            $SQLCriteria['conditions'][$key] = [
                                'type'      => 'AND', 
                                'field'     => $key, 
                                'operator'  => '=',
                                'value'     => $value
                            ];
                        };
                    };
                    // END: Step 1 - Process queryString ------------------------------
                    // BEGIN: Step 2 - Load SQL conditions ----------------------------
                    if (isset($SQLCriteria['conditions'])) {
                        foreach ($SQLCriteria['conditions'] AS $identifier => $condition) {
                            $this->SQL_Params[':'.$identifier] = $condition['value'];
                            $this->SQL_Conditions .= ' '.$condition['type'].(isset($condition['begingroup']) ? ' (' : ' ').$condition['field'].' '.$condition['operator'].' :'.$identifier.(isset($condition['finishgroup']) ? ')' : '');
                        };
                    };
                    // END: Step 2 - Load SQL conditions ------------------------------
                } catch (Exception $ex) {
                    $this->response['msg'] = '['.get_class($this).'] SQL criteria error';
                    $this->response['error'] = $ex->getMessage();
                    return false; // Return FALSE on SQL criteria error
                };
            };
            return true;
        }

        // SPECIAL FUNCTION: Load necesary parameters depending on criteria ***
        protected function DB_loadParameters() {
            if (count($this->SQL_Params) > 0) {
                foreach ($this->SQL_Params AS $identifier => &$value) {
                    $this->SQL_Sentence->bindParam($identifier, $value, PDO::PARAM_STR);
                };
            };
        }

        // SPECIAL FUNCTION: Load image file list array from server ***********
        protected function getFileList ( $url, $ObjectId, $main = false ) {
            $fileList = glob($url . $ObjectId . '(*.*');
            $fileListCount = count($fileList);
            if ($fileList !== false && $fileListCount > 0) {
                for ($i = 0 ; $i < $fileListCount ; $i++)
                    $fileList[$i] = substr($fileList[$i], strpos($fileList[$i], '/unitickets'));
                if ($main && strpos($fileList[0], '(0)') !== false)
                    return $fileList[0];
                else if ($main && strpos($fileList[0], '(0)') === false)
                    return false;
                else
                    return $fileList;
            } else {
                return false;
            };
        }

        // SPECIAL FUNCTION: Load response to be returned to the controller ***
        protected function DB_loadResponse($className = NULL) {
            $this->response['data'] = []; // Data Array to be included in the response
                while ($row_array = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                    // Static implementation of image URL *********************
                    $imageFileList = $this->getFileList(SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/', isset($row_array['TicketId']) ? $row_array['TicketId'] : 'nope');
                    if ($imageFileList != false) {
                        if ($className == 'Ticket') {
                            $row_array['TicketMainImage'] = $imageFileList[0];
                            if (count($imageFileList) > 1) {
                                for ($i = 1 ; $i < count($imageFileList) ; $i++) {
                                    $row_array['TicketImages'][] = $imageFileList[$i];
                                };
                            };
                        };
                    };
                    // Static implementation of image URL *********************
                    // SECURITY HOLE FIX ##############################################
                    if (isset($row_array['Password'])) unset($row_array['Password']);
                    // SECURITY HOLE FIX ##############################################
                    $this->response['data'][] = $row_array;
                };
            $this->response['count'] = count($this->response['data']); // Row count to be included in the response
        }

        // SPECIAL FUNCTION: Get global count for current SQL Query ***********
        protected function DB_getGlobalCount($SQL_GlobalQuery) {
            try {
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_GlobalQuery);
                $this->DB_loadParameters();
                $this->SQL_Sentence->execute();

                $this->response['globalCount'] = $this->SQL_Sentence->rowCount(); // Global Count
            }
            catch (PDOException $ex) {
                $this->response['globalCount'] = -1; // Means error
            };
        }

        ####################################################################
        ####################  AUTHORIZATION FUNCTIONS  #####################
        ####################################################################
        public function isValidToken($resourceId) {
            if ($resourceId == 'doLogin' || $resourceId == 'doLogoff') {
                // DEBUG ZONE #################################################
                if (DEBUG_MODE) {
                    $this->response['debug']['info'] = 'No token required (Login-Logoff-TokenValidated functions)';
                };
                // DEBUG ZONE #################################################
                return $this->response;
            };
            try {
                // Step 1: Get token from database ****************************
                $SQL_Query = 'SELECT 
                    Token, 
                    TokenExpiryDateTime  
                    FROM 
                    tblUsers 
                    WHERE 
                    UserId = :UserId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserId', $this->appUserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['msg'] = '['.get_class($this).'] Error: UserId not found';
                    $this->response['error'] = '['.get_class($this).'] Invalid UserId';
                    return $this->response; // Return response Array
                };
                $row = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->currentToken = $row['Token'];
                $this->currentTokenExpiryDateTime = $row['TokenExpiryDateTime'];
                // DEBUG ZONE #############################################
                if (DEBUG_MODE) {
                    $this->response['debug']['currentToken'] = $this->currentToken;
                    $this->response['debug']['bearerToken'] = $this->appBearerToken;
                    $this->response['debug']['currentTokenExpiryDateTime'] = $this->currentTokenExpiryDateTime;
                };
                // DEBUG ZONE #############################################
                if ( $this->currentToken && $this->appBearerToken && ($this->currentToken == $this->appBearerToken) ) {
                    if ( $this->verifyTokenExpiryDateTime() ) {
                        $this->response['msg'] = '['.get_class($this).'] OK: Token is valid';
                    } else {
                        $this->response['msg'] = '['.get_class($this).'] Error: Token Expired, please login again';
                        $this->response['error'] = 'The Token used for this transaction is expired.';
                    };
                    return $this->response; // Return response Array
                } else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Invalid Token';
                    $this->response['error'] = 'The provided Token is invalid.';
                    return $this->response; // Return response Array
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response Array
            };
        }

        private function verifyTokenExpiryDateTime () {
            $TokenExpiryDateTime = new DateTime($this->currentTokenExpiryDateTime);
            $CurrentDateTime = new DateTime(date('Y-m-d H:i:s'));
            $TimeDifference = $CurrentDateTime->diff($TokenExpiryDateTime);
            // DEBUG ZONE ##################################################
            if (DEBUG_MODE) {
                $this->response['debug']['exp'] = $TokenExpiryDateTime;
                $this->response['debug']['now'] = $CurrentDateTime;
                $this->response['debug']['dif'] = $TimeDifference->h.':'.$TimeDifference->i;
            };
            // DEBUG ZONE ##################################################
            if ($CurrentDateTime < $TokenExpiryDateTime) {
                if ($TimeDifference->h = 0 && $TimeDifference->i <= 10) {
                    if ( $this->tokenGeneration(30) ) {
                        // DEBUG ZONE #########################################
                        if (DEBUG_MODE) {
                            $this->response['debug']['info'] = 'The user Token has been renewed';
                        };
                        // DEBUG ZONE #########################################
                        $this->response['Auth']['Token'] = $this->currentToken; // SEND NEW TOKEN TO CLIENT
                    } else {
                        // DEBUG ZONE #########################################
                        if (DEBUG_MODE) {
                            $this->response['debug']['info'] = 'The user Token could not be renewed';
                        };
                        // DEBUG ZONE #########################################
                    };
                };
                return true;
            };
            return false;
        }

        protected function tokenGeneration( $Minutes = 120 ) {
            $Secret = 'UniticketsApp';
            $Issuer = 'IP20';
            $ExpiryDateTime = new DateTime(); // DateTimeObject for Token expiration
            $ExpiryDateTime->add(new DateInterval('PT'.$Minutes.'M')); // Add X minutes for expiration
            
            // Create token header and encode as JSON
            $header = json_encode([
                'typ' => 'JWT', 
                'alg' => 'HS256'
            ]);

            // Create token payload and encode as JSON
            $payload = json_encode([
                'iss' => $Issuer, 
                'exp' => $ExpiryDateTime->format('YmdHis'), 
                'jti' => $this->appUserId
            ]);

            // Encode Header to Base64Url String
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

            // Encode Payload to Base64Url String
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            // Create Signature Hash
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $Secret, true);

            // Encode Signature to Base64Url String
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            // Create JWT
            $JWT = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            return $this->updateToken( $JWT, $ExpiryDateTime->format('Y-m-d H:i:s') ); // Updates the token in Database
        }

        private function updateToken( $JWT, $TokenExpiryDateTime ) {
            try {
                $SQL_Query = 'UPDATE tblUsers SET 
                  Token = :Token,
                  TokenExpiryDateTime = :TokenExpiryDateTime 
                  WHERE 
                  UserId = :UserId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Token', $JWT, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TokenExpiryDateTime', $TokenExpiryDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $this->appUserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->currentToken = $JWT; // Update currentToken property with modified info
                    $this->currentTokenExpiryDateTime = $TokenExpiryDateTime; // Update Expiry info
                    return true;
                }
                else {
                    $this->response['error'] = '['.get_class($this).'] Error: Cannot update token';
                };
            }
            catch (PDOException $ex) {
                $this->response['error'] = $ex->getMessage();
            };
            return false; // If the update was unsuccessful, return false
        }
    }
?>