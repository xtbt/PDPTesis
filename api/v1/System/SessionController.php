<?php
    class SessionController {

        // Class properties
        private $UserId;
        private $Token;
        private $TokenExpiryDateTime;
        private $DB_Connector;

        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblUsers';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'UserId';
            $this->SQL_Limit = NULL;
            $this->SQL_Params = [];
            $this->SQL_Sentence = NULL;
            
            $this->response['globalCount'] = -1;
            $this->response['count'] = -1;
            $this->response['data'] = NULL;
        }

        // Function that gets all rows in the Database
        // If criteria was defined, it filters the result
        public function getAll($queryString = NULL) {
            $this->DB_initProperties();
            if (!$this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ))
                 return $this->response; // Return SQL criteria error
            
            try {
                $SQL_GlobalQuery = 'SELECT 
                    UserId, 
                    Username, 
                    Password, 
                    Email, 
                    PhoneNumber, 
                    FirstName, 
                    LastName, 
                    AreaId, 
                    UserStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    ' ORDER BY '
                    .$this->SQL_Order;
                $SQL_Query = $SQL_GlobalQuery . (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->DB_loadParameters();
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Returns response with no records
                };

                $this->DB_loadResponse(get_class($this)); // If records found, build response Array with DB info
                $this->DB_getGlobalCount($SQL_GlobalQuery); // Get global count of rows ignoring LIMIT
                return $this->response; // Return response with records
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response with error
            };
        }

        // vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
        // CRUD FUNCTIONS START ***********************************************
        
        // Update class properties --------------------------------------------
        private function updateProperties($field_array) {
            foreach ($field_array AS $propertyName => $value) {
                $this->$propertyName = $value;
            };
        }

        // ********************************************************************
        // (READ) GET A SINGLE ROW ********************************************
        // ********************************************************************
        public function getUser($UserId) {
            $this->DB_initProperties();
            if (is_numeric($UserId)) {
                $this->SQL_Conditions .= ' AND UserId = :UserId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                        UserId, 
                        Username, 
                        Password, 
                        Email, 
                        PhoneNumber, 
                        FirstName, 
                        LastName, 
                        AreaId, 
                        Token, 
                        TokenExpiryDateTime, 
                        UserStatusId 
                        FROM '
                        .$this->SQL_Tables.
                        ' WHERE '
                        .$this->SQL_Conditions.
                        (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$UserId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$UserId]);
                $this->response['count'] = 1; // Unique record
                $this->response['globalCount'] = 1; // Unique record
                // ------------------------------------------------------------

                return $this->response; // Return Array response
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response;
            };
        }

        // ********************************************************************
        // (CREATE) CREATE NEW RECORD INTO DB *********************************
        // ********************************************************************
        public function createUser( $Username, $Password, $Email, $PhoneNumber, $FirstName, $LastName, $AreaId ) {
            $this->DB_initProperties();
            $UserId = NULL; // NULL by default on new records
            $Token = NULL; // NULL by default on new records
            $TokenExpiryDateTime = NULL; // NULL by default on new records
            $UserStatusId = 1; // 1(Active) by default on new records
        
            #######################################################################
            ####################### PASSWORD HASHING BLOCK ########################
            #######################################################################
            $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);
            #######################################################################

            try {
                $SQL_Query = 'INSERT INTO tblUsers VALUES (
                    :UserId, 
                    :Username, 
                    :HashedPassword, 
                    :Email, 
                    :PhoneNumber, 
                    :FirstName, 
                    :LastName, 
                    :AreaId, 
                    :Token, 
                    :TokenExpiryDateTime, 
                    :UserStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Username', $Username, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':HashedPassword', $HashedPassword, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':Email', $Email, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PhoneNumber', $PhoneNumber, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':FirstName', $FirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastName', $LastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':AreaId', $AreaId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Token', $Token, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TokenExpiryDateTime', $TokenExpiryDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserStatusId', $UserStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $UserId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $UserId];
                    $this->response['msg'] = '['.get_class($this).'] Ok: New record created successfully';
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot create new record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response
        }

        // ********************************************************************
        // (UPDATE) UPDATE RECORD ON DB ***************************************
        // ********************************************************************
        public function updateUser( $UserId, $Username, $Email, $PhoneNumber, $FirstName, $LastName, $AreaId ) {
            $this->getUser($UserId); // Get current record data from DB

            // Confirm changes on at least 1 field ----------------------------
            if ($this->Username == $Username && $this->Email == $Email && $this->PhoneNumber == $PhoneNumber 
            && $this->FirstName == $FirstName && $this->LastName == $LastName && $this->AreaId == $AreaId) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblUsers SET 
                  Username = :Username, 
                  Email = :Email, 
                  PhoneNumber = :PhoneNumber, 
                  FirstName = :FirstName, 
                  LastName = :LastName, 
                  AreaId = :AreaId 
                  WHERE 
                  UserId = :UserId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Username', $Username, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':Email', $Email, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PhoneNumber', $PhoneNumber, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':FirstName', $FirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastName', $LastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':AreaId', $AreaId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record updated successfully';
                    $this->getUser($UserId); // Update current object data with modified info
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot update record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function reactivateUser( $UserId ) {
            $this->getUser($UserId); // Get current record data from DB
            $UserStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblUsers SET 
                    UserStatusId = :UserStatusId 
                    WHERE 
                    UserId = :UserId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserStatusId', $UserStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record reactivated successfully';
                    $this->getUser($UserId); // Update current object data after reactivation
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot reactivate record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (DEACTIVATE) DEACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function deactivateUser( $UserId ) {
            $this->getUser($UserId); // Get current record data from DB
            $UserStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblUsers SET 
                    UserStatusId = :UserStatusId 
                    WHERE 
                    UserId = :UserId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserStatusId', $UserStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record deactivated successfully';
                    $this->getUser($UserId); // Update current object data after deactivation
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot deactivate record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        #######################################################################
        ################### LOGIN/LOGOUT/SIGNIN FUNCTIONS #####################
        #######################################################################
        private function verifyTokenExpiryDateTime () {
            $TokenExpiryDateTime = new DateTime($this->TokenExpiryDateTime);
            $CurrentDateTime = new DateTime(date('Y-m-d H:i:s'));
            $TimeDifference = $CurrentDateTime->diff($TokenExpiryDateTime);
            // DEBUG ZONE ##################################################
            $this->response['exp'] = $TokenExpiryDateTime;
            $this->response['now'] = $CurrentDateTime;
            $this->response['dif'] = $TimeDifference->h.':'.$TimeDifference->i;
            // DEBUG ZONE ##################################################
            if ($CurrentDateTime < $TokenExpiryDateTime) {
                // if ($TimeDifference->i <= 10) {
                //     if ( $this->tokenGeneration(20) ) {
                //         $this->response['info'] = 'The user Token has been renewed';
                //         $this->response['newToken'] = $this->Token;
                //     } else {
                //         $this->response['info'] = 'The user Token could not be renewed';
                //     }
                // };
                return true;
            };
            return false;
        }

        public function isValidToken ( $UserId, $Token ) {
            $this->getUser($UserId); // Get current record data from DB
            if ( $this->Token == $Token && !empty($Token) ) {
                if ( $this->verifyTokenExpiryDateTime() ) {
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $UserId];
                    $this->response['msg'] = '['.get_class($this).'] OK: Token is valid';
                } else {
                    $this->response['count'] = -1;
                    $this->response['data'] = ['id' => $UserId];
                    $this->response['msg'] = '['.get_class($this).'] Error: Token Expired, please login again';
                    $this->response['error'] = 'The Token used for this transaction is expired.';
                };
            } else {
                $this->response['count'] = -1;
                $this->response['data'] = ['id' => $UserId];
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid Token';
                $this->response['error'] = 'The provided Token is invalid.';
            };
            return $this->response; // Return response array
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
                $this->SQL_Sentence->bindParam(':UserId', $this->UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->Token = $JWT; // Update current property with modified info
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

        private function tokenGeneration( $Minutes = 120 ) {
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
                'jti' => $this->UserId
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

            return $this->updateToken($JWT, $ExpiryDateTime->format('Y-m-d H:i:s')); // Updates the token in Database
        }

        public function login( $Username, $Password ) {
            try {
                // Step 1: Username verification ******************
                $SQL_Query = 'SELECT 
                    UserId 
                    FROM 
                    tblUsers 
                    WHERE 
                    Username = :Username';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Username', $Username, PDO::PARAM_STR);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['msg'] = '['.get_class($this).'] Error: Username not found';
                    $this->response['error'] = '['.get_class($this).'] Invalid Username';
                    return $this->response; // Return response Array
                };
                
                // If there is data, we get the current DB info -------
                $row = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->getUser($row['UserId']); // Update current object data
                $this->response['count'] = -1; // Restart count property to continue validation
                $this->response['globalCount'] = -1; // Restart count property to continue validation
                $this->response['data'] = NULL; // Restart data property to continue validation

                // Step 2: User Status verification ***********************
                if ($this->UserStatusId < 1) {
                    $this->response['msg'] = '['.get_class($this).'] Error: User is disabled';
                    $this->response['error'] = '['.get_class($this).'] The Username is disabled on the Database';
                    return $this->response; // Return response Array
                };

                // Step 3: Password verification ******************************
                if ( !password_verify($Password, $this->Password) ) {
                    $this->response['msg'] = '['.get_class($this).'] Error: Invalid password';
                    $this->response['error'] = '['.get_class($this).'] The submited password is incorrect';
                    return $this->response; // Return response Array
                };
                
                // Step 4: Token generation ***********************************
                if ( !$this->tokenGeneration(120) ) {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot create Token';
                    // We get the error detail from the tokenGeneration::updateToken function
                    return $this->response; // Return response Array
                };

                // If everything was fine, return response with valid token
                return $this->response = [
                    'count'     => 1,
                    'globalCount'     => 1,
                    'data'      => [
                        'UserId'    => $this->UserId, 
                        'Token'     => $this->Token
                    ],
                    'msg'       => '['.get_class($this).'] OK: User logged in successfully'
                ];
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response Array
            };
        }

        public function logoff( $UserId ) {
            $Token = NULL;
            $TokenExpiryDateTime = NULL;
            try {
                // Step 1: Destroy Token **************************************
                $SQL_Query = 'UPDATE 
                    tblUsers 
                    SET 
                    Token = :Token, 
                    TokenExpiryDateTime = :TokenExpiryDateTime 
                    WHERE 
                    UserId = :UserId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Token', $Token, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TokenExpiryDateTime', $TokenExpiryDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot logoff user';
                    $this->response['error'] = '['.get_class($this).'] The user logoff process was interrupted';
                    return $this->response; // Return response Array
                };

                // If everything was fine, return response with successful message
                return $this->response = [
                    'count'     => 1,
                    'globalCount'     => 1,
                    'data'      => [
                        'UserId'    => $UserId, 
                        'Token'     => 'Destroyed'
                    ],
                    'msg'       => '['.get_class($this).'] OK: The user logoff process was successful'
                ];
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response Array
            };
        }

        #######################################################################
        ######################### PASSWORD FUNCTIONS ##########################
        #######################################################################
        public function updatePassword( $UserId, $NewPassword ) {
            $this->getUser($UserId); // Get current record data from DB
        
            //--------------------- PASSWORD HASHING BLOCK --------------------
            $NewHashedPassword = password_hash($NewPassword, PASSWORD_DEFAULT);
            // ----------------------------------------------------------------

            if ($NewHashedPassword == $this->Password) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No changes made in password';
                $this->response['error'] = 'The entered password is the same as the one stored into the DB';
                return $this->response; // Return response Array
            }

            try {
                $SQL_Query = 'UPDATE tblUsers 
                    SET 
                    Password = :NewHashedPassword 
                    WHERE 
                    UserId = :UserId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':NewHashedPassword', $NewHashedPassword, PDO::PARAM_STR);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $UserId];
                    $this->response['msg'] = '['.get_class($this).'] Ok: The user Password has been updated';
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot change user password';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response
        }

        // ****************************************************************************
        // ******* AUXILIARY METHODS **************************************************
        // ****************************************************************************
        public function getStatuses ($queryString = NULL) {
            $this->DB_initProperties();
            $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;
            // if (!$this->buildSQLCriteria($json_criteria))
            //     return $this->response; // Return criteria error

            try {
                // MANUAL STATIC RESPONSE *************************************
                $this->response['data'] = [
                    array(
                        'UserStatusId' => 0,
                        'UserStatusValue' => 'Inactive'
                    ),
                    array(
                        'UserStatusId' => 1,
                        'UserStatusValue' => 'Active'
                    )
                ]; // Data Array to be included in the response
                
                $this->response['count'] = count($this->response['data']); // Row count to be included in the response
                $this->response['globalCount'] = $this->response['count'];
                // MANUAL STATIC RESPONSE *************************************

                return $this->response; // Return response with records
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response with error
            };
        }
    }
?>