<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;

class Module
{
    protected $acl = null;
    
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
