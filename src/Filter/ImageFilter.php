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
    private $filter;

    public function setInputFilter(InputFilterInterface $filter)
    {
        throw new \Exception("Setting input filter not allowed");
    }

    public function getInputFilter()
    {
        if(empty($this->filter)){
            $this->filter = new InputFilter;
            $factory      = new Factory;
            $exist        = new Exists;
            $filesize     = new Size(['max' => '2MB']);
            $extension    = new Extension(['extension' => ['png,jpg,jpeg']]);
            $imgValid     = new IsImage;

            $exist->setMessage('Slika je obavezna.');
            $filesize->setMessage('Maksimalna veličina slike je 2MB.');
            $imgValid->setMessage('Fajl mora biti validna slika.');
            $extension->setMessage('Fajl nema validnu ekstenziju.');

            $inputFilter = $factory->createInput([
                'name'       => 'image',
                'required'   => true,
                'validators' => [$exist, $imgValid, $extension, $filesize],
            ]);

            $this->filter->add($inputFilter);
        }

        return $this->filter;
    }
}