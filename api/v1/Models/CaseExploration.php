<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class CaseExploration extends AppModelCore {
        
        // Class properties
        public $CaseExplorationId;
        public $CaseId;
        public $CaseExplorationDate;
        public $CaseExplorationNotes;
        public $CaseExplorationStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(CaseExplorationId,""),"]",COALESCE(CaseId,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblCasesExplorations AS t1 LEFT JOIN 
                                tblCases AS t2 USING(CaseId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'CaseExplorationId';
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
                    t1.CaseExplorationId AS CaseExplorationId, 
                    t1.CaseId AS CaseId, 
                    t1.CaseExplorationDate AS CaseExplorationDate, 
                    t1.CaseExplorationNotes AS CaseExplorationNotes, 
                    t1.CaseExplorationStatusId AS CaseExplorationStatusId 
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
        public function getCaseExploration( $CaseExplorationId ) {
            $this->DB_initProperties();
            if (is_numeric($CaseExplorationId)) {
                $this->SQL_Conditions .= ' AND CaseExplorationId = :CaseExplorationId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.CaseExplorationId AS CaseExplorationId, 
                    t1.CaseId AS CaseId, 
                    t1.CaseExplorationDate AS CaseExplorationDate, 
                    t1.CaseExplorationNotes AS CaseExplorationNotes, 
                    t1.CaseExplorationStatusId AS CaseExplorationStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseExplorationId', $CaseExplorationId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$CaseExplorationId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$CaseExplorationId]);
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
        public function createCaseExploration( $CaseId, $CaseExplorationDate, $CaseExplorationNotes ) {
            $this->DB_initProperties();
            $CaseExplorationId = NULL; // NULL by default on new records
            $CaseExplorationDate = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $CaseExplorationDate = $CaseExplorationDate->format('Y-m-d'); // String converted
            $CaseExplorationStatusId = 1; // 1(Pending) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblCasesExplorations VALUES (
                    :CaseExplorationId, 
                    :CaseId, 
                    :CaseExplorationDate, 
                    :CaseExplorationNotes, 
                    :CaseExplorationStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseExplorationId', $CaseExplorationId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseExplorationDate', $CaseExplorationDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseExplorationNotes', $CaseExplorationNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseExplorationStatusId', $CaseExplorationStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $CaseExplorationId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $CaseExplorationId];
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
        public function updateCaseExploration( $CaseExplorationId, $CaseId, $CaseExplorationDate, $CurrentBloodPressure, $CurrentWeight, $CurrentSymptoms, $CaseExplorationNotes ) {
            $this->getCaseExploration($CaseExplorationId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ( $this->CaseId == $CaseId && $this->CaseExplorationDate == $CaseExplorationDate 
            && $this->CaseExplorationNotes == $CaseExplorationNotes ) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblCasesExplorations SET 
                    CaseId = :CaseId, 
                    CaseExplorationDate = :CaseExplorationDate, 
                    CurrentBloodPressure = :CurrentBloodPressure, 
                    CurrentWeight = :CurrentWeight, 
                    CurrentSymptoms = :CurrentSymptoms, 
                    CaseExplorationNotes = :CaseExplorationNotes 
                    WHERE 
                    CaseExplorationId = :CaseExplorationId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseExplorationDate', $CaseExplorationDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CurrentBloodPressure', $CurrentBloodPressure, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CurrentWeight', $CurrentWeight, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CurrentSymptoms', $CurrentSymptoms, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseExplorationNotes', $CaseExplorationNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseExplorationStatusId', $CaseExplorationStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseExplorationId', $CaseExplorationId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseExploration($CaseExplorationId); // Update current object data with modified info
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
        public function reactivateCaseExploration( $CaseExplorationId ) {
            $this->getCaseExploration($CaseExplorationId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseExplorationStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblCasesExplorations SET 
                    CaseExplorationStatusId = :CaseExplorationStatusId 
                    WHERE 
                    CaseExplorationId = :CaseExplorationId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseExplorationStatusId', $CaseExplorationStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseExplorationId', $CaseExplorationId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseExploration($CaseExplorationId); // Update current object data after reactivation
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
        public function deactivateCaseExploration( $CaseExplorationId ) {
            $this->getCaseExploration($CaseExplorationId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseExplorationStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblCasesExplorations SET 
                    CaseExplorationStatusId = :CaseExplorationStatusId 
                    WHERE 
                    CaseExplorationId = :CaseExplorationId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseExplorationStatusId', $CaseExplorationStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseExplorationId', $CaseExplorationId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseExploration($CaseExplorationId); // Update current object data after deactivation
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
        public function getStatuses ( $queryString = NULL ) {
            $this->DB_initProperties();
            $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;

            try {
                // MANUAL STATIC RESPONSE *************************************
                $this->response['data'] = [
                    array(
                        'CaseExplorationStatusId' => 0,
                        'CaseExplorationStatusValue' => 'Inactive'
                    ),
                    array(
                        'CaseExplorationStatusId' => 1,
                        'CaseExplorationStatusValue' => 'Active'
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