<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class CaseLabtest extends AppModelCore {
        
        // Class properties
        public $CaseLabtestId;
        public $CaseId;
        public $CaseLabtestDate;
        public $CaseLabtestNotes;
        public $CaseLabtestStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(CaseLabtestId,""),"]",COALESCE(CaseId,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblCasesLabtests AS t1 LEFT JOIN 
                                tblCases AS t2 USING(CaseId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'CaseLabtestId';
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
                    t1.CaseLabtestId AS CaseLabtestId, 
                    t1.CaseId AS CaseId, 
                    t1.CaseLabtestDate AS CaseLabtestDate, 
                    t1.CaseLabtestNotes AS CaseLabtestNotes, 
                    t1.CaseLabtestStatusId AS CaseLabtestStatusId 
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
        public function getCaseLabtest( $CaseLabtestId ) {
            $this->DB_initProperties();
            if (is_numeric($CaseLabtestId)) {
                $this->SQL_Conditions .= ' AND CaseLabtestId = :CaseLabtestId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.CaseLabtestId AS CaseLabtestId, 
                    t1.CaseId AS CaseId, 
                    t1.CaseLabtestDate AS CaseLabtestDate, 
                    t1.CaseLabtestNotes AS CaseLabtestNotes, 
                    t1.CaseLabtestStatusId AS CaseLabtestStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseLabtestId', $CaseLabtestId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$CaseLabtestId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$CaseLabtestId]);
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
        public function createCaseLabtest( $CaseId, $CaseLabtestDate, $CaseLabtestNotes ) {
            $this->DB_initProperties();
            $CaseLabtestId = NULL; // NULL by default on new records
            $CaseLabtestDate = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $CaseLabtestDate = $CaseLabtestDate->format('Y-m-d'); // String converted
            $CaseLabtestStatusId = 1; // 1(Pending) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblCasesLabtests VALUES (
                    :CaseLabtestId, 
                    :CaseId, 
                    :CaseLabtestDate, 
                    :CaseLabtestNotes, 
                    :CaseLabtestStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseLabtestId', $CaseLabtestId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseLabtestDate', $CaseLabtestDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseLabtestNotes', $CaseLabtestNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseLabtestStatusId', $CaseLabtestStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $CaseLabtestId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $CaseLabtestId];
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
        public function updateCaseLabtest( $CaseLabtestId, $CaseId, $CaseLabtestDate, $CaseLabtestNotes ) {
            $this->getCaseLabtest($CaseLabtestId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ( $this->CaseId == $CaseId && $this->CaseLabtestDate == $CaseLabtestDate 
            && $this->CaseLabtestNotes == $CaseLabtestNotes ) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblCasesLabtests SET 
                    CaseId = :CaseId, 
                    CaseLabtestDate = :CaseLabtestDate, 
                    CaseLabtestNotes = :CaseLabtestNotes 
                    WHERE 
                    CaseLabtestId = :CaseLabtestId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseLabtestDate', $CaseLabtestDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseLabtestNotes', $CaseLabtestNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseLabtestId', $CaseLabtestId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseLabtest($CaseLabtestId); // Update current object data with modified info
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
        public function reactivateCaseLabtest( $CaseLabtestId ) {
            $this->getCaseLabtest($CaseLabtestId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseLabtestStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblCasesLabtests SET 
                    CaseLabtestStatusId = :CaseLabtestStatusId 
                    WHERE 
                    CaseLabtestId = :CaseLabtestId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseLabtestStatusId', $CaseLabtestStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseLabtestId', $CaseLabtestId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseLabtest($CaseLabtestId); // Update current object data after reactivation
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
        public function deactivateCaseLabtest( $CaseLabtestId ) {
            $this->getCaseLabtest($CaseLabtestId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseLabtestStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblCasesLabtests SET 
                    CaseLabtestStatusId = :CaseLabtestStatusId 
                    WHERE 
                    CaseLabtestId = :CaseLabtestId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseLabtestStatusId', $CaseLabtestStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseLabtestId', $CaseLabtestId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseLabtest($CaseLabtestId); // Update current object data after deactivation
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
                        'CaseLabtestStatusId' => 0,
                        'CaseLabtestStatusValue' => 'Inactive'
                    ),
                    array(
                        'CaseLabtestStatusId' => 1,
                        'CaseLabtestStatusValue' => 'Active'
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