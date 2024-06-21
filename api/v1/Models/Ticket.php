<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Ticket extends AppModelCore {
        
        // Class properties
        public $TicketId;
        public $TicketOpenedDateTime;
        public $ProblemCategoryId;
        public $ProblemCategoryName;            // tblProblemsCategories::ProblemCategoryName
        public $ProblemSubCategoryId;
        public $ProblemSubCategoryName;         // tblProblemsSubCategories::ProblemSubCategoryName
        public $TicketDescription;
        public $TicketLastComment;
        public $TicketClosedDateTime;
        public $UserId;
        public $Username;                       // tblUsers::Username
        public $TicketStatusId;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(TicketId,""),"]",COALESCE(ProblemCategoryName,""),COALESCE(ProblemSubCategoryName,""),COALESCE(Username,""))';

        // Constructor (DB Connection)
        public function __construct() {
            global $appBearerToken, $appUserId;
            $this->appBearerToken = $appBearerToken;
            $this->appUserId = $appUserId;

            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblTickets AS t1 LEFT JOIN 
                                tblProblemsCategories AS t2 USING(ProblemCategoryId) LEFT JOIN 
                                tblProblemsSubCategories AS t3 USING(ProblemSubCategoryId) LEFT JOIN 
                                tblUsers AS t4 USING(UserId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'TicketId';
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
                    t1.TicketId AS TicketId, 
                    t1.TicketOpenedDateTime AS TicketOpenedDateTime, 
                    t1.ProblemCategoryId AS ProblemCategoryId, 
                    t2.ProblemCategoryName AS ProblemCategoryName, 
                    t1.ProblemSubCategoryId AS ProblemSubCategoryId, 
                    t3.ProblemSubCategoryName AS ProblemSubCategoryName, 
                    t1.TicketDescription AS TicketDescription, 
                    t1.TicketLastComment AS TicketLastComment, 
                    t1.TicketClosedDateTime AS TicketClosedDateTime, 
                    t1.UserId AS UserId, 
                    t4.Username AS Username, 
                    t1.TicketStatusId AS TicketStatusId 
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
        public function getTicket($TicketId) {
            $this->DB_initProperties();
            if (is_numeric($TicketId)) {
                $this->SQL_Conditions .= ' AND TicketId = :TicketId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msg'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.TicketId AS TicketId, 
                    t1.TicketOpenedDateTime AS TicketOpenedDateTime, 
                    t1.ProblemCategoryId AS ProblemCategoryId, 
                    t2.ProblemCategoryName AS ProblemCategoryName, 
                    t1.ProblemSubCategoryId AS ProblemSubCategoryId, 
                    t3.ProblemSubCategoryName AS ProblemSubCategoryName, 
                    t1.TicketDescription AS TicketDescription, 
                    t1.TicketLastComment AS TicketLastComment, 
                    t1.TicketClosedDateTime AS TicketClosedDateTime, 
                    t1.UserId AS UserId, 
                    t4.Username AS Username, 
                    t1.TicketStatusId AS TicketStatusId 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['msg'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$TicketId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                // Static implementation of image URL *********************
                $imageCollection = $this->getFileList(SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/', $TicketId);
                if ($imageCollection != false) {
                    if (strpos($imageCollection[0], '(0)') !== false)
                        $this->response['data'][$TicketId]['TicketMainImage'] = $imageCollection[0];
                    $this->response['data'][$TicketId]['TicketExtraImage'] = $imageCollection;
                };
                // ************************************************************
                $this->updateProperties($this->response['data'][$TicketId]);
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
        public function createTicket( $ProblemCategoryId, $ProblemSubCategoryId, $TicketDescription ) {
            $this->DB_initProperties();
            $TicketId = NULL; // NULL by default on new records
            $TicketOpenedDateTime = new DateTime(date('Y-m-d H:i:s')); // Current DateTime object
            $TicketOpenedDateTime = $TicketOpenedDateTime->format('Y-m-d H:i:s'); // String converted
            $TicketLastComment = NULL; // NULL by default on new records
            $TicketClosedDateTime = NULL; // NULL by default on new records
            $UserId = $this->appUserId; // Ticket creator
            $TicketStatusId = 1; // 1(Pending) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblTickets VALUES (
                    :TicketId, 
                    :TicketOpenedDateTime, 
                    :ProblemCategoryId, 
                    :ProblemSubCategoryId, 
                    :TicketDescription, 
                    :TicketLastComment, 
                    :TicketClosedDateTime, 
                    :UserId, 
                    :TicketStatusId)';
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketOpenedDateTime', $TicketOpenedDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ProblemCategoryId', $ProblemCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ProblemSubCategoryId', $ProblemSubCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketDescription', $TicketDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TicketLastComment', $TicketLastComment, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TicketClosedDateTime', $TicketClosedDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketStatusId', $TicketStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $TicketId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['count'] = 1;
                    $this->response['data'] = ['id' => $TicketId];
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
        public function updateTicket( $TicketId, $ProblemCategoryId, $ProblemSubCategoryId, $TicketDescription ) {
            $this->getTicket($TicketId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            // Confirm changes on at least 1 field ----------------------------
            if ($this->ProblemCategoryId == $ProblemCategoryId 
            && $this->ProblemSubCategoryId == $ProblemSubCategoryId 
            && $this->TicketDescription == $TicketDescription) {
                $this->response['msg'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblTickets SET 
                    ProblemCategoryId = :ProblemCategoryId, 
                    ProblemSubCategoryId = :ProblemSubCategoryId, 
                    TicketDescription = :TicketDescription 
                    WHERE 
                    TicketId = :TicketId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ProblemCategoryId', $ProblemCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ProblemSubCategoryId', $ProblemSubCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketDescription', $TicketDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTicket($TicketId); // Update current object data with modified info
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
        public function reopenTicket( $TicketId ) {
            $this->getTicket($TicketId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $TicketStatusId = 1; // Default opened status (1)

            try {
                $SQL_Query = 'UPDATE tblTickets SET 
                    TicketStatusId = :TicketStatusId 
                    WHERE 
                    TicketId = :TicketId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketStatusId', $TicketStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTicket($TicketId); // Update current object data after reactivation
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
        public function cancelTicket( $TicketId ) {
            $this->getTicket($TicketId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $TicketStatusId = 0; // Default cancelled status (0)

            try {
                $SQL_Query = 'UPDATE tblTickets SET 
                    TicketStatusId = :TicketStatusId 
                    WHERE 
                    TicketId = :TicketId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketStatusId', $TicketStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTicket($TicketId); // Update current object data after deactivation
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
        public function changeTicketStatus( $TicketId, $TicketStatusId ) {
            $this->getTicket($TicketId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information

            try {
                $SQL_Query = 'UPDATE tblTickets SET 
                    TicketStatusId = :TicketStatusId 
                    WHERE 
                    TicketId = :TicketId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketStatusId', $TicketStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTicket($TicketId); // Update current object data after deactivation
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

        public function closeTicket ( $TicketId, $TicketLastComment ) {
            $this->getTicket($TicketId); // Get current record data from DB
            $this->initResponseData(); // Reset Response Array Information
            $TicketStatusId = 3; // Default cancelled status (0)

            try {
                $SQL_Query = 'UPDATE tblTickets SET 
                    TicketLastComment = :TicketLastComment, 
                    TicketStatusId = :TicketStatusId 
                    WHERE 
                    TicketId = :TicketId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TicketStatusId', $TicketStatusId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TicketLastComment', $TicketLastComment, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TicketId', $TicketId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTicket($TicketId); // Update current object data after deactivation
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
                        'TicketStatusId' => 0,
                        'TicketStatusValue' => 'Cancelado'
                    ),
                    array(
                        'TicketStatusId' => 1,
                        'TicketStatusValue' => 'Pendiente'
                    ),
                    array(
                        'TicketStatusId' => 2,
                        'TicketStatusValue' => 'En Progreso'
                    ),
                    array(
                        'TicketStatusId' => 3,
                        'TicketStatusValue' => 'Finalizado'
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

        public function uploadFile ( $TicketId, $Context ) {
            try {
                // File extension
                $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                // Max filesize
                $maxFilesize = 0.5 * 1024 * 1024; // 500KB
                // Allowed file formats
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['image']['tmp_name']);
                $allowedFileFormats = ['image/jpg', 'image/jpeg', 'image/png'];

                if ($_FILES['image']['size'] <= $maxFilesize && in_array($mimeType, $allowedFileFormats)) {
                    if ($Context == 'TicketMainImage') {
                        $fileDirectory = SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/';
                        $fileName = $TicketId . '(0).' . $fileExtension;
                        $fileURL = $fileDirectory . $fileName;
                    }
                    else if ($Context == 'TicketExtraImage') {
                        $fileList = $this->getFileList(SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/', $TicketId);
                        $fileDirectory = SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/';
                        $fileName = $TicketId . '(' . ($fileList === false ? '0' : count($fileList)) . ').' . $fileExtension;
                        $fileURL = $fileDirectory . $fileName;
                    }
                    else {
                        $this->response['data']['count'] = 0; // Invalid Context
                    };
                } else {
                    throw new Exception("Invalid filesize/filetype.");
                };

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $fileURL))
                    throw new Exception('Cannot upload image to target URL');

                $this->response['msg'] = '['.get_class($this).'] OK: File Uploaded';
                $this->response['count'] = 1; // Image uploaded
                $this->response['globalCount'] = $this->response['count'];
                // ############## STATIC CLIENT URL FOR IMAGES ################
                if ($Context == 'TicketMainImage')
                    $this->response[$Context] = '/unitickets/media/fotos/' . $fileName;
                else if ($Context == 'TicketExtraImage')
                    $this->response[$Context] = $this->getFileList(SERVER_FILESYSTEM_PREFIX . 'unitickets/media/fotos/', $TicketId);
                else
                    true;
                // ############################################################
                return $this->response; // Return response
            }
            catch (Exception $ex) {
                $this->response['msg'] = '['.get_class($this).'] Error: Upload Exception';
                $this->response['error'] = $ex->getMessage();
                $this->response['debug'] = $fileURL;
                return $this->response; // Return response with error
            };
        }
    }
?>