<?php

/**
 * CuxUser class file
 */

namespace CuxFramework\components\user;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBCriteria;
use CuxFramework\components\db\CuxDBObject;

/**
 * Identity provider class
 */
class CuxUser extends CuxBaseObject {

    /**
     * Flash messages are messages that will appear only once to the user
     * Store flash messages in the cache, using this key
     * @var string
     */
    public $flashMessageKey = "userFlash";
    
    /**
     * The list of validation errors
     * @var string
     */
    protected $_errors = array();
    
    /**
     * Checks if the current object instance has validation errors
     * @var bool
     */
    protected $_hasErrors = false;
    
    /**
     * The user identity, as set from the database
     * @var mixed
     */
    protected $_identity = false;
    
    /**
     * The current user's role
     * @var mixed
     */
    protected $_role;
    
    /**
     * The list of user granted permissions
     * @var array
     */
    protected $_permissions = array();

    /**
     * Setup class instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Set the error for a given property
     * @param string $field The property with errors
     * @param string $message The error message
     * @return \CuxFramework\components\user\CuxUser
     */
    public function addError(string $field, string $message): CuxUser {
        $this->_errors[$field] = $message;
        $this->_hasErrors = true;
        return $this;
    }

    /**
     * $_errors getter
     * @return array
     */
    public function getErrors(): array {
        return $this->_errors;
    }

    /**
     * Checks if a given field is valid
     * @param string $field
     * @return bool
     */
    public function hasError(string $field): bool {
        return isset($this->_errors[$field]);
    }

    /**
     * Checks if the current object instance has validation errors
     * @return bool
     */
    public function hasErrors(): bool {
        return $this->_hasErrors;
    }

    /**
     * Gets the error message for a given property
     * @param string $field
     * @return string
     */
    public function getError(string $field): string {
        return $this->hasError($field) ? $this->_errors[$field] : "";
    }

    /**
     * Checks if the current user is logged in or not
     * @return bool
     */
    public function isGuest(): bool {
        return $this->_identity == false;
    }

    /**
     * Resets the current session
     * @return bool
     */
    public function logOut(): bool {
        $this->_errors = array();
        $this->_hasErrors = false;
        $this->_identity = null;
        return Cux::getInstance()->session->end();
    }

    /**
     * Setup the current logged in user details
     * @param CuxDBObject $user
     */
    public function setIdentity(CuxDBObject $user) {
        Cux::info("Setting identity for user " . $user->user_id);
        $this->_identity = $user;
        Cux::getInstance()->session->set("user", $this->_identity);
    }

    /**
     * Load the list of permissions granted by a given role
     * @param mixed $roleId
     * @return void
     */
    private function loadPermissionsByRole($roleId) {
        return;
    }

    /**
     * $_identity getter
     * @return mixed
     */
    public function getIdentity() {
        return $this->_identity;
    }

    /**
     * Getter for the current logged in user id
     * @return mixed
     */
    public function getId() {
        return $this->_identity ? $this->_identity->getId() : 0;
    }

    /**
     * Checks if the current user has a given permission
     * @param mixed $permission
     * @return bool
     */
    public function can($permission): bool {
        if (empty($this->_permissions)) {
            $this->loadPermissionsByRole($this->_role);
        }
        return isset($this->_permissions[$permission]);
    }

    /**
     * $_permissions getter
     * @return array
     */
    public function getPermissions(): array {
        return $this->_permissions;
    }

    /**
     * Sets a new flash message ( message will be shown only a single time )
     * @param string $key
     * @param string $message
     * @return boolean
     */
    public function setFlashMessage(string $key, string $message) {
        if (Cux::getInstance()->hasComponent("session")) {
            $messages = $this->getFlashMessages();
            if ($messages == false) {
                $messages = array(
                    $key => $message
                );
            } else {
                $messages[$key] = $message;
            }
            Cux::getInstance()->session->set($this->flashMessageKey, $messages);
            return true;
        }
        return false;
    }

    /**
     * Check if there are any flash messages for a given key/category
     * @param type $key
     * @return boolean
     */
    public function hasFlashMessage(string $key): bool {
        if (Cux::getInstance()->hasComponent("session")) {
            if (($messages = Cux::getInstance()->session->get($this->flashMessageKey)) != false) {
                return isset($messages[$key]);
            }
            return false;
        }
        return false;
    }

    /**
     * Get the flash message for a given key/category
     * @param type $key
     * @return mixed
     */
    public function getFlashMessage(string $key) {
        if (Cux::getInstance()->hasComponent("session")) {
            if (($messages = Cux::getInstance()->session->get($this->flashMessageKey)) != false && isset($messages[$key])) {
                $ret = $messages[$key];
                $messages[$key] = false;
                unset($messages[$key]);
                Cux::getInstance()->session->set($this->flashMessageKey, $messages);
                return $ret;
            }
            return false;
        }
        return false;
    }

    /**
     * Check if there are any flash messages
     * @return boolean
     */
    public function hasFlashMessages() {
        if (Cux::getInstance()->hasComponent("session")) {
            return Cux::getInstance()->session->get($this->flashMessageKey) != false;
        }
        return false;
    }

    /**
     * Get all flash messages
     * @return mixed
     */
    public function getFlashMessages() {
        if (Cux::getInstance()->hasComponent("session")) {
            $messages = Cux::getInstance()->session->get($this->flashMessageKey);
            Cux::getInstance()->session->set($this->flashMessageKey, false);
            return $messages;
        }
        return false;
    }

}
