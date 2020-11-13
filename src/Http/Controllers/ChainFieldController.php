<?php

namespace Armincms\Fields\Http\Controllers;
 
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChainFieldController extends Controller
{
    /**
     * Retrieve the given field for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(NovaRequest $request)
    { 
        $resource = $request->newResourceWith(
            $request->findModelQuery()->first() ?? $request->model()
        ); 

        return $resource
                ->availableFields($request)
                ->authorized($request)
                ->findFieldByAttribute($request->viaField, function () {
                    abort(response()->json([]));
                })
                ->resolveFields($request) 
                ->each->resolve($resource)
                ->authorized($request); 
    }
}
