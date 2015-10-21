<?php

namespace AppBundle\Service\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;
use JMS\Serializer\SerializerInterface;
use AppBundle\Service\Client\TokenStorage\TokenStorageInterface;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Exception as AppException;
use AppBundle\Entity\User;
use GuzzleHttp\Message\ResponseInterface;
use AppBundle\Model\SelfRegisterData;

/**
 * Connects to RESTful Server (API)
 * Perform login and logout exchanging and persist token into the given storage
 * 
 */
class RestClient
{
    const HTTP_CODE_AUTHTOKEN_EXPIRED = 419;
    
    /**
     * @var ClientInterface 
     */
    private $client;

    /**
     * @var SerializerInterface 
     */
    private $serialiser;

    /**
     * @var TokenStorageInterface 
     */
    private $tokenStorage;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var array 
     */
    private $history;
    
    /**
     * @var boolean 
     */
    private $saveHistory;

    /**
     * Header name holding auth token, returned at login time and re-sent at each requests
     */
    const HEADER_AUTH_TOKEN = 'AuthToken';

    /**
     * Header name holding client secret, to send at login time
     */
    const HEADER_CLIENT_SECRET = 'ClientSecret';

     /**
     * Error Messages
     */
    const ERROR_CONNECT = 'API not available.';
    const ERROR_NO_SUCCESS = 'Endpoint failed with message %s';
    const ERROR_FORMAT = 'Cannot decode endpoint response';


    public function __construct(
        ClientInterface $client, 
        TokenStorageInterface $tokenStorage, 
        SerializerInterface $serialiser, 
        Logger $logger, 
        $clientSecret,
        $saveHistory
    ) {
        $this->client = $client;
        $this->serialiser = $serialiser;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->clientSecret = $clientSecret;
        $this->saveHistory = $saveHistory;
        $this->history = [];
    }


    /**
     * Call /auth/login endpoints passing email and password
     * Stores AuthToken in storage
     * Returns user
     * 
     * @param array $credentials with keys "token" or "email" and "password"
     * 
     * @return User
     */
    public function login(array $credentials)
    {
        $response = $this->rawSafeCall('post', '/auth/login', [
            'body' => $this->toJson($credentials),
            'addClientSecret' => true,
        ]);

        $this->tokenStorage->set($response->getHeader(self::HEADER_AUTH_TOKEN));

        return $this->arrayToEntity('User', $this->extractDataArray($response));
    }


    /**
     * Call /auth/logout
     */
    public function logout()
    {
        $response = $this->rawSafeCall('post', '/auth/logout', [
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }

    
    /**
     * Finds user by email
     * 
     * @param string $token
     * @return \AppBundle\Entity\User $user
     * @throws UsernameNotFoundException
     */
    public function loadUserByToken($token)
    {
        $response = $this->rawSafeCall('get', 'user/get-by-token/' . $token, [
            'addClientSecret' => true,
        ]);
        
        $responseArray = $this->extractDataArray($response);
        
        return $this->arrayToEntity('User', $responseArray);
    }

    /**
     * @param User $user
     * @param string $type
     * 
     * @return array
     */
    public function userRecreateToken(User $user, $type)
    {
        $response = $this->rawSafeCall('put', 'user/recreate-token/' .  $user->getEmail() . '/' . $type, [
            'addClientSecret' => true,
        ]);
        
        return $this->extractDataArray($response);
    }
    
    
    /**
     * Call POST /selfregister passing client secret
     * 
     * @param SelfRegisterData $selfRegData
     * 
     * @return array
     */
    public function registerUser(SelfRegisterData $selfRegData)
    {
        $response = $this->rawSafeCall('post', 'selfregister', [
            'addClientSecret' => true,
            'body' => $this->toJson($selfRegData)
        ]);
        
        return $this->extractDataArray($response);
    }
    
    /**
     * @param string $endpoint e.g. /user
     * @param string|object|array $mixed HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array $options keys: deserialise_group
     * 
     * @return string response body
     */
    public function put($endpoint, $mixed, array $options = [])
    {
        $response = $this->rawSafeCall('put', $endpoint, [
            'body' =>  $this->toJson($mixed, $options),
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }

    
    /**
     * @param string $endpoint e.g. /user
     * @param string|object $mixed HTTP body. json_encoded string or entity (that will JMS-serialised)
     * @param array $options keys: deserialise_group
     * 
     * @return string response body
     */
    public function post($endpoint, $mixed, array $options = [])
    {
        $body = $this->toJson($mixed, $options);

        $response = $this->rawSafeCall('post', $endpoint, [
            'body' => $body,
            'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }


    /**
     * @param string $endpoint e.g. /user
     * @param string $expectedResponseType Entity class to deserialise response into 
     *                e.g. "Account" (AppBundle\Entity\ prefix not needed) 
     *                or "Account[]" to deseialise into an array of entities
     * @return mixed $expectedResponseType type
     */
    public function get($endpoint, $expectedResponseType, $options = [])
    {
        $response = $this->rawSafeCall('get', $endpoint, $options + [
            'addAuthToken' => true,
        ]);

        $responseArray = $this->extractDataArray($response);
        if ($expectedResponseType == 'array') {
            return $responseArray;
        } else if (substr($expectedResponseType, -2) == '[]') {
            return $this->arrayToEntitities('AppBundle\\Entity\\' . $expectedResponseType, $responseArray);
        } else if (class_exists('AppBundle\\Entity\\' . $expectedResponseType)) {
            return $this->arrayToEntity($expectedResponseType, $responseArray);
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ": invalid type of expected response, $expectedResponseType given.");
        }

        return $responseArray;
    }

    
    /**
     * @param string $endpoint e.g. /user
     * 
     * @return string response body
     */
    public function delete($endpoint)
    {
        $response = $this->rawSafeCall('delete', $endpoint, [
           'addAuthToken' => true,
        ]);

        return $this->extractDataArray($response);
    }


    /**
     * Performs HTTP client call
     * // TODO refactor into  rawSafeCallWithAuthToken and rawSafeCallWithClientSecret
     * 
     * In case of connect/HTTP failure:
     * - throws DisplayableException using self::ERROR_CONNECT as a message, keeping exception code
     * - logs the full error message with with warning priority
     * 
     * @return ResponseInterface
     */
    private function rawSafeCall($method, $url, $options)
    {
        // process special header options
        if (!empty($options['addAuthToken'])) {
            $options['headers'][self::HEADER_AUTH_TOKEN] = $this->tokenStorage->get();
        }
        unset($options['addAuthToken']);
        
        if (!empty($options['addClientSecret'])) {
            $options['headers'][self::HEADER_CLIENT_SECRET] = $this->clientSecret;
        }
        unset($options['addClientSecret']);
        
        
        try {
            $start = microtime(true);
            $response = $this->client->$method($url, $options);
            
            if ($this->saveHistory) {
                $this->logRequest($url, $method, $start, $options, $response);
                
            }
            
            return $response;
        } catch (RequestException $e) {
            // request exception contains a body, that gets decoded and passed to RestClientException
            $this->logger->warning('RestClient | ' . $url . ' | ' . $e->getMessage());
            
            try {
                $data = $this->serialiser->deserialize($e->getResponse()->getBody(), 'array', 'json') ?: [];
            } catch (\Exception $e) {
                $data = [];
            }
            
            throw new AppException\RestClientException(self::ERROR_CONNECT, $e->getCode(), $data);
        } catch (TransferException $e) {
            $this->logger->warning('RestClient | ' . $url . ' | ' . $e->getMessage());
            
            throw new AppException\RestClientException(self::ERROR_CONNECT, $e->getCode());
        }
    }


    /**
     * Return the 'data' array from the response
     * 
     * @param type $class
     * @param ResponseInterface $response
     * 
     * @return array content of "data" key from response
     */
    private function extractDataArray(ResponseInterface $response)
    {
        //TODO validate $response->getStatusCode()

        
        try{ 
            $data = $this->serialiser->deserialize($response->getBody(), 'array', 'json');
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . '. Api responded with invalid JSON. Body: ' . $response->getBody());
            throw new Exception\JsonDecodeException(self::ERROR_FORMAT);
        }
        
        if (empty($data['success'])) {
            throw new Exception\NoSuccess(sprintf(self::ERROR_NO_SUCCESS, $data['message']));
        }
        
        return $data['data'];
    }

    /**
     * @param string $class full class name of the class to deserialise to
     * @param array $data "data" returned from the RESTful server
     * 
     * @return Object of type $class
     */
    private function arrayToEntity($class, array $data)
    {
        $fullClassName = (strpos($class, 'AppBundle') !== false) 
                 ? $class : 'AppBundle\\Entity\\' . $class;
        
        return $this->serialiser->deserialize(json_encode($data), $fullClassName, 'json');
    }


    /**
     * @param string $class full class name of the class to deserialise to
     * @param array $data "data" returned from the RESTful server
     * 
     * @return array of type $class
     */
    private function arrayToEntitities($class, array $data)
    {
        $expectedResponseType = substr($class, 0, -2);
        $ret = [];
        foreach ($data as $row) {
            $entity = $this->arrayToEntity($expectedResponseType, $row);
            $ret[$entity->getId()] = $entity;
        }

        return $ret;
    }


    /**
     * //TODO use for other calls ?
     * @param string $mixed json_encoded string or Doctrine Entity (it will be serialised before posting)
     * @param array $options
     * @return type
     */
    private function toJson($mixed, array $options = [])
    {
        if (is_object($mixed)) {

            $context = \JMS\Serializer\SerializationContext::create()
                ->setSerializeNull(true);

            if (!empty($options['deserialise_group'])) {
                $context->setGroups([$options['deserialise_group']]);
            }
            
            return $this->serialiser->serialize($mixed, 'json', $context);
        } else if (is_array($mixed)) {
            return $this->serialiser->serialize($mixed, 'json');
        }

        return $mixed;
    }
    
    /**
     * @param string $url
     * @param string $method
     * @param string $start
     * @param type $response
     */
    private function logRequest($url, $method, $start, $options, ResponseInterface $response)
    {
        $this->history[] = [
            'url' => $url,
            'method' => $method,
            'time' => microtime(true) - $start,
            'options'=> print_r($options, true),
            'responseCode' => $response->getStatusCode(),
            'responseBody' => print_r(json_decode((string)$response->getBody(), true), true)
        ];
    }
    
    /**
     * @return array of calls, for debug reasons (e.g. symfony debug toolbar)
     */
    public function getHistory()
    {
        return $this->history;
    }
    
}