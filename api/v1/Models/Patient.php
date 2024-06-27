<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Patient extends AppModelCore {

        // Class properties
        public $PatientId;
        public $PatientAffiliationId;
        public $PatientFirstName;
        public $PatientLastName;
        public $PatientBirthDate;
        public $PatientBloodType;
        public $PatientObservations;
        public $PatientStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(PatientId,""),"]",COALESCE(PatientAffiliationId,""),"|",COALESCE(PatientFirstName,""),"|",COALESCE(PatientLastName,""),"|",COALESCE(PatientBloodType,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblPatients AS t1';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'PatientId';
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
                    t1.PatientId AS PatientId, 
                    t1.PatientAffiliationId AS PatientAffiliationId, 
                    t1.PatientFirstName AS PatientFirstName, 
                    t1.PatientLastName AS PatientLastName, 
                    t1.PatientBirthDate AS PatientBirthDate, 
                    t1.PatientBloodType AS PatientBloodType, 
                    t1.PatientObservations AS PatientObservations, 
                    t1.PatientStatusId AS PatientStatusId 
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
        private function updateProperties( $field_array ) {
            foreach ($field_array AS $propertyName => $value) {
                $this->$propertyName = $value;
            };
        }

        // ********************************************************************
        // (READ) GET A SINGLE ROW ********************************************
        // ********************************************************************
        public function getPatient( $PatientId ) {
            $this->DB_initProperties();
            if (is_numeric($PatientId)) {
                $this->SQL_Conditions .= ' AND PatientId = :PatientId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.PatientId AS PatientId, 
                    t1.PatientAffiliationId AS PatientAffiliationId, 
                    t1.PatientFirstName AS PatientFirstName, 
                    t1.PatientLastName AS PatientLastName, 
                    t1.PatientBirthDate AS PatientBirthDate, 
                    t1.PatientBloodType AS PatientBloodType, 
                    t1.PatientObservations AS PatientObservations, 
                    t1.PatientStatusId AS PatientStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$PatientId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$PatientId]);
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
        public function createPatient( $PatientAffiliationId, $PatientFirstName, $PatientLastName, $PatientBirthDate, $PatientBloodType, $PatientObservations ) {
            $this->DB_initProperties();
            $PatientId = NULL; // NULL by default on new records
            $PatientStatusId = 1; // 1(Active) by default on new records

            try {
                $SQL_Query = 'INSERT INTO tblPatients VALUES (
                    :PatientId, 
                    :PatientAffiliationId, 
                    :PatientFirstName, 
                    :PatientLastName, 
                    :PatientBirthDate, 
                    :PatientBloodType, 
                    :PatientObservations, 
                    :PatientStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':PatientAffiliationId', $PatientAffiliationId, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientFirstName', $PatientFirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientLastName', $PatientLastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientBirthDate', $PatientBirthDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientBloodType', $PatientBloodType, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientObservations', $PatientObservations, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientStatusId', $PatientStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $PatientId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $PatientId];
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
        public function updatePatient( $PatientId, $PatientAffiliationId, $PatientFirstName, $PatientLastName, $PatientBirthDate, $PatientBloodType, $PatientObservations ) {
            $this->getPatient( $PatientId ); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ($this->PatientAffiliationId == $PatientAffiliationId 
            && $this->PatientFirstName == $PatientFirstName 
            && $this->PatientLastName == $PatientLastName 
            && $this->PatientBirthDate == $PatientBirthDate 
            && $this->PatientBloodType == $PatientBloodType 
            && $this->PatientObservations == $PatientObservations) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblPatients SET 
                  PatientAffiliationId = :PatientAffiliationId, 
                  PatientFirstName = :PatientFirstName, 
                  PatientLastName = :PatientLastName, 
                  PatientBirthDate = :PatientBirthDate, 
                  PatientBloodType = :PatientBloodType, 
                  PatientObservations = :PatientObservations 
                  WHERE 
                  PatientId = :PatientId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientAffiliationId', $PatientAffiliationId, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientFirstName', $PatientFirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientLastName', $PatientLastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientBirthDate', $PatientBirthDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientBloodType', $PatientBloodType, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientObservations', $PatientObservations, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getPatient($PatientId); // Update current object data with modified info
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
        public function reactivatePatient( $PatientId ) {
            $this->getPatient($PatientId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $PatientStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblPatients SET 
                    PatientStatusId = :PatientStatusId 
                    WHERE 
                    PatientId = :PatientId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientStatusId', $PatientStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getPatient($PatientId); // Update current object data after reactivation
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
        public function deactivatePatient( $PatientId ) {
            $this->getPatient($PatientId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $PatientStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblPatients SET 
                    PatientStatusId = :PatientStatusId 
                    WHERE 
                    PatientId = :PatientId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientStatusId', $PatientStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
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
                        'PatientStatusId' => 0,
                        'PatientStatusValue' => 'Inactive'
                    ),
                    array(
                        'PatientStatusId' => 1,
                        'PatientStatusValue' => 'Active'
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