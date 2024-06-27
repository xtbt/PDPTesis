<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Patient_CPField extends AppModelCore {
        
        // Class properties
        public $Patient_CPFieldId;
        public $PatientId;
        public $CPFieldId;
        public $Patient_CPFieldNote;
        public $Patient_CPFieldCreatedAt;
        public $Patient_CPFieldStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(Patient_CPFieldId,""),"]",COALESCE(PatientId,""),"|",COALESCE(CPFieldId,""),"|",COALESCE(Patient_CPFieldNote,""))';
        
        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;
            
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblPatients_CPFields';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'Patient_CPFieldId';
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
                    Patient_CPFieldId, 
                    PatientId, 
                    CPFieldId, 
                    Patient_CPFieldNote, 
                    Patient_CPFieldCreatedAt, 
                    Patient_CPFieldStatusId 
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
        public function getPatient_CPField( $Patient_CPFieldId ) {
            $this->DB_initProperties();
            if (is_numeric($Patient_CPFieldId)) {
                $this->SQL_Conditions .= ' AND Patient_CPFieldId = :Patient_CPFieldId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    Patient_CPFieldId, 
                    PatientId, 
                    CPFieldId, 
                    Patient_CPFieldNote, 
                    Patient_CPFieldCreatedAt, 
                    Patient_CPFieldStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldId', $Patient_CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$Patient_CPFieldId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$Patient_CPFieldId]);
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
        public function createPatient_CPField( $PatientId, $CPFieldId, $Patient_CPFieldNote ) {
            $this->DB_initProperties();
            $Patient_CPFieldId = NULL; // NULL by default on new records
            $Patient_CPFieldCreatedAt = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $Patient_CPFieldCreatedAt = $Patient_CPFieldCreatedAt->format('Y-m-d'); // String converted
            $Patient_CPFieldStatusId = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblPatients_CPFields VALUES (
                    :Patient_CPFieldId, 
                    :PatientId, 
                    :CPFieldId, 
                    :Patient_CPFieldNote, 
                    :Patient_CPFieldCreatedAt, 
                    :Patient_CPFieldStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldId', $Patient_CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CPFieldId', $CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldNote', $Patient_CPFieldNote, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldCreatedAt', $Patient_CPFieldCreatedAt, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldStatusId', $Patient_CPFieldStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $Patient_CPFieldId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $Patient_CPFieldId];
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
        public function updatePatient_CPField( $Patient_CPFieldId, $PatientId, $CPFieldId, $Patient_CPFieldNote ) {
            $this->getPatient_CPField($Patient_CPFieldId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ($this->PatientId == $PatientId 
            && $this->CPFieldId == $CPFieldId 
            && $this->Patient_CPFieldNote == $Patient_CPFieldNote) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblPatients_CPFields SET 
                    PatientId = :PatientId, 
                    CPFieldId = :CPFieldId, 
                    Patient_CPFieldNote = :Patient_CPFieldNote  
                    WHERE 
                    Patient_CPFieldId = :Patient_CPFieldId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CPFieldId', $CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldNote', $Patient_CPFieldNote, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldId', $Patient_CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getPatient_CPField($Patient_CPFieldId); // Update current object data with modified info
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
        public function reactivatePatient_CPField( $Patient_CPFieldId ) {
            $this->getPatient_CPField($Patient_CPFieldId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $Patient_CPFieldStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblPatients_CPFields SET 
                    Patient_CPFieldStatusId = :Patient_CPFieldStatusId 
                    WHERE 
                    Patient_CPFieldId = :Patient_CPFieldId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldStatusId', $Patient_CPFieldStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldId', $Patient_CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getPatient_CPField($Patient_CPFieldId); // Update current object data after reactivation
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
        public function deactivatePatient_CPField( $Patient_CPFieldId ) {
            $this->getPatient_CPField($Patient_CPFieldId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $Patient_CPFieldStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblPatients_CPFields SET 
                    Patient_CPFieldStatusId = :Patient_CPFieldStatusId 
                    WHERE 
                    Patient_CPFieldId = :Patient_CPFieldId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldStatusId', $Patient_CPFieldStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Patient_CPFieldId', $Patient_CPFieldId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getPatient_CPField($Patient_CPFieldId); // Update current object data after deactivation
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
                        'Patient_CPFieldStatusId' => 0,
                        'Patient_CPFieldStatusValue' => 'Inactive'
                    ),
                    array(
                        'Patient_CPFieldStatusId' => 1,
                        'Patient_CPFieldStatusValue' => 'Active'
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