Quinto projeto das aulas de Zend Framework 2 com Nataniel Paiva
=======================

Introdução
------------

Esse quinto projeto contempla os seguintes tópicos:

* Criar uma autenticação utilizando a tb_usuario juntamente com a tb_perfil.
* Criar um ACL para utilizarmos em conjunto com a autenticação.



Mudando a autenticação
----------------------

Primeiro vamos criar nossa tabela de perfil certo?
segue abaixo o script do banco que estamos utilizando até o momento:
~~~sql
	CREATE SCHEMA IF NOT EXISTS `db_projeto5` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

	CREATE TABLE IF NOT EXISTS `db_projeto5`.`tb_celular` (
	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `marca` VARCHAR(100) NOT NULL,
	  `modelo` VARCHAR(100) NOT NULL,
	  `ativo` TINYINT(4) NULL DEFAULT NULL,
	  PRIMARY KEY (`id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;

	CREATE TABLE IF NOT EXISTS `db_projeto5`.`tb_usuario` (
	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `id_perfil` INT(11) NOT NULL,
	  `nome` VARCHAR(100) NOT NULL,
	  `email` VARCHAR(100) NOT NULL,
	  `login` VARCHAR(20) NOT NULL,
	  `senha` VARCHAR(32) NOT NULL,
	  `ativo` TINYINT(4) NOT NULL DEFAULT 1,
	  PRIMARY KEY (`id`),
	  INDEX `fk_tb_usuario_tb_perfil_idx` (`id_perfil` ASC),
	  CONSTRAINT `fk_tb_usuario_tb_perfil`
	    FOREIGN KEY (`id_perfil`)
	    REFERENCES `db_projeto5`.`tb_perfil` (`id`)
	    ON DELETE NO ACTION
	    ON UPDATE NO ACTION)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;

	CREATE TABLE IF NOT EXISTS `db_projeto5`.`tb_perfil` (
	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `nome` VARCHAR(45) NOT NULL,
	  `ativo` TINYINT(4) NOT NULL DEFAULT 1,
	  PRIMARY KEY (`id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;


	INSERT INTO `db_projeto5`.`tb_celular` (`marca`, `modelo`, `ativo`) VALUES ('Samsung', 'Galaxy 5', '1');
	INSERT INTO `db_projeto5`.`tb_celular` (`id`, `marca`, `modelo`, `ativo`) VALUES ('', 'Motorola', 'Moto G', '1');
	INSERT INTO `db_projeto5`.`tb_celular` (`id`, `marca`, `modelo`, `ativo`) VALUES ('', 'Nokia', 'Lumia', '1');

	INSERT INTO `db_projeto5`.`tb_perfil` (`nome`, `ativo`) VALUES ('Administrador', '1');
	INSERT INTO `db_projeto5`.`tb_perfil` (`nome`, `ativo`) VALUES ('Vendedor', '1');


	INSERT INTO `db_projeto5`.`tb_usuario` (`nome`, `email`, `login`, `senha`, id_perfil) VALUES ('Nataniel Paiva', 'nataniel.paiva@gmail.com', 'nataniel.paiva', md5('123'), 1);
~~~

Agora que já temos o nosso banco com a tabela perfil, precisamos mudar um pouco a nossa autenticação.
Vou deixar por sua conta criar as models de perfil, agora vamos direto para AuthController.php mudando a action autenticarAction com o seguinte código:
~~~php
	public function autenticarAction()
	    {
		$redirect = 'autenticar';
		$request = $this->getRequest();

		if ($request->isPost()){
		        //Verifica autenticacao
		        $this->getAuthService()->getAdapter()
		                               ->setIdentity($request->getPost('login'))
		                               ->setCredential($request->getPost('senha'));

		        $result = $this->getAuthService()->authenticate();
		        foreach($result->getMessages() as $message)
		        {
		            $this->flashmessenger()->addMessage($message);
		        }
		        if ($result->isValid()) {
		            $redirect = 'home';
		            if ($request->getPost('rememberme') == 1 ) {
		                $this->getSessionStorage()
		                     ->setRememberMe(1);
		                $this->getAuthService()->setStorage($this->getSessionStorage());
		            }
		            //Coloque essas três linhas
		            $usuarioLogado  = $this->getUsuarioTable()->getUsuarioIdentidade($request->getPost('login'));
		            $this->getAuthService()->setStorage($this->getSessionStorage());
		            $this->getAuthService()->getStorage()->write($usuarioLogado);
		        }

		}

		return $this->redirect()->toRoute($redirect);
	    }
~~~
Agora vamos na UsuarioTable criar nosso método getUsuarioIdentidade com o seguinte código:
~~~php
	public function getUsuarioIdentidade($login)
	    {
		$select = new Select();
		$select->from('tb_usuario')
		->columns(array( 'id', 'nome', 'id_perfil' ))
		->where( array('ativo' => UsuarioTable::ATIVO) )
		->where( array( 'login' => $login ) );
		
		$rowset = $this->tableGateway->selectWith($select);
		$row    = $rowset->current();
		return $row;
	    }
~~~
Com o método acima conseguiremos recuperar todos os dados que precisaremos para guardar na configuração do nosso ACL.
Agora vamos para o Module.php do módulo Application e utilize o seguinte código:
~~~php
<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;

	class Module
	{
	    //crie esse atributo global
	    protected $acl = null;
	    //Altere esse método
	    public function onBootstrap(MvcEvent $e)
	    {
		$this->configurarAcl($e);
		$e->getApplication()
		    ->getEventManager()
		    ->attach('route', array(
		    $this,
		    'checkAcl'
		));
	    }

	    //Crie esse método
	    public function loadConfiguration(MvcEvent $e)
	    {
	    
	    	$application = $e->getApplication();
	    	$sm = $application->getServiceManager();
	    
	    
	    	if ($sm->get('AuthService')->hasIdentity()) {
	    		$usuario = $sm->get('Autenticacao\Model\AutenticacaoStorage')->read();
	    		if ( !empty( $usuario->perfil->id ) ) {
	    			return $usuario->perfil->id;
	    		}
	    	}
	    
	    
	    }
	    //Crie esse método
	    public function configurarAcl(MvcEvent $e)
	    {
		
	    	$this->acl = new Acl();
	    	$aclRoles = include __DIR__ . '/config/module.acl.perfis.php';
	    	$allResources = array();
	    
	    	foreach ($aclRoles as $valores) {
	    		$role = new GenericRole($valores['role']);
	    		if(!$this->acl ->hasRole(($role)))
	    			$this->acl -> addRole($role);
	    
	    		if(!$this->acl ->hasResource('deny'))
	    			$this->acl -> addResource(new GenericResource('deny'));

	    			if(!$this->acl ->hasResource($valores['resource']))
	    				$this->acl -> addResource(new GenericResource($valores['resource']));
	    
	    			$this->acl->allow($role, $valores['resource'], $valores['privileges']);
	    		}
	    
	    		$e->getViewModel()->acl = $this->acl;
	    }
	    //Crie esse método
	    public function checkAcl(MvcEvent $e)
	    {
	    	$route = $e->getRouteMatch()->getMatchedRouteName();
	    	
	    	if(!$this->acl ->hasResource($route))
	    		$this->acl -> addResource(new GenericResource($route));
	    
	    	$perfilId = $this->loadConfiguration($e);
	    	if (empty($perfilId)) {
	    		if ( $route != 'autenticar' ) {
	    			$response = $e -> getResponse();
	    			$response -> getHeaders() -> addHeaderLine('Location', $e -> getRequest() -> getBaseUrl() . '/autenticar/');
	    			$response -> setStatusCode(404);
	    			$response->sendHeaders ();exit;
	    		}
	    	}else{
	    		$privilegio = $e->getRouteMatch()->getParam('action');
	    		$route      = $this->retiraBarraRota($route);
	    		if (! $e->getViewModel()->acl->isAllowed($perfilId, $route, $privilegio)){
					if ( $route != 'deny' ) {
						$response = $e -> getResponse();
						$response -> getHeaders() -> addHeaderLine('Location', $e -> getRequest() -> getBaseUrl() . '/deny/');
						$response -> setStatusCode(404);
						$response->sendHeaders ();exit;
					}
	    		}
	    	}
	    }
	    //Crie esse método
	    public function retiraBarraRota($route)
	    {
		$route      = explode("/", $route);
		return $route[0];
	    }
	    
	    public function getConfig()
	    {
	    	return include __DIR__ . '/config/module.config.php';
	    }
	    
	    public function getAutoloaderConfig()
	    {
	    	return array(
	    			'Zend\Loader\StandardAutoloader' => array(
	    					'namespaces' => array(
	    							__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
	    					),
	    			),
	    	);
	    }
	}
~~~
Agora precisamos criar o arquivo que o nosso método configurarAcl chama, que está em /config/module.acl.perfis.php
Vamos criá-lo com o seguinte código:
~~~php
	<?php
	return array(
	    // Perfil de administrador
	    array(
		'role' => 1,
		'resource' => 'autenticar',
		'privileges' => array()
	    ),
	    array(
		'role' => 1,
		'resource' => 'usuario',
		'privileges' => array(
		    'index',
		    'add',
		    'edit'
		)
	    ),
	    array(
		'role' => 1,
		'resource' => 'celular',
		'privileges' => array()
	    ),
	    array(
		'role' => 1,
		'resource' => 'sair',
		'privileges' => array()
	    ),
	    
	    // Perfil de oreia
	    array(
		'role' => 2,
		'resource' => 'autenticar',
		'privileges' => array()
	    ),
	    array(
		'role' => 2,
		'resource' => 'sair',
		'privileges' => array(
		    'index'
		)
	    ),
	    array(
		'role' => 2,
		'resource' => 'celular',
		'privileges' => array(
		    'index'
		)
	    )
	);
~~~
Pronto!! Nosso ACL está pronto!!
De uma forma muito simples!! Temos várias outras formas de criar um acl no Zend 2, mas nesse fiz com que as rotas fossem os resources, mas isso não é obrigatório.
Você quem decide o que vai ser o resource ou não.















