<?php

namespace MyApplication;

use OAuth2\Interfaces\ITokenStorage;

use SmartFactory\SmartException;
use OAuth2\MissingParametersException;
use OAuth2\InvalidTokenException;
use OAuth2\TokenExpiredException;

use function SmartFactory\config_settings;
use function SmartFactory\dom_to_array;

class DemoTokenStorage implements ITokenStorage
{
    protected $supported_keys = ['user_id', 'client_id', 'access_token', 'refresh_token'];
    protected $records = [];
    protected $storage_file = null;
    
    protected function loadRecords()
    {
        $this->validateParameters();
        
        if (!file_exists($this->storage_file)) {
            throw new SmartException(sprintf("The stirage file '" . $this->storage_file . "' does not exist!"), SmartException::ERR_CODE_SYSTEM_ERROR);
        }
        
        $xmldoc = new \DOMDocument();
        
        if (!@$xmldoc->load($this->storage_file)) {
            throw new SmartException(sprintf("The stirage file '" . $this->storage_file . "' is invalid!"), SmartException::ERR_CODE_SYSTEM_ERROR);
        }
        
        dom_to_array($xmldoc->documentElement, $this->records);
    }
    
    protected function saveRecords()
    {
        $this->validateParameters();
        
        $xmldoc = new \DOMDocument("1.0", "UTF-8");
        
        $xmldoc->formatOutput = true;
        
        $root = $xmldoc->createElement("array");
        $root = $xmldoc->appendChild($root);
        
        if (!empty($this->records)) {
            \SmartFactory\array_to_dom($root, $this->records);
        }
        
        if (!@$xmldoc->save($this->storage_file)) {
            throw new SmartException(sprintf("The storage file '%s' cannot be written!", $this->storage_file), SmartException::ERR_CODE_SYSTEM_ERROR);
        }
    }
    
    protected function validateParameters()
    {
        if (empty($this->storage_file)) {
            throw new SmartException("The 'storage_file' is not specified!", SmartException::ERR_CODE_SYSTEM_ERROR);
        }
        
        if (!is_writable(dirname($this->storage_file)) || (file_exists($this->storage_file) && !is_writable($this->storage_file))) {
            throw new SmartException(sprintf("The storage file '%s' is not writable!", $this->storage_file), SmartException::ERR_CODE_SYSTEM_ERROR);
        }
    }
    
    public function init($params)
    {
        if (!empty($params["storage_file"])) {
            $this->storage_file = $params["storage_file"];
        }
        
        $this->validateParameters();
    }
    
    public function saveTokenRecord(&$token_record)
    {
        if (empty($token_record["user_id"])) {
            throw new MissingParametersException("The parameter 'user_id' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record["client_id"])) {
            throw new MissingParametersException("The parameter 'client_id' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record["access_token"])) {
            throw new MissingParametersException("The parameter 'access_token' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record["refresh_token"])) {
            throw new MissingParametersException("The parameter 'refresh_token' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record["access_token_expire"])) {
            throw new MissingParametersException("The parameter 'access_token_expire' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (!is_numeric($token_record["access_token_expire"]) || $token_record["access_token_expire"] < 0) {
            throw new InvalidTokenException("The parameter 'access_token_expire' is not valid!", SmartException::ERR_CODE_INVALID_REQUEST_DATA);
        }
        
        if (empty($token_record["refresh_token_expire"])) {
            throw new MissingParametersException("The parameter 'refresh_token_expire' is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (!is_numeric($token_record["refresh_token_expire"]) || $token_record["refresh_token_expire"] < 0) {
            throw new InvalidTokenException("The parameter 'refresh_token_expire' is not valid!", SmartException::ERR_CODE_INVALID_REQUEST_DATA);
        }
        
        if (!is_numeric($token_record["last_activity"]) || $token_record["last_activity"] <= 0) {
            throw new InvalidTokenException("The parameter 'last_activity' is not valid!", SmartException::ERR_CODE_INVALID_REQUEST_DATA);
        }
        
        $this->loadRecords();
        
        $new_record = [
            "user_id" => $token_record["user_id"],
            "client_id" => $token_record["client_id"],
            "access_token" => $token_record["access_token"],
            "refresh_token" => $token_record["refresh_token"],
            "access_token_expire" => $token_record["access_token_expire"],
            "refresh_token_expire" => $token_record["refresh_token_expire"],
            "last_activity" => $token_record["last_activity"]
        ];
        
        $exists = false;
        foreach ($this->records as &$record) {
            if ($record["user_id"] == $new_record["user_id"] && $record["client_id"] == $new_record["client_id"]) {
                $record["access_token"] = $new_record["access_token"];
                $record["refresh_token"] = $new_record["refresh_token"];
                $record["access_token_expire"] = $new_record["access_token_expire"];
                $record["refresh_token_expire"] = $new_record["refresh_token_expire"];
                $record["last_activity"] = $new_record["last_activity"];
                
                $exists = true;
            }
        }
        
        if (!$exists) {
            $this->records[] = $new_record;
        }
        
        $this->saveRecords();
    }
    
    public function loadTokenRecord(&$token_record)
    {
        if (empty($token_record["user_id"])) {
            throw new MissingParametersException("The user id is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record["client_id"])) {
            throw new MissingParametersException("The client id is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        $token_type = "";
        if (!empty($token_record["access_token"])) {
            $token_type = "access_token";
        } elseif (!empty($token_record["refresh_token"])) {
            $token_type = "refresh_token";
        }
        
        if (empty($token_type)) {
            throw new MissingParametersException("The token type is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($token_record[$token_type])) {
            throw new MissingParametersException("The token is not specified!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        $this->loadRecords();
        
        foreach ($this->records as $record) {
            if ($record[$token_type] == $token_record[$token_type] &&
                $record["user_id"] == $token_record["user_id"] &&
                $record["client_id"] == $token_record["client_id"]) {
                
                $token_record["user_id"] = $record["user_id"];
                $token_record["client_id"] = $record["client_id"];
                $token_record["access_token"] = $record["access_token"];
                $token_record["refresh_token"] = $record["refresh_token"];
                $token_record["access_token_expire"] = $record["access_token_expire"];
                $token_record["refresh_token_expire"] = $record["refresh_token_expire"];
                $token_record["last_activity"] = $record["last_activity"];
                
                return;
            }
        }
        
        throw new InvalidTokenException("The token is invalid, it is not found in the storage!", SmartException::ERR_CODE_NOT_FOUND);
    } // loadTokenRecord
    
    public function deleteTokenRecordByKey($key, $value)
    {
        if (!in_array($key, $this->supported_keys)) {
            throw new SmartException(sprintf("The key %s is not supported! The suppoted keys are: %s", $key, implode(", ", $this->supported_keys)), SmartException::ERR_CODE_SYSTEM_ERROR);
        }
        
        $this->loadRecords();
        
        $exists = false;
        
        foreach ($this->records as $i => $record) {
            if ($record[$key] == $value) {
                unset($this->records[$i]);
                
                $exists = true;
            }
        }
        
        $this->records = array_values($this->records);
        
        if (!$exists) {
            throw new SmartException("No records found with $key=$value!", SmartException::ERR_CODE_SYSTEM_ERROR);
        }
        
        $this->saveRecords();
    }

    public function verifyAccessToken($access_token, $user_id, $client_id)
    {
        $token_record["access_token"] = $access_token;
        $token_record["user_id"] = $user_id;
        $token_record["client_id"] = $client_id;

        $this->loadTokenRecord($token_record);

        if (time() > $token_record["access_token_expire"]) {
            throw new TokenExpiredException("The access token is expired!", "token_expired", "", [], "Expired on " . date("Y-m-d H:i:s", $token_record["access_token_expire"]));
        }

        $token_record["last_activity"] = time();
        $token_record["access_token_expire"] = time() + 60 * config_settings()->getParameter("access_token_ttl_minutes", 10);
        $this->saveTokenRecord($token_record);
    }

    public function verifyRefreshToken($refresh_token, $user_id, $client_id)
    {
        $token_record["refresh_token"] = $refresh_token;
        $token_record["user_id"] = $user_id;
        $token_record["client_id"] = $client_id;

        $this->loadTokenRecord($token_record);

        if (time() > $token_record["refresh_token_expire"]) {
            throw new TokenExpiredException("The refresh token is expired!", "token_expired", "", [], "Expired on " . date("Y-m-d H:i:s", $token_record["refresh_token_expire"]));
        }

        $token_record["last_activity"] = time();
        $token_record["refresh_token_expire"] = time() + 24 * 3600 * config_settings()->getParameter("refresh_token_ttl_days", 10);
        $this->saveTokenRecord($token_record);
    }
}