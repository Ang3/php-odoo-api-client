<?php

namespace Ang3\Component\OdooApiClient\Factory;

use Ang3\Component\OdooApiClient\Exception\ClientConfigException;
use Ang3\Component\OdooApiClient\ExternalApiClient;

/**
 * @author Joanis ROUANET
 *
 * This class helps to create clients for Odoo API.
 */
class ApiClientFactory
{
    /**
     * @deprecated This method will be removed in 3.0. Please use method "create" instead.
     *
     * @param array $parameters
     * @param array $options
     *
     * @return ExternalApiClient
     */
    public function createExternalApiClient(array $parameters = [], array $options = [])
    {
        trigger_error(sprintf('The method %s:%s() is deprecated and will be removed in 3.0. Please use %s:create() instead.', __CLASS__, __METHOD__, __CLASS__), E_USER_DEPRECATED);

        return $this->create($parameters, $options);
    }

    /**
     * Create external API client for Odoo from config array.
     *
     * @param array $parameters
     * @param array $options
     *
     * @throws ClientConfigException when a required parameter is missing
     *
     * @return ExternalApiClient
     */
    public function create(array $parameters = [], array $options = [])
    {
        // Si pas d'URL
        if (!array_key_exists('url', $parameters)) {
            throw new ClientConfigException('Missing required parameter "url".');
        }

        // Si pas de nom de base de données Odoo
        if (!array_key_exists('database', $parameters)) {
            throw new ClientConfigException('Missing required parameter "database".');
        }

        // Si pas d'utilisateur
        if (!array_key_exists('user', $parameters)) {
            throw new ClientConfigException('Missing required parameter "user".');
        }

        // Si pas de mot de passe
        if (!array_key_exists('password', $parameters)) {
            throw new ClientConfigException('Missing required parameter "password".');
        }

        // Récupération des options éventuelles du client
        $options = array_key_exists('options', $parameters) ? $parameters['options'] : [];

        // Retour de la nouvelle instance
        return new ExternalApiClient($parameters['url'], $parameters['database'], $parameters['user'], $parameters['password'], $options);
    }
}
