<?php

class MultiCurl
{
    private $mh;
    private $curl;

    public function sendRequest()
    {
        if (false === $this->mh = curl_multi_init()) {
            throw new \RuntimeException('Unable to create a new cURL multi handle');
        }

        // FIXME Using a function like this does not work. If we inline the contents of addServerPushCallback(), then we got no problem
        $this->addServerPushCallback();

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($this->curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, false);
        curl_setopt($this->curl, CURLOPT_URL, 'https://http2.golang.org/serverpush');
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, function ($ch, $data) {
            return \strlen($data);
        });

        curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            return \strlen($data);
        });

        curl_multi_add_handle($this->mh, $this->curl);


        $stillRunning = null;
        while (true) {
            do {
                // Start processing each handler in the stack
                $mrc = curl_multi_exec($this->mh, $stillRunning);
            } while (CURLM_CALL_MULTI_PERFORM === $mrc);

            $info = curl_multi_info_read($this->mh);
            while (false !== $info && $info['msg'] == CURLMSG_DONE) {
                // handle any completed requests
                if (CURLMSG_DONE !== $info['msg']) {
                    continue;
                }
                die("Start handle request. ");
            }
        }
    }

    private function addServerPushCallback(): void
    {

        $callback = static function () {
            return CURL_PUSH_OK;
        };

        curl_multi_setopt($this->mh, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
        curl_multi_setopt($this->mh, CURLMOPT_PUSHFUNCTION, $callback);
    }
}

$buzz = new MultiCurl();
$buzz->sendRequest();
