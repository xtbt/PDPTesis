<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class CaseM extends AppModelCore {
        
        // Class properties
        public $CaseId;
        public $PatientId;
        public $PatientFirstName;                   // tblPatients::PatientFirstName
        public $PatientLastName;                    // tblPatients::PatientLastName
        public $CaseDate;
        public $LastMenstrualPeriod;
        public $InitialBloodPressure;
        public $InitialWeight;
        public $InitialSymptoms;
        public $DeliveryDate;
        public $CaseNotes;
        public $UserId;
        public $CaseStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(CaseId,""),"]",COALESCE(PatientId,""),COALESCE(PatientFirstName,""),COALESCE(PatientLastName,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblCases AS t1 LEFT JOIN 
                                tblPatients AS t2 USING(PatientId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'CaseId';
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
                    t1.CaseId AS CaseId, 
                    t1.PatientId AS PatientId, 
                    t2.PatientFirstName AS PatientFirstName, 
                    t2.PatientLastName AS PatientLastName, 
                    t1.CaseDate AS CaseDate, 
                    t1.LastMenstrualPeriod AS LastMenstrualPeriod, 
                    t1.InitialBloodPressure AS InitialBloodPressure, 
                    t1.InitialWeight AS InitialWeight, 
                    t1.InitialSymptoms AS InitialSymptoms, 
                    t1.DeliveryDate AS DeliveryDate, 
                    t1.CaseNotes AS CaseNotes, 
                    t1.UserId AS UserId, 
                    t1.CaseStatusId AS CaseStatusId 
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
        public function getCase( $CaseId ) {
            $this->DB_initProperties();
            if (is_numeric($CaseId)) {
                $this->SQL_Conditions .= ' AND CaseId = :CaseId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.CaseId AS CaseId, 
                    t1.PatientId AS PatientId, 
                    t2.PatientFirstName AS PatientFirstName, 
                    t2.PatientLastName AS PatientLastName, 
                    t1.CaseDate AS CaseDate, 
                    t1.LastMenstrualPeriod AS LastMenstrualPeriod, 
                    t1.InitialBloodPressure AS InitialBloodPressure, 
                    t1.InitialWeight AS InitialWeight, 
                    t1.InitialSymptoms AS InitialSymptoms, 
                    t1.DeliveryDate AS DeliveryDate, 
                    t1.CaseNotes AS CaseNotes, 
                    t1.UserId AS UserId, 
                    t1.CaseStatusId AS CaseStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$CaseId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$CaseId]);
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
        public function createCase( $PatientId, $CaseDate, $LastMenstrualPeriod, $InitialBloodPressure, $InitialWeight, $InitialSymptoms, $DeliveryDate, $CaseNotes ) {
            $this->DB_initProperties();
            $CaseId = NULL; // NULL by default on new records
            $CaseDate = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $CaseDate = $CaseDate->format('Y-m-d'); // String converted
            $DeliveryDate = NULL; // NULL by default on new records
            $CaseNotes = NULL; // NULL by default on new records
            $UserId = $this->appUserId; // Case creator
            $CaseStatusId = 1; // 1(Ongoing Pregnancy) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblCases VALUES (
                    :CaseId, 
                    :PatientId, 
                    :CaseDate, 
                    :LastMenstrualPeriod, 
                    :InitialBloodPressure, 
                    :InitialWeight, 
                    :InitialSymptoms, 
                    :DeliveryDate, 
                    :CaseNotes, 
                    :UserId, 
                    :CaseStatusId)';
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseDate', $CaseDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastMenstrualPeriod', $LastMenstrualPeriod, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InitialBloodPressure', $InitialBloodPressure, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InitialWeight', $InitialWeight, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':InitialSymptoms', $InitialSymptoms, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':DeliveryDate', $DeliveryDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseNotes', $CaseNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseStatusId', $CaseStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $CaseId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $CaseId];
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
        public function updateCase( $CaseId, $PatientId, $CaseDate, $LastMenstrualPeriod, $InitialBloodPressure, $InitialWeight, $InitialSymptoms, $DeliveryDate, $CaseNotes ) {
            $this->getCase($CaseId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ($this->PatientId == $PatientId 
            && $this->CaseDate == $CaseDate 
            && $this->LastMenstrualPeriod == $LastMenstrualPeriod 
            && $this->InitialBloodPressure == $InitialBloodPressure 
            && $this->InitialWeight == $InitialWeight 
            && $this->InitialSymptoms == $InitialSymptoms 
            && $this->DeliveryDate == $DeliveryDate 
            && $this->CaseNotes == $CaseNotes) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblCases SET 
                    PatientId = :PatientId, 
                    CaseDate = :CaseDate, 
                    LastMenstrualPeriod = :LastMenstrualPeriod, 
                    InitialBloodPressure = :InitialBloodPressure, 
                    InitialWeight = :InitialWeight, 
                    InitialSymptoms = :InitialSymptoms, 
                    DeliveryDate = :DeliveryDate, 
                    CaseNotes = :CaseNotes 
                    WHERE 
                    CaseId = :CaseId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':PatientId', $PatientId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseDate', $CaseDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastMenstrualPeriod', $LastMenstrualPeriod, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InitialBloodPressure', $InitialBloodPressure, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':InitialWeight', $InitialWeight, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':InitialSymptoms', $InitialSymptoms, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':DeliveryDate', $DeliveryDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseNotes', $CaseNotes, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCase($CaseId); // Update current object data with modified info
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
        // (REOPEN) REOPEN RECORD ON DB ***************************************
        // ********************************************************************
        public function reopenCase( $CaseId ) {
            $this->getCase($CaseId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseStatusId = 1; // Default opened status (1)

            try {
                $SQL_Query = 'UPDATE tblCases SET 
                    CaseStatusId = :CaseStatusId 
                    WHERE 
                    CaseId = :CaseId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseStatusId', $CaseStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCase($CaseId); // Update current object data after reactivation
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record reopened successfully';
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot reopen record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (CANCEL) CANCEL RECORD ON DB ***************************************
        // ********************************************************************
        public function cancelCase( $CaseId ) {
            $this->getCase($CaseId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $CaseStatusId = 0; // Default cancelled status (0)

            try {
                $SQL_Query = 'UPDATE tblCases SET 
                    CaseStatusId = :CaseStatusId 
                    WHERE 
                    CaseId = :CaseId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseStatusId', $CaseStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCase($CaseId); // Update current object data after deactivation
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record cancelled successfully';
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot cancel record';
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
        public function changeCaseStatus( $CaseId, $CaseStatusId ) {
            $this->getCase($CaseId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            try {
                $SQL_Query = 'UPDATE tblCases SET 
                    CaseStatusId = :CaseStatusId 
                    WHERE 
                    CaseId = :CaseId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CaseStatusId', $CaseStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CaseId', $CaseId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getCase($CaseId); // Update current object data after deactivation
                    $this->response['msg'] = '['.get_class($this).'] Ok: Record status changed successfully';
                }
                else {
                    $this->response['msg'] = '['.get_class($this).'] Error: Cannot change record status';
                };
            }
            catch (PDOException $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        public function getStatuses ( $queryString = NULL ) {
            $this->DB_initProperties();
            $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;

            try {
                // MANUAL STATIC RESPONSE *************************************
                $this->response['data'] = [
                    array(
                        'CaseStatusId' => 0,
                        'CaseStatusValue' => 'Sin Embarazo'
                    ),
                    array(
                        'CaseStatusId' => 1,
                        'CaseStatusValue' => 'Embarazo en curso'
                    ),
                    array(
                        'CaseStatusId' => 2,
                        'CaseStatusValue' => 'Parto Vaginal Pretermino'
                    ),
                    array(
                        'CaseStatusId' => 3,
                        'CaseStatusValue' => 'Parto Vaginal Regular'
                    ),
                    array(
                        'CaseStatusId' => 4,
                        'CaseStatusValue' => 'Parto por Cesarea Pretermino'
                    ),
                    array(
                        'CaseStatusId' => 5,
                        'CaseStatusValue' => 'Parto por Cesarea Regular'
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