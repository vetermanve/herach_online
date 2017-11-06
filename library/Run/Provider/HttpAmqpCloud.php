<?php


namespace Run\Provider;


use Mu\Amqp\Queue;
use Mu\Env;
use Router\Actors\RouterRequestConsumer;
use Run\RunContext;
use Run\RunRequest;
use Run\Spec\AmqpHttpRequest;
use Run\Spec\HttpRequestHeaders;
use Run\Spec\HttpRequestMetaSpec;
use Run\Util\HttpParse;
use Run\Util\HttpResourceHelper;
use Run\Util\RestMethodHelper;

class HttpAmqpCloud extends RunProviderProto
{
    private $restarting = true;
    
    /**
     * @var RouterRequestConsumer
     */
    private $consumer;
    
    public function prepare()
    {
        $queueName = $this->context->get(RunContext::QUEUE_INCOMING, 'http.amqp.cloud.requests.default');
        $amqpCustomHost  = $this->context->get(RunContext::AMQP_REQUEST_CLOUD_HOST);
        $amqpCustomPort  = $this->context->get(RunContext::AMQP_REQUEST_CLOUD_PORT);
    
        if ($amqpCustomHost) {
            Env::getRouter()->registerQueue($queueName, $amqpCustomHost, $amqpCustomPort);    
        }
        
        $this->consumer = Env::getRouter()->getConsumer($queueName);
//        $this->consumer->setEventDispatcher(Env::getEventDispatcher());
    }
    
    public function run()
    {
        $this->runtime->debug('RUN_HTTP_CLOUD_START', [$this->consumer->getFullConfiguration()]);
        $this->consumer->consume([$this, '_process']);
    }
    
    public function _process(\AMQPEnvelope $envelope)
    {
        $amqpRequest = json_decode($envelope->getBody(), 1);
        
        if (!$amqpRequest) {
            $this->runtime->emergency('RUN_ERROR Broken body of envelope!', ['body' => $envelope->getBody()]);
        
            return $this->restarting;
        }
        
        $this->runtime->debug('RUN_HTTP_AMQP_REQ_DETAIL', $amqpRequest);
    
        $this->runtime->runtime('RUN_HTTP_AMQP_REQ_INCOME', ['request_id' => $amqpRequest[AmqpHttpRequest::UID]]);
    
        // getting params
        parse_str($amqpRequest[AmqpHttpRequest::QUERY], $params);
    
        // path hack for old nginx 
        $path = $amqpRequest[AmqpHttpRequest::PATH];
        if (isset($params['path'])) {
            if (strpos($path, 'index.php')) {
                $path = str_replace('index.php', $params['path'], $path);
            } else {
                $path = $params['path'];
            }
        
            unset($params['path']);
        }
    
        // getting routing data
        $pathData = new HttpResourceHelper($path);
        if ($pathData->getId()) {
            $params['id'] = $pathData->getId();    
        }
    
        RestMethodHelper::makeStrictParams($params);
    
        // create main request object
        $request = new RunRequest($amqpRequest[AmqpHttpRequest::UID], $pathData->getResource(), $amqpRequest[AmqpHttpRequest::REPLY]);
        $request->params = $params;
                           
        // data processing
        {
            $request->body = $amqpRequest[AmqpHttpRequest::DATA] ? trim($amqpRequest[AmqpHttpRequest::DATA]) : '';
            $decodedData   = $request->body && strpos($request->body, '{') === 0 ? json_decode($amqpRequest[AmqpHttpRequest::DATA], true) : [];
            $request->data = $decodedData ? $decodedData : [];    
        }
        
        $method = RestMethodHelper::getRealMethod($amqpRequest[AmqpHttpRequest::METHOD], $request);
    
        $request->meta = [
            HttpRequestMetaSpec::REQUEST_METHOD  => $method,
            HttpRequestMetaSpec::REQUEST_VERSION => $pathData->getVersion(),
            HttpRequestMetaSpec::REQUEST_HEADERS => $amqpRequest[AmqpHttpRequest::HEADERS],
        ];
    
        $request->meta[HttpRequestMetaSpec::REQUEST_SOURCE] = $request->getMetaItem(HttpRequestMetaSpec::REQUEST_HEADERS, HttpRequestHeaders::ORIGIN, '');
    
        $cookie = $request->getMetaItem(HttpRequestMetaSpec::REQUEST_HEADERS, HttpRequestHeaders::COOKIE, '');
    
        $request->getChannelState()->setPacked(HttpParse::cookie($cookie));
    
        $this->core->process($request);
    
        $this->runtime->runtime('RUN_HTTP_AMQP_REQ_END', ['request_id' => $amqpRequest[AmqpHttpRequest::UID]]);
    
        return $this->restarting;
    }
    
    public function cancelConsuming()
    {
        $this->restarting = false;
    }
}