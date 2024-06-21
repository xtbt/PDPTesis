<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class User extends AppModelCore {

        // Class properties
        public $UserId;
        public $Username;
        public $Password;
        public $Email;
        public $PhoneNumber;
        public $FirstName;
        public $LastName;
        public $AreaId;
        public $AreaName;                   // tblAreas::AreaName
        public $UserStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(UserId,""),"]",COALESCE(Username,""),"|",COALESCE(Email,""),"|",COALESCE(FirstName,""),"|",COALESCE(LastName,""),"|",COALESCE(AreaName,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblUsers AS t1 LEFT JOIN tblAreas AS t2 USING(AreaId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'UserId';
            $this->SQL_Limit = NULL;
            $this->SQL_Params = [];
            $this->SQL_Sentence = NULL;

            $this->initResponseData();
        }

        // Function that gets all rows in the Database
        // If criteria was defined, it filters the result
        public function getAll($queryString = NULL) {
            $this->DB_initProperties();
            if (!$this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ))
                 return $this->response; // Return SQL criteria error
            
            try {
                $SQL_GlobalQuery = 'SELECT 
                    t1.UserId AS UserId, 
                    t1.Username AS Username, 
                    t1.Password AS Password, 
                    t1.Email AS Email, 
                    t1.PhoneNumber AS PhoneNumber, 
                    t1.FirstName AS FirstName, 
                    t1.LastName AS LastName, 
                    t1.AreaId AS AreaId, 
                    t2.AreaName AS AreaName, 
                    t1.UserStatusId AS UserStatusId 
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
                    t1.UserId AS UserId, 
                    t1.Username AS Username, 
                    t1.Password AS Password, 
                    t1.Email AS Email, 
                    t1.PhoneNumber AS PhoneNumber, 
                    t1.FirstName AS FirstName, 
                    t1.LastName AS LastName, 
                    t1.AreaId AS AreaId, 
                    t2.AreaName AS AreaName, 
                    t1.UserStatusId AS UserStatusId 
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
                // SECURITY HOLE FIX ##########################################
                if (isset($this->response['data'][$UserId]['Password'])) unset($this->response['data'][$UserId]['Password']);
                // SECURITY HOLE FIX ##########################################
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
            $this->getUser( $UserId ); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

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
                    $this->getUser($UserId); // Update current object data with modified info
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record updated successfully';
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
            $this->initResponseData(); // Reset Response Array Information
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
                    $this->getUser($UserId); // Update current object data after reactivation
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record reactivated successfully';
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
            $this->initResponseData(); // Reset Response Array Information
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
                    $this->getUser($UserId); // Update current object data after deactivation
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record deactivated successfully';
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
                $this->appUserId = $this->UserId;
                $this->initResponseData(); // Reset Response Array Information

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
                if ( !$this->tokenGeneration( 120 ) ) {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot create Token';
                    // We get the error detail from the tokenGeneration::updateToken function
                    return $this->response; // Return response Array
                };

                // If everything was fine, return response with valid token
                $this->response['count'] = 1;
                $this->response['globalCount'] = 1;
                $this->response['Auth']['UserId'] = $this->appUserId;
                $this->response['Auth']['AreaId'] = $this->AreaId;
                $this->response['Auth']['Token'] = $this->currentToken;
                $this->response['msg'] = '['.get_class($this).'] OK: User logged in successfully';
                // DEBUG ZONE #################################################
                if (DEBUG_MODE) {
                    $this->response['debug']['appBearerToken'] = $this->appBearerToken;
                    $this->response['debug']['appUserId'] = $this->appUserId;
                };
                // DEBUG ZONE #################################################
                return $this->response;
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
                $this->response['count'] = 1;
                $this->response['globalCount'] = 1;
                //$this->response['Auth']['UserId'] = $UserId;
                $this->response['Auth']['UserId'] = NULL;
                $this->response['Auth']['Token'] = NULL;
                $this->response['msg'] = '['.get_class($this).'] OK: The user logoff process was successful';
                // DEBUG ZONE #################################################
                if (DEBUG_MODE) {
                    $this->response['debug']['appBearerToken'] = $this->appBearerToken;
                    $this->response['debug']['appUserId'] = $this->appUserId;
                };
                // DEBUG ZONE #################################################
                return $this->response;
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
            $this->initResponseData(); // Reset Response Array Information

            if ( password_verify($NewPassword, $this->Password) ) {
            //if ($NewHashedPassword == $this->Password) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No changes made in password';
                $this->response['error'] = 'The entered password is the same as the one stored into the DB';
                return $this->response; // Return response Array
            }

            //--------------------- PASSWORD HASHING BLOCK --------------------
            $NewHashedPassword = password_hash($NewPassword, PASSWORD_DEFAULT);
            // ----------------------------------------------------------------

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