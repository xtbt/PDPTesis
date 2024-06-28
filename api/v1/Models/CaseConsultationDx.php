<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class CaseConsultationDx extends AppModelCore {
        
        // Class properties
        public $CaseConsultationDxId;
        public $CaseConsultationId;
        public $CaseConsultationDxCIE11;
        public $CaseConsultationDxDescription;
        public $CaseConsultationDxStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(CaseConsultationDxId,""),"]",COALESCE(CaseConsultationId,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblCasesConsultationsDx AS t1 LEFT JOIN 
                                tblCasesConsultations AS t2 USING(CaseConsultationId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'CaseConsultationDxId';
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
                    t1.CaseConsultationDxId AS CaseConsultationDxId, 
                    t1.CaseConsultationId AS CaseConsultationId, 
                    t1.CaseConsultationDxCIE11 AS CaseConsultationDxCIE11, 
                    t1.CaseConsultationDxDescription AS CaseConsultationDxDescription, 
                    t1.CaseConsultationDxStatusId AS CaseConsultationDxStatusId 
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
        public function getCaseConsultationDx( $CaseConsultationDxId ) {
            $this->DB_initProperties();
            if (is_numeric($CaseConsultationDxId)) {
                $this->SQL_Conditions .= ' AND CaseConsultationDxId = :CaseConsultationDxId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.CaseConsultationDxId AS CaseConsultationDxId, 
                    t1.CaseConsultationId AS CaseConsultationId, 
                    t1.CaseConsultationDxCIE11 AS CaseConsultationDxCIE11, 
                    t1.CaseConsultationDxDescription AS CaseConsultationDxDescription, 
                    t1.CaseConsultationDxStatusId AS CaseConsultationDxStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxId', $CaseConsultationDxId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$CaseConsultationDxId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$CaseConsultationDxId]);
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
        public function createCaseConsultationDx( $CaseConsultationId, $CaseConsultationDxCIE11, $CaseConsultationDxDescription ) {
            $this->DB_initProperties();
            $CaseConsultationDxId = NULL; // NULL by default on new records
            $CaseConsultationDxCIE11 = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $CaseConsultationDxCIE11 = $CaseConsultationDxCIE11->format('Y-m-d'); // String converted
            $CaseConsultationDxStatusId = 1; // 1(Pending) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblCasesConsultationsDx VALUES (
                    :CaseConsultationDxId, 
                    :CaseConsultationId, 
                    :CaseConsultationDxCIE11, 
                    :CaseConsultationDxDescription, 
                    :CaseConsultationDxStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxId', $CaseConsultationDxId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseConsultationId', $CaseConsultationId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxCIE11', $CaseConsultationDxCIE11, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxDescription', $CaseConsultationDxDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxStatusId', $CaseConsultationDxStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $CaseConsultationDxId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $CaseConsultationDxId];
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
        public function updateCaseConsultationDx( $CaseConsultationDxId, $CaseConsultationId, $CaseConsultationDxCIE11, $CaseConsultationDxDescription ) {
            $this->getCaseConsultationDx($CaseConsultationDxId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ( $this->CaseConsultationId == $CaseConsultationId && $this->CaseConsultationDxCIE11 == $CaseConsultationDxCIE11 
            && $this->CaseConsultationDxDescription == $CaseConsultationDxDescription ) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblCasesConsultationsDx SET 
                    CaseConsultationId = :CaseConsultationId, 
                    CaseConsultationDxCIE11 = :CaseConsultationDxCIE11, 
                    CaseConsultationDxDescription = :CaseConsultationDxDescription 
                    WHERE 
                    CaseConsultationDxId = :CaseConsultationDxId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseConsultationId', $CaseConsultationId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxCIE11', $CaseConsultationDxCIE11, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxDescription', $CaseConsultationDxDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxId', $CaseConsultationDxId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseConsultationDx($CaseConsultationDxId); // Update current object data with modified info
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
        public function reactivateCaseConsultationDx( $CaseConsultationDxId ) {
            $this->getCaseConsultationDx($CaseConsultationDxId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseConsultationDxStatusId = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblCasesConsultationsDx SET 
                    CaseConsultationDxStatusId = :CaseConsultationDxStatusId 
                    WHERE 
                    CaseConsultationDxId = :CaseConsultationDxId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxStatusId', $CaseConsultationDxStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxId', $CaseConsultationDxId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseConsultationDx($CaseConsultationDxId); // Update current object data after reactivation
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
        public function deactivateCaseConsultationDx( $CaseConsultationDxId ) {
            $this->getCaseConsultationDx($CaseConsultationDxId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseConsultationDxStatusId = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblCasesConsultationsDx SET 
                    CaseConsultationDxStatusId = :CaseConsultationDxStatusId 
                    WHERE 
                    CaseConsultationDxId = :CaseConsultationDxId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxStatusId', $CaseConsultationDxStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseConsultationDxId', $CaseConsultationDxId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCaseConsultationDx($CaseConsultationDxId); // Update current object data after deactivation
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
                        'CaseConsultationDxStatusId' => 0,
                        'CaseConsultationDxStatusValue' => 'Inactive'
                    ),
                    array(
                        'CaseConsultationDxStatusId' => 1,
                        'CaseConsultationDxStatusValue' => 'Active'
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