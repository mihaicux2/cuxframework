<?php

namespace CuxFramework\components\user;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBCriteria;
use CuxFramework\components\db\CuxDBObject;

class CuxUser extends CuxBaseObject {

    public $flashMessageKey = "userFlash";
    
    protected $_errors = array();
    protected $_hasErrors = false;
    protected $_identity = false;
    protected $_role;
    protected $_permissions = array();

    public function config(array $config): void {
        parent::config($config);
    }

    public function addError(string $field, string $message): CuxUser {
        $this->_errors[$field] = $message;
        $this->_hasErrors = true;
        return $this;
    }

    public function getErrors(): array {
        return $this->_errors;
    }

    public function hasError($field): bool {
        return isset($this->_errors[$field]);
    }

    public function hasErrors(): bool {
        return $this->_hasErrors;
    }

    public function getError($field): string {
        return $this->hasError($field) ? $this->_errors[$field] : "";
    }

    public function isGuest(): bool {
        return $this->_identity == false;
    }

    public function logOut(): bool {
        $this->_errors = array();
        $this->_hasErrors = false;
        $this->_identity = null;
        return Cux::getInstance()->session->end();
    }

    public function setIdentity(CuxDBObject $user) {
        Cux::getInstance()->logger->info("Setting identity for user " . $user->user_id);
        $this->_identity = $user;
        Cux::getInstance()->session->set("user", $this->_identity);
    }

    private function loadPermissionsByRole($roleId) {
        return;
    }

    public function getIdentity() {
        return $this->_identity;
    }

    public function getId() {
        return $this->_identity ? $this->_identity->user_id : 0;
    }

    public function can($permission) {
        if (empty($this->_permissions)) {
            $this->loadPermissionsByRole($this->_role);
        }
        return isset($this->_permissions[$permission]);
    }

    public function getPermissions() {
        return $this->_permissions;
    }

    public function setFlashMessage($key, $message) {
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

    public function hasFlashMessage($key) {
        if (Cux::getInstance()->hasComponent("session")) {
            if (($messages = Cux::getInstance()->session->get($this->flashMessageKey)) != false) {
                return isset($messages[$key]);
            }
            return false;
        }
        return false;
    }

    public function getFlashMessage($key) {
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

    public function hasFlashMessages() {
        if (Cux::getInstance()->hasComponent("session")) {
            return Cux::getInstance()->session->get($this->flashMessageKey) != false;
        }
        return false;
    }

    public function getFlashMessages() {
        if (Cux::getInstance()->hasComponent("session")) {
            $messages = Cux::getInstance()->session->get($this->flashMessageKey);
            Cux::getInstance()->session->set($this->flashMessageKey, false);
            return $messages;
        }
        return false;
    }

}
