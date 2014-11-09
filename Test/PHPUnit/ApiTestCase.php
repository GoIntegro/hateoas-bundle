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
    GoIntegro\Bundle\HateoasBundle\Http\Client;

abstract class ApiTestCase extends WebTestCase
{
    const FAIL_RESPONSE_STATUS_PATTERN = "Failed asserting that the status code %s (\"%s)\" matches the expected %s (\"%s\").",
        WSSE_CREDENTIALS_PATTERN = "X-WSSE: UsernameToken Username=\"%s\", PasswordDigest=\"%s\", Nonce=\"%s\", Created=\"%s\"",
        HEADER_LOCALE = 'en-GB';

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
     * Prepara el entorno antes de la primer prueba.
     */
    public static function setUpBeforeClass()
    {
        // Inicializamos el framework web.
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $container = static::$kernel->getContainer();

        // Obtenemos algunos servicios de uso común.
        static::$em = $container->get('doctrine')->getManager();
        static::$rootUrl = $container->getParameter('api.base_url');

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
    public function setUp()
    {
        parent::setUp();
        self::$kernel->boot();
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
            ->get('hateoas.json_coder');
        $condition = $jsonCoder->matchSchema($actualJson, $expectedSchema);

        try {
            $jsonCoder->throwLastSchemaError();
        } catch (\Exception $e) {
            $message .= $e->getMessage();
        }

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
            ->get('hateoas.json_coder');
        $condition = $jsonCoder->assertJsonApi($actualJson);

        try {
            $jsonCoder->throwLastSchemaError();
        } catch (\Exception $e) {
            $message .= $e->getMessage();
        }

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
        $url = NULL, $username = NULL, $password = NULL
    )
    {
        $client = new Client($url);
        $client->setHead([
            'Accept: application/json',
            'Accept-Language: ' . static::HEADER_LOCALE
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
}
