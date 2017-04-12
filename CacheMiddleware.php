<?php

class CachingMiddleware {

    /**
     * @var Collection
     */
    protected $cachedActions;


    /**
     * @var int
     */
    protected $lifeTime = 120;

    /**
     * @var Request
     */
    protected $request;

    public function handle(Request $request, Closure $next) {
        $this->request = $request;

        return $this->getResponse($next);
    }

    protected function getResponse(Closure $next) {
        // check if we don't need to cache
        if (!$this->isCached()) return $next($this->request);

        $cacheKey = $this->request->getPathInfo();

        if(!\Cache::has($cacheKey)) {
            $response = $next($this->request);

            $response->original = '';

            \Cache::put($cacheKey, $response, $this->lifeTime);
        }
    }

    protected function isCached() {
        if(app()->environment('local')) return false;

        return $this->checkRoute();
    }

    protected function checkRoute() {
        list($controller, $action) = explode('@', $this->request->route()->getActionName());

        $cachedController = $this->cachedActions->get($controller, false);

        if($cachedController === false) return false;

        if($cachedController->isEmpty()) return true;

        return !! $cachedController->get($action, false);
    }
}