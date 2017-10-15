<?php


namespace Run\Processor;


use Mu\Env;
use Rpc\RpcMaster;
use Run\ChannelMessage\HttpReply;
use Run\Execution\Rest\MsgModificator\DateWiperAndNullErase;
use Run\Execution\RestAppExecution;
use Run\Rest\Exception\Redirect;
use Run\RunContext;
use Run\RunRequest;
use Run\Spec\HttpRequestHeaders;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;
use Run\Util\SchemaChecker;
use Run\Util\SessionBuilder;

class AlolRestRequestProcessor extends RunRequestProcessorProto
{
    private $profilingEnabled = false;
    
    public function prepare()
    {
        if (!$this->sessionBuilder) {
            $this->sessionBuilder = new SessionBuilder();    
        }
        
        $this->sessionBuilder->follow($this);
        
        $this->addMsgModificator(new DateWiperAndNullErase());
        
        $this->profilingEnabled = $this->context->getEnv(RunContext::REQUEST_PROFILING_ENABLED);
        
        if ($this->profilingEnabled) {
            RpcMaster::i()->setEnabled(true);
        }
    }
    
    public function process(RunRequest $request)
    {
        RpcMaster::i()->clear();
        
        /* prepare execution */
        $execution = new RestAppExecution();
        $execution->follow($this);
        $execution->setRunRequest($request);
        $execution->start();
        
        /* prepare response */
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);

        /* Try to execute */
        try {
            // Построим сессию
            $session = $this->sessionBuilder->getSession($request);
            $session->start();

            // Положим сессию в контейнер зависимостей, вдруг пригодится
            Env::getContainer()->setModule('session', $session);

            /* Проверим есть ли у нас такой контроллер  */
            $execution->extractRequestClassAndAction();
            $execution->prepareController();
            if (!$execution->getController()) {
                $response->setCode(HttpResponseSpec::HTTP_CODE_NOT_FOUND);

                $response->body = [
                    HttpResponseSpec::STATUS     => 1,
                    HttpResponseSpec::MESSAGE    => 'Resource not found.',
                ];

                return $this->processResponse($response, $request, $execution);
            }

            /* Проверим есть ли у нас такой экшн */
            $execution->prepareAction();
            if (!$execution->getAction()) {
                $response->setCode(HttpResponseSpec::HTTP_CODE_UNSUPPORTED);

                $response->body = [
                    HttpResponseSpec::STATUS     => 1,
                    HttpResponseSpec::MESSAGE    => 'Method not supported.',
                ];

                return $this->processResponse($response, $request, $execution);
            }

            
            // Поехали
            $execution->run();
            
            // Приехали
            $response->setCode(HttpResponseSpec::HTTP_CODE_OK);
            $response->body = [
                HttpResponseSpec::STATUS     => $execution->getStatus(),
                HttpResponseSpec::DATA       => $execution->getData(),
                HttpResponseSpec::REQUEST_ID => $request->getUid(),
            ];
            
        } catch (Redirect $e) { // some redirect
            $response->setCode(HttpResponseSpec::HTTP_CODE_REDIRECT);
            $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_LOCATION, $e->getUrl());
            
            return $this->sendResponse($response, $request);
        } catch (\RuntimeException $e) { // some logic hard exception
            $response->setCode(HttpResponseSpec::HTTP_CODE_ERROR);
    
            $response->body = [
                HttpResponseSpec::STATUS     => 1,
                HttpResponseSpec::MESSAGE    => 'Internal error.',
            ];
    
            if ($this->profilingEnabled) {
                $response->body['error'] = [
                    'msg'   => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            };
    
            $this->runtime->error($request->getResource() . ' has exception: ' . $e->getMessage(), [
                'params' => $request->params,
                'place'  => str_replace(getcwd(), '', $e->getFile()) . ':' . $e->getLine(),
                'trace'  => $e->getTraceAsString(),
            ]);
            
//        } catch (\ErrorException $e) { // some logic exception
//            $response->setCode($e->getStatusCode());
//            
//            $response->body = [
//                HttpResponseSpec::STATUS     => $e->getErrorNo(),
//                HttpResponseSpec::MESSAGE    => $e->getMsg(),
//            ];
//            
//            if ($e instanceof Validator && strlen($e->getErrorFieldName()) > 0) {
//                $response->body['field'] = $e->getErrorFieldName();
//            }
//            
//            if ($e instanceof AdditionalExceptionInterface) {
//                $response->body += (array)$e->getAdditionalFields();
//            }

        } catch (\Exception $e) { // some unexpected exception
            $response->setCode(HttpResponseSpec::HTTP_CODE_ERROR);
            
            $response->body = [
                HttpResponseSpec::STATUS     => 1,
                HttpResponseSpec::MESSAGE    => 'Internal error.',
            ];
            
            if ($this->profilingEnabled) {
                $response->body['error'] = [
                    'msg'   => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            };
            
            $this->runtime->error($request->getResource() . ' has exception: ' . $e->getMessage(), [
                'params' => $request->params,
                'place'  => str_replace(getcwd(), '', $e->getFile()) . ':' . $e->getLine(),
                'trace'  => $e->getTraceAsString(),
            ]);
        }
    
        return $this->processResponse($response, $request, $execution);
    }
    
    public function processResponse (HttpReply $response, RunRequest $request, RestAppExecution $execution) 
    {
        $session = Env::getSession(false);
        if ($session && $userId = $session->getUserId()) {
            $response->body[HttpResponseSpec::USER_ID] = $userId;
        }
    
        if ($this->profilingEnabled) {
            $response->body['__profiling'] = [
                'run'   => [
                    'class'  => get_class($execution->getController()),
                    'method' => $execution->getAction(),
                ],
                'calls' => RpcMaster::i()->getProfiling(),
            ];
        }
    
        if ($origin = $request->getMetaItem(HttpRequestMetaSpec::REQUEST_HEADERS, HttpRequestHeaders::ORIGIN)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }
    
        if ($request->getMetaItem(HttpRequestMetaSpec::REQUEST_HEADERS, HttpRequestHeaders::SUPPRESS_HTTP_CODE)) {
            $currentCode = $response->getCode();
            $response->setCode(HttpResponseSpec::HTTP_CODE_OK);
            $response->setHeader('X-Status-Code', $currentCode);
        }
    

    
        $response->setHeader('X-Application', $this->context->get(RunContext::IDENTITY));
        
        // set request id
        $response->setHeader('X-Request-id', $request->getUid());
        $response->body[HttpResponseSpec::REQUEST_ID] = $request->getUid();
    
        // set time
        $response->body[HttpResponseSpec::TIME] = round($execution->getExecutionTime(), 6);

        $dispatcher = $execution->getDispatcher();
        // в случае если у нас есть диспатчер
        if ($dispatcher) {
            // если явно передан типа контента
            $contentType = $execution->getDispatcher()->getContentType();
            if ($contentType) {
                $response->setHeader('Content-Type', $contentType . '; charset=UTF-8');
            }
        }

        return $this->sendResponse($response, $request);   
    }
    
    /**
     * @param SessionBuilder $sessionBuilder
     */
    public function setSessionBuilder($sessionBuilder)
    {
        $this->sessionBuilder = $sessionBuilder;
    }
}
