<?php

namespace Ang3\Component\OdooApiClient\Factory;

use InvalidArgumentException;
use Ang3\Component\OdooApiClient\Client\ExternalApiClient;

/**
 * @author Joanis ROUANET
 *
 * This class helps to create clients for Odoo API.
 */
class OdooClientFactory
{
	/**
	 * Create external API client for Odoo from config array.
	 * 
	 * @param  array  $parameters
	 * @param  array  $options
	 *
	 * @throws InvalidArgumentException When a required parameter is missing.
	 * 
	 * @return ExternalApiClient
	 */
	public function createExternalApiClient(array $parameters = [], array $options = [])
	{
		// Si pas d'URL
		if(!array_key_exists('url', $parameters)) {
			throw new InvalidArgumentException('Missing required parameter "url".');
		}

		// Si pas de nom de base de données Odoo
		if(!array_key_exists('database', $parameters)) {
			throw new InvalidArgumentException('Missing required parameter "database".');
		}

		// Si pas d'utilisateur
		if(!array_key_exists('user', $parameters)) {
			throw new InvalidArgumentException('Missing required parameter "user".');
		}

		// Si pas de mot de passe
		if(!array_key_exists('password', $parameters)) {
			throw new InvalidArgumentException('Missing required parameter "password".');
		}

		// Récupération des options éventuelles du client
		$options = array_key_exists('options', $parameters) ? $parameters['options'] : [];

		// Retour de la nouvelle instance
		return new ExternalApiClient($parameters['url'], $parameters['database'], $parameters['user'], $parameters['password'], $options);
	}
}