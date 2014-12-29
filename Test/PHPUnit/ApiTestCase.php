<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Test\PHPUnit;

// Testing.
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
// ORM Fixtures
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader,
    Doctrine\Common\DataFixtures\Purger\ORMPurger,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor;
// Test tools.
use JsonSchema\Validator,
    GoIntegro\Hateoas\Http\Client;

abstract class ApiTestCase extends WebTestCase
{
    const FAIL_RESPONSE_STATUS_PATTERN = "Failed asserting that the status code %s (\"%s)\" matches the expected %s (\"%s\").",
        WSSE_CREDENTIALS_PATTERN = "X-WSSE: UsernameToken Username=\"%s\", PasswordDigest=\"%s\", Nonce=\"%s\", Created=\"%s\"",
        HEADER_LOCALE = 'en-GB',
        CONTENT_JSON = 'application/json',
        /**
         * @see http://www.iana.org/assignments/media-types/application/vnd.api+json
         */
        CONTENT_JSON_API = 'application/vnd.api+json',
        API_SERVER_SHELL = '/usr/bin/php5 -S %s:%s %s/config/router_%s.php > /dev/null 2>&1 & echo $!;';

    /**
     * @var array Los estados de respuesta HTTP y sus códigos.
     */
    protected static $statusCodes = array(
        // Successful.
        'OK' => 200,
        'Created' => 201,
        'NoContent' => 204,
        'PartialContent' => 206,
        // Redirection.
        'Found' => 302,
        // Client error.
        'BadRequest' => 400,
        'Unauthorized' => 401,
        'Forbidden' => 403,
        'NotFound' => 404,
        'MethodNotAllowed' => 405,
        'Conflict' => 409,
        'UnsupportedMediaType' => 415,
        'UnprocessableEntity' => 422,
        // Server error.
        'InternalServerError' => 500,
        'NotImplemented' => 501
    );
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $em;
    
    /**
     * @var string
     */
    protected static $rootUrl;
    
    /** 
     * @var string
     */
    protected static $serverHost;
    
    /**
     *
     * @var string
     */
    protected static $serverPort;
    
    /**
     * @var string
     */
    protected static $environment = 'test';
    
    /**
     * @var bool
     */
    protected static $debug = true;

    /**
     * @var string
     */
    protected $serverPid;
    
    

    /**
     * Prepara el entorno antes de la primer prueba.
     */
    public static function setUpBeforeClass()
    {
        // Inicializamos el framework web.
       static::$kernel = static::createKernel(
            array(
            'environment' => static::$environment,
            'debug'       => static::$debug
        ));
        static::$kernel->boot();
        $container = static::$kernel->getContainer();

        // Obtenemos algunos servicios de uso común.
        static::$em = $container->get('doctrine')->getManager();
        $testServerData = $container->getParameter('go_integro_hateoas.test_server');
        static::$serverHost = $testServerData['host'];
        static::$serverPort = $testServerData['port'];
        static::$rootUrl = 'http://' . $testServerData['host'] . ':' . $testServerData['port'];

        if ($fixtures = static::getFixtures()) {
            $loader = new ContainerAwareLoader($container);
            array_walk($fixtures, array($loader, 'addFixture'));
            $purger = new ORMPurger(static::$em);
            $executor = new ORMExecutor(static::$em, $purger);
            $executor->execute($loader->getFixtures());
        }
    }

    /**
     * Prepara el entorno antes de cada prueba.
     */
    /**
     * Prepara el entorno antes de cada prueba.
     */
    public function setUp()
    {
        parent::setUp();
        self::$kernel->boot();
        $this->runServer();
    }

    /**
     * Desarma el entorno después de la última prueba.
     */
    public static function tearDownAfterClass()
    {
        //Purgamos la base de datos.
        $purger = new ORMPurger(static::$em);
        $purger->purge();
    }

    /**
     * Limpia el kernel
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->stopServer();
    }

    /**
     * Obtiene el entity manager de Doctrine 2.
     * @return DoctrineRegistry
     */
    protected function getEntityManager()
    {
        return static::$em;
    }

    /**
     * @return array <FixtureInterface>
     */
    protected static function getFixtures()
    {
        return array();
    }

    /**
     * Añadiéndole expresiones regulares a las aserciones de JSON.
     * @see PHPUnit_Framework_TestCase::assertJsonStringEqualsJsonFile
     * @see http://json-schema.org/
     */
    public static function assertJsonSchema(
        $expectedSchema, $actualJson, $message = ''
    )
    {
        $jsonCoder = static::$kernel
            ->getContainer()
            ->get('hateoas.json_validator');
        $condition = $jsonCoder->matchSchema($actualJson, $expectedSchema);
        $message .= $jsonCoder->getSchemaErrorMessage();

        return static::assertThat($condition, static::isTrue(), $message);
    }

    /**
     * Añadiéndole expresiones regulares a las aserciones de JSON.
     * @see PHPUnit_Framework_TestCase::assertJsonStringEqualsJsonFile
     * @see http://jsonapi.org/schema
     */
    public static function assertJsonApiSchema($actualJson, $message = '')
    {
        $jsonCoder = static::$kernel
            ->getContainer()
            ->get('hateoas.json_validator');
        $condition = $jsonCoder->assertJsonApi($actualJson);
        $message .= $jsonCoder->getSchemaErrorMessage();

        return static::assertThat($condition, static::isTrue(), $message);
    }

    /**
     * Maneja todas las llamadas a métodos no declarados.
     * @param string $method
     * @param array $args
     * @return bool En caso de existir el método, es una verificación.
     * @throws \BadMethodCallException Para métodos inexistentes.
     */
    public function __call($method, $args)
    {
        if (
            0 === strpos($method, 'assertResponse')
            and $status = substr($method, strlen('assertResponse'))
            and in_array($status, array_keys(static::$statusCodes))
        ) {
            array_unshift($args, static::$statusCodes[$status]);

            return call_user_func_array(
                [$this, 'assertResponseStatus'], $args
            );
        } else {
            throw new \BadMethodCallException("No such method can be called.");
        }
    }

    /**
     * Verifica el código de estado de una respuesta HTTP.
     * @param int $expectedCode
     * @param Client $client
     */
    public static function assertResponseStatus(
        $expectedCode, Client $client, $message = ''
    )
    {
        $actualCode = $client->getInfo(CURLINFO_HTTP_CODE);
        $condition = $expectedCode === $actualCode;
        $message .= sprintf(
            static::FAIL_RESPONSE_STATUS_PATTERN,
            $actualCode,
            array_flip(static::$statusCodes)[$actualCode],
            $expectedCode,
            array_flip(static::$statusCodes)[$expectedCode]
        );

        return static::assertThat($condition, static::isTrue(), $message);
    }

    /**
     * Obtiene la URL base de la API REST.
     * @return string
     */
    public function getRootUrl()
    {
        return static::$rootUrl;
    }

    /**
     * Obtiene un cliente de HTTP.
     * @param string $url
     * @param string $username
     * @param string $password
     * @return Client
     */
    protected function buildHttpClient(
        $url = NULL,
        $username = NULL,
        $password = NULL,
        $contentType = self::CONTENT_JSON_API,
        $language = self::HEADER_LOCALE
    )
    {
        $client = new Client($url);
        $client->setHead([
            'Accept: ' . $contentType,
            'Accept-Language: ' . $language,
            'Content-Type: application/vnd.api+json'
        ]);

        if (is_scalar($username) && is_scalar($password)) {
            $client->setOptions([CURLOPT_HTTPAUTH, CURLAUTH_ANY]);
            $client->setOption(CURLOPT_USERPWD, $username . ':' . $password);
        }

        return $client;
    }

    /**
     * Compone un header de autenticación WSSE.
     * @param string $username
     * @param string $password
     * @see http://en.wikipedia.org/wiki/WS-Security
     */
    protected function buildWsseHeader($username, $password)
    {
        $nonce = mt_rand();
        $date = new \DateTime();
        $createdAt = $date->format("c");
        $passwordDigest = base64_encode(sha1(
            base64_decode($nonce) . $createdAt . $password,
            TRUE
        ));
        $wsseCredentials = sprintf(
            static::WSSE_CREDENTIALS_PATTERN,
            $username,
            $passwordDigest,
            $nonce,
            $createdAt
        );

        return $wsseCredentials;
    }
    
    /**
     * Inicia el servidor de testing
     * 
     * @throws Exception
     */
    protected function runServer() 
    {
        $shell = sprintf(self::API_SERVER_SHELL, self::$serverHost, self::$serverPort, self::$kernel->getRootDir(), self::$environment);
        $retry = 0;
        
        $pid = exec($shell);
        do {
            sleep(1);
            $fp = fsockopen(self::$serverHost, self::$serverPort, $errno, $errstr);
            $retry++;
        } while (!$fp && $retry <= 4);

        if(!$fp) {
            throw new Exception('Cannot connect with server');
        }
        $this->serverPid = $pid;
    }
    
    /**
     * Detiene el servidor de testing
     */
    protected function stopServer()
    {
        exec("kill -9 {$this->serverPid}");
    }
}