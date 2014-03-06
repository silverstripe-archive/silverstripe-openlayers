<?php

class CustomCompositeField extends CompositeField {

    protected $classnames = array();

    public function addClassName($classname) {
        $this->classnames[] = $classname;
    }

    public function extraClass() {
        $classes = parent::extraClass();
        $classes = $classes . ' ' . implode(' ', $this->classnames);
        return $classes;
    }
}