<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Usuario;

use Usuario\Model\Usuario;
use Usuario\Model\UsuarioTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Usuario\Model\Perfil;
use Usuario\Model\PerfilTable;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $GLOBALS['sm'] = $e->getApplication()->getServiceManager();
        
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					'Usuario\Model\UsuarioTable' =>  function($sm) {
    						$tableGateway = $sm->get('UsuarioTableGateway');
    						$table = new UsuarioTable($tableGateway);
    						return $table;
    					},
    					'UsuarioTableGateway' => function ($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new Usuario());
    						return new TableGateway('tb_usuario', $dbAdapter, null, $resultSetPrototype);
    					},
    					'Usuario\Model\PerfilTable' =>  function($sm) {
    						$tableGateway = $sm->get('PerfilTableGateway');
    						$table = new PerfilTable($tableGateway);
    						return $table;
    					},
    					'PerfilTableGateway' => function ($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new Perfil());
    						return new TableGateway('tb_perfil', $dbAdapter, null, $resultSetPrototype);
    					},
    			),
    	);
    }
}
