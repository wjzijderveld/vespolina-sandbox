<?php
/**
 * This file and its content is copyright of Beeldspraak Website Creators BV - (c) Beeldspraak 2012. All rights reserved.
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 * You may not, except with our express written permission, distribute or commercially exploit the content.
 *
 * @author      Beeldspraak <info@beeldspraak.com>
 * @copyright   Copyright 2012, Beeldspraak Website Creators BV
 * @link        http://beeldspraak.com
 *
 */
namespace Application\DevBundle\Document;

use Vespolina\ProductBundle\Document\Asset as BaseAsset;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Asset")
 */
class Asset extends BaseAsset
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\File $file
     * @Assert\File()
     */
    protected $file;

    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ODM\PrePersist()
     * @ODM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name
            $this->src = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ODM\PostPersist()
     * @ODM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->file->move($this->getUploadRootDir(), $this->src);

        unset($this->file);
    }

    /**
     * @ODM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    public function getWebPath()
    {
        return null === $this->src ? null : DIRECTORY_SEPARATOR . $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->src;
    }

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return 'uploads/product/assets';
    }

    /**
     * @return string
     */
    protected function getUploadRootDir()
    {
        // todo fix this... http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html says it like this, but this just sucks
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return null|string
     */
    protected function getAbsolutePath()
    {
        return null === $this->src ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->src;
    }

}
