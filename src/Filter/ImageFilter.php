<?php

namespace UploadHelper\Filter;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension;
use Zend\Validator\File\IsImage;
use Zend\Validator\File\Exists;

class ImageFilter implements InputFilterAwareInterface
{

    public function setInputFilter(InputFilterInterface $filter)
    {
        throw new \Exception("Setting input filter not allowed");
    }

    /**
     *  Always return new instance
     */
    public function getInputFilter($key = null)
    {
        $filter    = new InputFilter;
        $factory   = new Factory;
        $exist     = new Exists;
        $imgValid  = new IsImage;
        $extension = new Extension(['extension' => ['png,jpg,jpeg']]);
        $filesize  = new Size(['max' => '2MB']);

        $imgValid->setOptions([
            'disableMagicFile' => true,
            'magicFile'        => false,
        ]);

        $inputFilter = $factory->createInput([
            'name'       => $key,
            'required'   => true,
            'validators' => [$exist, $imgValid, $extension, $filesize],
        ]);

        $filter->add($inputFilter);

        return $filter;
    }
}