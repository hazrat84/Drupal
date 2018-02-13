<?php

namespace Drupal\dino_roar\Jurassic;
use Symfony\Component\HttpFoundation\Response;

class RoarGenerator {

	public function getRoar($length) {
		$roar = 'R'.str_repeat('O', $length).'AR!';
		return new Response($roar);
	}

}