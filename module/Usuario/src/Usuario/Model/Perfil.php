<?php
namespace Usuario\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Perfil
{
	public $id;
	public $nome;
	public $ativo;
	protected $inputFilter;

	public function exchangeArray($data)
	{
		$this->id	    = (isset($data['id'])) ? $data['id'] : null;
		$this->nome 	= (isset($data['nome'])) ? $data['nome'] : null;
		$this->ativo 	= (isset($data['ativo'])) ? $data['ativo'] : null;

	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();

			$inputFilter->add($factory->createInput(array(
					'name'     => 'id',
					'required' => false,
					'filters'  => array(
							array('name' => 'Int'),
					),
					'validators' => array(
							array(
									'name'    => 'Between',
									'options' => array(
											'min'      => 0,
											'max'      => 3600
									),
							),
					),
			)));

			$inputFilter->add($factory->createInput(array(
					'name'     => 'nome',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 2,
											'max'      => 30,
									),
							),
					),
			)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}

	public function getArrayCopy()
	{
		return get_object_vars($this);
	}
}
