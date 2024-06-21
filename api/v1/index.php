<?php
    // Main RESTful API script
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,PATCH,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    header("Cache-Control: no-cache, max-age=0, must-revalidate, no-store");

    require_once( './config.php' );

    $requestMethod = NULL;
    $resourceType = NULL;
	$resourceId = NULL;
    $queryString = NULL;
    $requestBody = NULL;
    
    $authorizedRequest = true;
    $appUserId = NULL;
    $appBearerToken = NULL;

    $apiResponse = [
        'statusCodeHeader' => 'HTTP/1.1 400 Bad Request', 
        'httpResponseCode' => 400, 
        'error' => 'Error: Bad request',
        'debug' => 'INITIALIZATION'
    ];

    $requestMethod = $_SERVER['REQUEST_METHOD'];

    $uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $uri = explode( '/', $uri );
    if (count($uri) > 5 && count($uri) < 8) {
        $resourceType = isset($uri[5]) ? $uri[5] : NULL;
        $resourceId = isset($uri[6]) ? $uri[6] : NULL;
    }
    parse_str($_SERVER['QUERY_STRING'], $queryString);
    $requestBody = $requestMethod == 'POST' || $requestMethod == 'PUT' || $requestMethod == 'PATCH' ? json_decode(file_get_contents('php://input'), true) : NULL;

    // ********************** AUTHORIZATION PROCESS ***************************
    preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $appBearerToken);
    $appBearerToken = $appBearerToken ? $appBearerToken[1] : NULL;
    $appUserId = $queryString['appUserId'] ? $queryString['appUserId'] : NULL;

    if ($queryString['appUserId']) unset($queryString['appUserId']);
    // ********************** AUTHORIZATION PROCESS ***************************

    if ( $resourceType && $authorizedRequest) {
        switch  ( $resourceType ) {
            case 'users':
                require_once( './Controllers/UserController.php' );
                $controllerObject = new UserController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'modules':
                require_once( './Controllers/ModuleController.php' );
                $controllerObject = new ModuleController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'modules-options':
                require_once( './Controllers/ModuleOptionController.php' );
                $controllerObject = new ModuleOptionController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'users_modules-options':
                require_once( './Controllers/User_ModuleOptionController.php' );
                $controllerObject = new User_ModuleOptionController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'institutions':
                require_once( './Controllers/InstitutionController.php' );
                $controllerObject = new InstitutionController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'areas':
                require_once( './Controllers/AreaController.php' );
                $controllerObject = new AreaController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'patients':
                require_once( './Controllers/PatientController.php' );
                $controllerObject = new PatientController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            // case 'problems-categories':
            //     require_once( './Controllers/ProblemCategoryController.php' );
            //     $controllerObject = new ProblemCategoryController( $requestMethod, $resourceId, $queryString, $requestBody );
            //     $apiResponse = $controllerObject->processRequest();
            //     break;
            // case 'problems-subcategories':
            //     require_once( './Controllers/ProblemSubCategoryController.php' );
            //     $controllerObject = new ProblemSubCategoryController( $requestMethod, $resourceId, $queryString, $requestBody );
            //     $apiResponse = $controllerObject->processRequest();
            //     break;
            // case 'tickets':
            //     require_once( './Controllers/TicketController.php' );
            //     $controllerObject = new TicketController( $requestMethod, $resourceId, $queryString, $requestBody );
            //     $apiResponse = $controllerObject->processRequest();
            //     break;
            // case 'tickets-updates':
            //     require_once( './Controllers/TicketUpdateController.php' );
            //     $controllerObject = new TicketUpdateController( $requestMethod, $resourceId, $queryString, $requestBody );
            //     $apiResponse = $controllerObject->processRequest();
            //     break;
            default:
                $apiResponse = [
                    'statusCodeHeader' => 'HTTP/1.1 404 Not Found', 
                    'httpResponseCode' => 404, 
                    'error' => 'Error: Resource not found.',
                    'debug' => [
                        'requestMethod' => $requestMethod,
                        'resourceType' => $resourceType,
                        'resourceId' => $resourceId,
                        'queryString' => $queryString,
                        'requestBody' => $requestBody,
                        'requestURI' => $_SERVER['REQUEST_URI']
                    ]
                ];
        };
    };
    header( $apiResponse['statusCodeHeader'] );
    http_response_code( $apiResponse['httpResponseCode'] );
    echo json_encode ( $apiResponse );
?>