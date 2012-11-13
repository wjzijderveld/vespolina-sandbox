<?php
/**
 * (c) 2012 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Application\DevBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Vespolina\Entity\Product\ProductInterface;
use Vespolina\ProductBundle\Document\BaseProduct;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="vespolinaProduct")
 */
class Product extends BaseProduct implements ProductInterface
{
    /**
     * @ODM\Id()
     */
    protected $id;

    /**
     * @ODM\ReferenceMany("Application\DevBundle\Document\Asset", cascade={"persist"})
     */
    protected $images;

    /**
     * @var array
     * @ODM\Hash
     */
    protected $pricing;

    /**
     * @param array $pricing
     */
    public function setPricing($pricing)
    {
        $this->pricing = $pricing;
    }

    /**
     * @return array
     */
    public function getPricing()
    {
        return $this->pricing;
    }

    public function setImages($images)
    {
        $this->images = $images;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getFirstImage()
    {
        return count($this->images) ? $this->images[0] : null;
    }

}
