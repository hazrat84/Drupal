<?php

namespace Drupal\dino_roar\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\dino_roar\Jurassic\RoarGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

class RoarController extends ControllerBase {

	private $roarGenerator;
	protected $loggerFactory;

	public function __construct(RoarGenerator $roarGenerator, LoggerChannelFactoryInterface $loggerFactory){
		$this->roarGenerator = $roarGenerator;
		$this->loggerFactory = $loggerFactory;
	}

	public function roar($count){
		//$roarGenerator = new  RoarGenerator();
		$roar = $this->roarGenerator->getRoar($count);
		//$keyValueStore = $this->keyValue('dino');
		//$keyValueStore->set('roar_string', $roar);
		$this->loggerFactory->get('default')->debug($roar);
		return new Response($roar);
	}

	// create method is available to us when we extend from ControllerBase
	public static function create(ContainerInterface $container) {
		// ContainerInterface is step 1 when your controller needs to access services from the container
		$roarGenerator = $container->get('dino_roar.roar_generator');
		// Create a $roarGenerator variable, set it to $container->get(''); and pass it the name of the service: 
		//dino_roar.roar_generator
		$loggerFactory = $container->get('logger.factory');

		return new static($roarGenerator, $loggerFactory); // Create a new instance of RoarController and return it, please
	}
}

/*
Why did I put my Service in the Container? 

Why did we go to all this trouble? After all, this only saved us one line in the controller: the new RoarGenerator() line.

Two reasons, big reasons. First, I keep telling you the container is like an array of all the useful objects in the system. Ok, that's kind of a lie. It's more like an array of potential objects. The container doesn't instantiate a service until and unless someone asks for it. So, until we actually hit the line that asks for the dino_roar.roar_generator service, your app doesn't use the memory or CPUs needed to create that.
*/