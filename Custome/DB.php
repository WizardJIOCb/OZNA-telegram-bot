<?php

class pDB {

    // Connect DB vars
    private static $databaseHost = '82.146.42.213';
    private static $databaseUsername = 'WizardJIOCb';
    private static $databasePassword = '7qQ#28051961';
    private static $databaseName = 'tg_bot';

    // Константы уровней доступа
    public const ACCESS_LEVEL_GUEST = 0;
    public const ACCESS_LEVEL_USER = 1;
    public const ACCESS_LEVEL_ADMIN = 2;
    public const ACCESS_LEVEL_SUPER_ADMIN = 3;

    // Get user data (for all commands)
    public static function getUserData($user_id){

        $user = self::select("select * from ozna_data o where user_id = ".$user_id.";")[0];        

        if (!$user){
            self::insert("insert into ozna_data set user_id = ".$user_id.";");
            return [];
        }

        $ret = $user ? json_decode($user["data"], true) : [];

        if (!isset($ret["fio"])) $ret["fio"] = "Без ФИО";
        if (!isset($ret["functional_direction"])) $ret["functional_direction"] = "Не указано";
        // Установка уровня доступа по умолчанию (гость)
        if (!isset($ret["access_level"])) $ret["access_level"] = self::ACCESS_LEVEL_GUEST;

        return $ret;
    }

    // Set user data !!!TODO SQL Injection - FIX ME!!!
    public static function setUserData($user_id, $data){

        $mysqli = mysqli_connect(self::$databaseHost, self::$databaseUsername, self::$databasePassword, self::$databaseName); 
        $sqlResult = mysqli_query($mysqli, "update ozna_data od set od.`data` = '".json_encode($data, JSON_UNESCAPED_UNICODE)."' where user_id = ".$user_id.";");                    
        return $sqlResult;
    }

    public static function select($query){

        // Fetch user associative array
        $mysqli = mysqli_connect(self::$databaseHost, self::$databaseUsername, self::$databasePassword, self::$databaseName); 
        $sqlResult = mysqli_query($mysqli, $query);    
        $rows = [];
        while ($rowr = $sqlResult->fetch_assoc()) {
            $rows[] = $rowr;
        }
        return $rows;
    }

    public static function update($query){

        // Fetch user associative array
        $mysqli = mysqli_connect(self::$databaseHost, self::$databaseUsername, self::$databasePassword, self::$databaseName); 
        $sqlResult = mysqli_query($mysqli, $query);    
        $rows = [];
        while ($rowr = $sqlResult->fetch_assoc()) {
            $rows[] = $rowr;
        }
        return $rows;
    }

    public static function insert($query){

        // Fetch user associative array
        $mysqli = mysqli_connect(self::$databaseHost, self::$databaseUsername, self::$databasePassword, self::$databaseName); 
        $sqlResult = mysqli_query($mysqli, $query);            
        return $sqlResult;
    }

    // Update user functional direction
    public static function updateUserFunctionalDirection($user_id, $functional_direction){
        
        // Get current user data
        $user_data = self::getUserData($user_id);
        
        // Set default value if not specified
        if (!isset($user_data["functional_direction"])) {
            $user_data["functional_direction"] = "Не указано";
        }
        
        // Update functional direction
        $user_data["functional_direction"] = $functional_direction;
        
        // Save updated data
        return self::setUserData($user_id, $user_data);
    }

    // Update user FIO
    public static function updateUserFio($user_id, $fio){
        
        // Get current user data
        $user_data = self::getUserData($user_id);
        
        // Set default value if not specified
        if (!isset($user_data["fio"])) {
            $user_data["fio"] = "Без ФИО";
        }
        
        // Update FIO
        $user_data["fio"] = $fio;
        
        // Save updated data
        return self::setUserData($user_id, $user_data);
    }

    // Update user access level
    public static function updateUserAccessLevel($user_id, $access_level){
        
        // Get current user data
        $user_data = self::getUserData($user_id);
        
        // Set default value if not specified
        if (!isset($user_data["access_level"])) {
            $user_data["access_level"] = self::ACCESS_LEVEL_GUEST;
        }
        
        // Update access level
        $user_data["access_level"] = $access_level;
        
        // Save updated data
        return self::setUserData($user_id, $user_data);
    }

    // Get user access level
    public static function getUserAccessLevel($user_id){
        $user_data = self::getUserData($user_id);
        return $user_data["access_level"] ?? self::ACCESS_LEVEL_GUEST;
    }

    // Add access request
    public static function addAccessRequest($request_data) {
        // Get current user data
        $user_data = self::getUserData($request_data['user_id']);
        
        // Initialize access_requests array if it doesn't exist
        if (!isset($user_data["access_requests"])) {
            $user_data["access_requests"] = [];
        }
        
        // Add the new request
        $user_data["access_requests"][] = $request_data;
        
        // Save updated data
        return self::setUserData($request_data['user_id'], $user_data);
    }

    // Get all access requests
    public static function getAccessRequests() {
        // For simplicity, we'll return requests from a special user (e.g., user_id = 0)
        // In a real implementation, you might want to store requests in a separate table
        $user_data = self::getUserData(0);
        return isset($user_data["all_access_requests"]) ? $user_data["all_access_requests"] : [];
    }

    // Save all access requests
    public static function saveAccessRequests($requests) {
        // For simplicity, we'll save requests in a special user (e.g., user_id = 0)
        // In a real implementation, you might want to store requests in a separate table
        $user_data = self::getUserData(0);
        $user_data["all_access_requests"] = $requests;
        return self::setUserData(0, $user_data);
    }

    // Get pending access requests
    public static function getPendingAccessRequests() {
        $all_requests = self::getAccessRequests();
        $pending_requests = [];
        
        foreach ($all_requests as $request) {
            if (!isset($request['status']) || $request['status'] === 'pending') {
                $pending_requests[] = $request;
            }
        }
        
        return $pending_requests;
    }

    // Approve access request
    public static function approveAccessRequest($request_id, $admin_id) {
        $all_requests = self::getAccessRequests();
        
        foreach ($all_requests as &$request) {
            if (isset($request['id']) && $request['id'] == $request_id) {
                $request['status'] = 'approved';
                $request['processed_by'] = $admin_id;
                $request['processed_at'] = time();
                break;
            }
        }
        
        return self::saveAccessRequests($all_requests);
    }

    // Reject access request
    public static function rejectAccessRequest($request_id, $admin_id) {
        $all_requests = self::getAccessRequests();
        
        foreach ($all_requests as &$request) {
            if (isset($request['id']) && $request['id'] == $request_id) {
                $request['status'] = 'rejected';
                $request['processed_by'] = $admin_id;
                $request['processed_at'] = time();
                break;
            }
        }
        
        return self::saveAccessRequests($all_requests);
    }

    // Clear pending access requests
    public static function clearPendingAccessRequests() {
        $all_requests = self::getAccessRequests();
        $filtered_requests = [];
        
        foreach ($all_requests as $request) {
            if (isset($request['status']) && $request['status'] !== 'pending') {
                $filtered_requests[] = $request;
            }
        }
        
        return self::saveAccessRequests($filtered_requests);
    }
}