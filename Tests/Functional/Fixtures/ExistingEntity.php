<?php
namespace Famelo\Bean\Tests\Functional\Fixtures;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ExistingEntity {

    /**
     * @var boolean
     *
     */
    protected $existingProperty;

    /**
     * Gets existingProperty.
     *
     * @return boolean $existingProperty
     */
    public function getExistingProperty() {
        return $this->existingProperty;
    }

    /**
     * Sets the existingProperty.
     *
     * @param boolean $existingProperty
     */
    public function setExistingProperty($existingProperty) {
        $this->existingProperty = $existingProperty;
    }

}