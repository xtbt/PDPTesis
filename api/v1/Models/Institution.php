<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Institution extends AppModelCore {
        
        // Class properties
        public $InstitutionId;
        public $InstitutionName;
        public $InstitutionStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(InstitutionId,""),"]",COALESCE(InstitutionName,""),"|",COALESCE(InstitutionShortname,""))';
        
        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;
            
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblInstitutions';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'InstitutionId';
            $this->SQL_Limit = NULL;
            $this->SQL_Params = [];
            $this->SQL_Sentence = NULL;
            
            $this->initResponseData();
        }

        // Function that gets all rows in the Database
        // If criteria was defined, it filters the result
        public function getAll( $queryString = NULL ) {
            $this->DB_initProperties();
            if (!$this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ))
                 return $this->response; // Return SQL criteria error
            
            try {
                $SQL_GlobalQuery = 'SELECT 
                    InstitutionId, 
                    InstitutionName, 
                    InstitutionShortname, 
                    InstitutionStatusId 
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
        public function getInstitution( $InstitutionId ) {
            $this->DB_initProperties();
            if (is_numeric($InstitutionId)) {
                $this->SQL_Conditions .= ' AND InstitutionId = :InstitutionId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    InstitutionId, 
                    InstitutionName, 
                    InstitutionShortname, 
                    InstitutionStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':InstitutionId', $InstitutionId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$InstitutionId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$InstitutionId]);
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
        public function createInstitution( $InstitutionName, $InstitutionShortname ) {
            $this->DB_initProperties();
            $InstitutionId = NULL; // NULL by default on new records
            $InstitutionStatusId = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblInstitutions VALUES (
                  :InstitutionId, 
                  :InstitutionName, 
                  :InstitutionShortname, 
                  :InstitutionStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':InstitutionId', $InstitutionId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':InstitutionName', $InstitutionName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InstitutionShortname', $InstitutionShortname, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InstitutionStatusId', $InstitutionStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $InstitutionId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $InstitutionId];
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
        public function updateInstitution( $InstitutionId, $InstitutionName, $InstitutionShortname ) {
            $this->getInstitution($InstitutionId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ($this->InstitutionName == $InstitutionName && $this->InstitutionShortname == $InstitutionShortname) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblInstitutions SET 
                    InstitutionName = :InstitutionName, 
                    InstitutionShortname = :InstitutionShortname 
                    WHERE 
                    InstitutionId = :InstitutionId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':InstitutionName', $InstitutionName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InstitutionShortname', $InstitutionShortname, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InstitutionId', $InstitutionId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getInstitution($InstitutionId); // Update current object data with modified info
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
        public function reactivateInstitution( $InstitutionId ) {
            $this->getInstitution($InstitutionId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $InstitutionStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblInstitutions SET 
                    InstitutionStatusId = :InstitutionStatusId 
                    WHERE 
                    InstitutionId = :InstitutionId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':InstitutionStatusId', $InstitutionStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':InstitutionId', $InstitutionId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getInstitution($InstitutionId); // Update current object data after reactivation
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
        public function deactivateInstitution($InstitutionId) {
            $this->getInstitution($InstitutionId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $InstitutionStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblInstitutions SET 
                    InstitutionStatusId = :InstitutionStatusId 
                    WHERE 
                    InstitutionId = :InstitutionId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':InstitutionStatusId', $InstitutionStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':InstitutionId', $InstitutionId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getInstitution($InstitutionId); // Update current object data after deactivation
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

// ****************************************************************************
// ******* AUXILIARY METHODS (NON-CRUD) ***************************************
// ****************************************************************************
        
        public function getStatuses ($queryString = NULL) {
            $this->DB_initProperties();
            $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;

            try {
                // MANUAL STATIC RESPONSE *************************************
                $this->response['data'] = [
                    array(
                        'InstitutionStatusId' => 0,
                        'InstitutionStatusValue' => 'Inactive'
                    ),
                    array(
                        'InstitutionStatusId' => 1,
                        'InstitutionStatusValue' => 'Active'
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