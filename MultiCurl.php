<?php

declare(strict_types=1);

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\ClientException;
use Buzz\Message\ResponseBuilder;
use Psr\Http\Message\RequestInterface;
use Buzz\Exception\InvalidArgumentException;
use Http\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;

class MultiCurl
{
    private $curlm;
    private $curl;

    /**
     * @var ResponseFactoryInterface|ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var bool
     */
    private $serverPushSupported = false;


    /**
     * @param ResponseFactoryInterface|ResponseFactory $responseFactory
     */
    public function __construct($responseFactory, array $options = [])
    {
        error_reporting(-1);
        ini_set('display_errors', 'On');


        if (!$responseFactory instanceof ResponseFactoryInterface && !$responseFactory instanceof ResponseFactory) {
            throw new InvalidArgumentException(sprintf('First argument of %s must be an instance of %s or %s.', __CLASS__, ResponseFactoryInterface::class, ResponseFactory::class));
        }

        $this->serverPushSupported = PHP_VERSION_ID >= 70215 && version_compare(curl_version()['version'], '7.61.0') >= 0;
        if (false === $this->curlm = curl_multi_init()) {
            throw new ClientException('Unable to create a new cURL multi handle');
        }

        if ($this->serverPushSupported) {
            echo "Server push is supported\n";
            $cb = function ($parent, $pushed, $headers) {
                //echo "callback\n";

                curl_setopt($pushed, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($pushed, CURLOPT_HEADER, true);

                return CURL_PUSH_OK;
            };

            echo "set options\n";
            curl_multi_setopt($this->curlm, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
            curl_multi_setopt($this->curlm, CURLMOPT_PUSHFUNCTION, $cb);
        } else {
            echo "Server push NOT is supported\n";
        }
    }

    /**
     * This is a blocking function call.
     */
    public function sendRequest(RequestInterface $request, array $options = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_URL, 'https://http2.golang.org/serverpush');
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($curl, CURLOPT_HTTPGET, true);

        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($ch, $data) {
            echo "foo";
            return \strlen($data);
        });

        curl_setopt($curl, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            echo "bar";
            return \strlen($data);
        });

        $this->curl = $curl;
        echo "add handle\n";
        curl_multi_add_handle($this->curlm, $curl);

        $this->flush();

        echo "return response\n";
    }


    public function flush(): void
    {
        while (null !== $this->curl) {
            $this->proceed();
        }
    }

    public function proceed(): void
    {
        $exception = null;
        $stillRunning = null;
        echo "2";
        do {
            // Start processing each handler in the stack
            sleep(1);
            $mrc = curl_multi_exec($this->curlm, $stillRunning);
        } while (CURLM_CALL_MULTI_PERFORM === $mrc);


        //echo "idx: ".$this->i."\n";$this->i++;
        echo "a";
        $info = curl_multi_info_read($this->curlm);
        echo "b";
        while (false !== $info && $info['msg'] == CURLMSG_DONE) {
            echo "inside\n";
            die("inside4");
            // handle any completed requests
            if (CURLMSG_DONE !== $info['msg']) {
                continue;
            }
            echo "inside2\n";

            $handled = false;

            $curl = $this->curl;
            var_dump($curl);
            // Try to find the correct handle from the queue.
            if ($curl !== $info['handle']) {
                continue;
            }
            echo "handle matched\n";

            try {
                $handled = true;
                $response = null;
            } catch (\Throwable $e) {
                if (null === $exception) {
                    $exception = $e;
                    echo "exception";
                }
            }

            // remove from queue
            echo "remove handle\n";
            curl_multi_remove_handle($this->curlm, $curl);
            echo "unset queue\n";
            $this->curl = null;

            $exception = null;

            if (!$handled) {
                // It must be a pushed response.
                echo "handle pushed\n";
                // TODO release pushed handlers
            }
        }

        echo "c\n";

        // cleanup
        if ($this->curl === null) {
            echo "cleanup\n";
            curl_multi_close($this->curlm);
            $this->curlm = null;
        }
    }
}
