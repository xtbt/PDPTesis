<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class TicketUpdate extends AppModelCore {
        
        // Class properties
        public $TicketUpdateId;
        public $TicketId;
        public $TicketUpdateDateTime;
        public $TicketUpdateNote;
        public $UserId;
        public $Username;                       // tblUsers::Username
        public $TicketUpdateStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(TicketUpdateId,""),"]",COALESCE(TicketId,""),COALESCE(Username,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblTicketsUpdates AS t1 LEFT JOIN 
                                tblUsers AS t2 USING(UserId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'TicketUpdateId';
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
                    t1.TicketUpdateId AS TicketUpdateId, 
                    t1.TicketId AS TicketId, 
                    t1.TicketUpdateDateTime AS TicketUpdateDateTime, 
                    t1.TicketUpdateNote AS TicketUpdateNote, 
                    t1.UserId AS UserId, 
                    t2.Username AS Username, 
                    t1.TicketUpdateStatusId AS TicketUpdateStatusId 
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
        public function getTicketUpdate($TicketUpdateId) {
            $this->DB_initProperties();
            if (is_numeric($TicketUpdateId)) {
                $this->SQL_Conditions .= ' AND TicketUpdateId = :TicketUpdateId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.TicketUpdateId AS TicketUpdateId, 
                    t1.TicketId AS TicketId, 
                    t1.TicketUpdateDateTime AS TicketUpdateDateTime, 
                    t1.TicketUpdateNote AS TicketUpdateNote, 
                    t1.UserId AS UserId, 
                    t2.Username AS Username, 
                    t1.TicketUpdateStatusId AS TicketUpdateStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketUpdateId', $TicketUpdateId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$TicketUpdateId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$TicketUpdateId]);
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
        public function createTicketUpdate( $TicketId, $TicketUpdateNote ) {
            $this->DB_initProperties();
            $TicketUpdateId = NULL; // NULL by default on new records
            $TicketUpdateDateTime = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $TicketUpdateDateTime = $TicketUpdateDateTime->format('Y-m-d H:i:s'); // String converted
            $UserId = $this->appUserId; // TicketUpdate creator
            $TicketUpdateStatusId = 1; // 1(Pending) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblTicketsUpdates VALUES (
                    :TicketUpdateId, 
                    :TicketId, 
                    :TicketUpdateDateTime, 
                    :TicketUpdateNote, 
                    :UserId, 
                    :TicketUpdateStatusId)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketUpdateId', $TicketUpdateId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketUpdateDateTime', $TicketUpdateDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TicketUpdateNote', $TicketUpdateNote, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketUpdateStatusId', $TicketUpdateStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $TicketUpdateId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $TicketUpdateId];
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

//         // ********************************************************************
//         // (UPDATE) UPDATE RECORD ON DB ***************************************
//         // ********************************************************************
//         public function updateTicketUpdate( $TicketUpdateId, $TicketUpdateNote ) {
//             $this->getTicketUpdate($TicketUpdateId); // Get current record data from DB
//             $this->initResponseData(); // Reset Response Array Information

//             // Confirm changes on at least 1 field ----------------------------
//             if ( $this->TicketUpdateNote == $TicketUpdateNote ) {
//                 $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
//                 return $this->response; // Return 'no modification' response
//             };
//             // ----------------------------------------------------------------

//             try {
//                 $SQL_Query = 'UPDATE tblTicketsUpdates SET 
//                     TicketUpdateId = :TicketUpdateId, 
//                     TicketUpdateNote = :TicketUpdateNote 
//                     WHERE 
//                     TicketUpdateId = :TicketUpdateId';
                  
//                 $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateNote', $TicketUpdateNote, PDO::PARAM_STR);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateId', $TicketUpdateId, PDO::PARAM_INT);
//                 $this->SQL_Sentence->execute();
                
//                 if ($this->SQL_Sentence->rowCount() != 0) {
//                     $this->getTicketUpdate($TicketUpdateId); // Update current object data with modified info
//                     $this->response['msg'] = '['.get_class($this).'] Ok: Record updated successfully';
//                 }
//                 else {
//                     $this->response['msg'] = '['.get_class($this).'] Error: Cannot update record';
//                 };
//             }
//             catch (PDOException $ex) {
//                 $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
//                 $this->response['error'] = $ex->getMessage();
//             };
//             return $this->response; // Return response Array
//         }

//         // ********************************************************************
//         // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
//         // ********************************************************************
//         public function reactivateTicketUpdate( $TicketUpdateId ) {
//             $this->getTicketUpdate($TicketUpdateId); // Get current record data from DB
//             $this->initResponseData(); // Reset Response Array Information
//             $TicketUpdateStatusId = 1; // Default active status (1)

//             try {
//                 $SQL_Query = 'UPDATE tblTicketsUpdates SET 
//                     TicketUpdateStatusId = :TicketUpdateStatusId 
//                     WHERE 
//                     TicketUpdateId = :TicketUpdateId';

//                 $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateStatusId', $TicketUpdateStatusId, PDO::PARAM_INT);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateId', $TicketUpdateId, PDO::PARAM_INT);
//                 $this->SQL_Sentence->execute();
                
//                 if ($this->SQL_Sentence->rowCount() != 0) {
//                     $this->getTicketUpdate($TicketUpdateId); // Update current object data after reactivation
//                     $this->response['msg'] = '['.get_class($this).'] Ok: Record reactivated successfully';
//                 }
//                 else {
//                     $this->response['msg'] = '['.get_class($this).'] Error: Cannot reactivate record';
//                 };
//             }
//             catch (PDOException $ex) {
//                 $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
//                 $this->response['error'] = $ex->getMessage();
//             };
//             return $this->response; // Return response Array
//         }

//         // ********************************************************************
//         // (DEACTIVATE) DEACTIVATE RECORD ON DB *******************************
//         // ********************************************************************
//         public function deactivateTicketUpdate( $TicketUpdateId ) {
//             $this->getTicketUpdate($TicketUpdateId); // Get current record data from DB
//             $this->initResponseData(); // Reset Response Array Information
//             $TicketUpdateStatusId = 0; // Default inactive status (0)

//             try {
//                 $SQL_Query = 'UPDATE tblTicketsUpdates SET 
//                     TicketUpdateStatusId = :TicketUpdateStatusId 
//                     WHERE 
//                     TicketUpdateId = :TicketUpdateId';

//                 $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateStatusId', $TicketUpdateStatusId, PDO::PARAM_INT);
//                 $this->SQL_Sentence->bindParam(':TicketUpdateId', $TicketUpdateId, PDO::PARAM_INT);
//                 $this->SQL_Sentence->execute();
                
//                 if ($this->SQL_Sentence->rowCount() != 0) {
//                     $this->getTicketUpdate($TicketUpdateId); // Update current object data after deactivation
//                     $this->response['msg'] = '['.get_class($this).'] Ok: Record deactivated successfully';
//                 }
//                 else {
//                     $this->response['msg'] = '['.get_class($this).'] Error: Cannot deactivate record';
//                 };
//             }
//             catch (PDOException $ex) {
//                 $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
//                 $this->response['error'] = $ex->getMessage();
//             };
//             return $this->response; // Return response Array
//         }

// // ****************************************************************************
// // ******* AUXILIARY METHODS (NON-CRUD) ***************************************
// // ****************************************************************************
//         public function getStatuses ( $queryString = NULL ) {
//             $this->DB_initProperties();
//             $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;

//             try {
//                 // MANUAL STATIC RESPONSE *************************************
//                 $this->response['data'] = [
//                     array(
//                         'TicketUpdateStatusId' => 0,
//                         'TicketUpdateStatusValue' => 'Inactive'
//                     ),
//                     array(
//                         'TicketUpdateStatusId' => 1,
//                         'TicketUpdateStatusValue' => 'Active'
//                     )
//                 ]; // Data Array to be included in the response
                
//                 $this->response['count'] = count($this->response['data']); // Row count to be included in the response
//                 $this->response['globalCount'] = $this->response['count'];
//                 // MANUAL STATIC RESPONSE *************************************

//                 return $this->response; // Return response with records
//             }
//             catch (PDOException $ex) {
//                 $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
//                 $this->response['error'] = $ex->getMessage();
//                 return $this->response; // Return response with error
//             };
//         }
    }
?>