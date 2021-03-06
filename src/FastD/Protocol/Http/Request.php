<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午3:48
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace FastD\Protocol\Http;

use FastD\Protocol\Http\Session\Session;
use FastD\Protocol\Http\Attribute\FilesAttribute;
use FastD\Protocol\Http\Attribute\CookiesAttribute;
use FastD\Protocol\Http\Attribute\HeaderAttribute;
use FastD\Protocol\Http\Attribute\QueryAttribute;
use FastD\Protocol\Http\Attribute\RequestAttribute;
use FastD\Protocol\Http\Attribute\ServerAttribute;
use FastD\Protocol\Http\Launcher\RequestLauncher;
use FastD\Protocol\Http\Session\SessionHandlerAbstract;

/**
 * Class Request
 *
 * @package FastD\Protocol\Http
 */
class Request
{
    /**
     * $_GET
     *
     * @var QueryAttribute
     */
    public $query;

    /**
     * $_POST
     *
     * @var RequestAttribute
     */
    public $request;

    /**
     * $_FILES
     *
     * @var FilesAttribute
     */
    public $files;

    /**
     * $_COOKIE
     *
     * @var CookiesAttribute
     */
    public $cookies;

    /**
     * $_SERVER
     *
     * @var ServerAttribute
     */
    public $server;

    /**
     * Http request headers or response headers.
     *
     * new HeaderAttribute($sever->getHeaders());
     *
     * @var HeaderAttribute
     */
    public $header;

    /**
     * Session management.
     *
     * $_SESSION
     *
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    private $content;

    /**
     * @var Request
     */
    private static $requestFactory;

    /**
     * The http request is has once request object.
     *
     * @param $get
     * @param $post
     * @param $files
     * @param $cookie
     * @param $server
     */
    public function __construct(array $get = [], array $post = [], array $files = [], array $cookie = [], array $server = [])
    {
        $this->initialize($get, $post, $files, $cookie, $server);
    }

    /**
     * The http request is has once request object.
     *
     * @param $get
     * @param $post
     * @param $files
     * @param $cookie
     * @param $server
     */
    public function initialize(array $get = [], array $post = [], array $files = [], array $cookie = [], array $server = [])
    {
        $this->query    = new QueryAttribute($get);
        $this->request  = new RequestAttribute($post);
        $this->files    = new FilesAttribute($files);
        $this->cookies  = new CookiesAttribute($cookie);
        $this->server   = new ServerAttribute($server);
        $this->header   = new HeaderAttribute($this->server->getHeaders());
    }

    /**
     * @return string
     */
    public function getHttpAndDomain()
    {
        return ('' == ($schema = $this->server->get('REQUEST_SCHEME')) ? '//' : ($schema . '://')) . $this->getDomain();
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->server->get('SERVER_NAME');
    }

    /**
     * Get user client request ip.
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->server->getClientIp();
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->server->getRequestUri();
    }

    /**
     * @return bool|string
     */
    public function getBaseUrl()
    {
        return $this->server->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->server->getRootPath();
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->server->getPathInfo();
    }

    /**
     * @return float
     */
    public function getRequestTime()
    {
        return $this->server->getRequestTime();
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD');
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->server->getFormat();
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return $this->header->has('X_REQUESTED_WITH') ? 'xmlhttprequest' === strtolower($this->header->get('X_REQUESTED_WITH')) : false;
    }

    /**
     * @param $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * @param SessionHandlerAbstract $sessionHandler
     * @return Session
     */
    public function getSessionHandle(SessionHandlerAbstract $sessionHandler = null)
    {
        if (null === $this->session) {
            $this->session = new Session($sessionHandler);
        }

        return $this->session;
    }

    /**
     * @param $name
     * @return array|int|string
     */
    public function getSession($name)
    {
        return $this->getSessionHandle()->getSession($name);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setSession($name, $value)
    {
        return $this->getSessionHandle()->setSession($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSession($name)
    {
        return $this->getSessionHandle()->hasSession($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clearSession($name)
    {
        return $this->getSessionHandle()->clearSession($name);
    }

    /**
     * @param $name
     * @return Cookie\Cookie
     */
    public function getCookie($name)
    {
        return $this->cookies->getCookie($name);
    }

    /**
     * @param        $name
     * @param null   $value
     * @param int    $expire
     * @param string $path
     * @param null   $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return CookiesAttribute
     */
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        return $this->cookies->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, true);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCookie($name)
    {
        return $this->cookies->hasCookie($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clearCookie($name)
    {
        return $this->cookies->clearCookie($name);
    }

    /**
     * @param array $config
     * @return File\Uploaded\Uploader
     */
    public function getUploader(array $config = [])
    {
        return $this->files->getUploader($config);
    }

    /**
     * @return resource|string
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Create one http request handle.
     *
     * @return Request|static
     */
    public static function createRequestHandle()
    {
        if (null === self::$requestFactory) {
            self::$requestFactory = new static($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);

            if (in_array(self::$requestFactory->server->get('REQUEST_METHOD'), array('PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'))
            ) {
                parse_str(self::$requestFactory->getContent(), $arguments);
                self::$requestFactory->request = new RequestAttribute($arguments);
            }
        }

        return self::$requestFactory;
    }

    /**
     * @param        $url
     * @param array  $arguments
     * @param int    $timeout
     * @return RequestLauncher
     */
    public function createRequest($url, array $arguments = array(), $timeout = 3)
    {
        return new RequestLauncher($url, $arguments, $timeout);
    }
}