<?php
namespace Usuario\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

class PerfilTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll()
	{
		$select = new Select();
		$select->from('tb_perfil')
		->columns(array('id','nome', 'ativo'));

		$resultSet = $this->tableGateway->selectWith($select);
		return $resultSet;
	}

	public function getPerfil($id)
	{
		$id = (int) $id;
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		return $row;
	}

	public function salvarPerfil(Perfil $perfil)
	{
		$data = array(
			'nome' => $perfil->nome,
		);
		$id = $perfil->id;

		if (!$this->getPerfil($id))
		{
			$data['id'] = $id;
			$this->tableGateway->insert($data);
		}
		else
		{
			$this->tableGateway->update($data, array('id' => $id));
		}
	}

	public function deletePerfil($id)
	{
		$this->tableGateway->delete(array('id' => $id));
	}

}