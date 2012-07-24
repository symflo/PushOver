<?php

namespace Sly\Push;

use Sly\Push\PushInterface;

use Buzz\Browser;
use Buzz\Message\Response;
use Buzz\Client\Curl;

/**
 * Push class.
 *
 * @uses PushInterface
 * @author Cédric Dugat <ph3@slynett.com>
 */
class Push implements PushInterface
{
    const API_URL = 'https://api.pushover.net/1/messages.json';

    protected $browser;

    protected $userKey;
    protected $apiKey;

    protected $pushTitle;
    protected $pushMessage;
    protected $pushDevice;

    /**
     * Constructor.
     *
     * @param string $userKey User key
     * @param string $apiKey  API key
     */
    public function __construct($userKey, $apiKey)
    {
        $this->browser = new Browser(new Curl());
        $this->userKey = $userKey;
        $this->apiKey  = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message, array $options = array())
    {
        $this->pushMessage = $message;
        $this->pushTitle   = isset($options['title']) ? $options['title'] : null;
        $this->pushDevice  = isset($options['device']) ? $options['device'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function push()
    {
        if (empty($this->pushMessage) || null == $this->pushMessage) {
            throw new \Exception('There is no message to push');
        }

        $response = $this->browser->submit(self::API_URL, array(
            'user'    => $this->userKey,
            'token'   => $this->apiKey,
            'message' => $this->pushMessage,
            'title'   => $this->pushTitle,
            'device'  => $this->pushDevice,
        ));

        $responseObj = $this->getResponseObj($response);

        if ($responseObj && true === is_object($responseObj)) {
            return (bool) $responseObj->status;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseObj(Response $response)
    {
        $responseObj = json_decode($response->getContent());

        if (isset($responseObj->user) && $responseObj->user == 'invalid') {
            throw new \Exception('User key is invalid');
        }

        if (isset($responseObj->token) && $responseObj->token == 'invalid') {
            throw new \Exception('User key is invalid');
        }

        return $responseObj;
    }
}