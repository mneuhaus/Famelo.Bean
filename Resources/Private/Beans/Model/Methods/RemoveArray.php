<?php
class RemoveArray {
    /**
     * Remove from __name__.
     *
     * @param __type__ $__singular__
     */
    public function remove__Singular__($__singular__) {
    	unset($this->__name__[array_search($__singular__, $this->__name__)]);
    }
}