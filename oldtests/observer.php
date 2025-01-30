<?php

class Observer {
    public function called($method, $argument) {}
    public function reportError($errorCode, $errorMessage, Subject $subject) {}
}

