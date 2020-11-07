<?php

namespace Armincms\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\{Field, FieldCollection};

class Chain extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'chain'; 

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var \Closure|bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the element should be shown on the detail view.
     *
     * @var \Closure|bool
     */
    public $showOnDetail = false;

    /**
     * Fields  resolver.
     * 
     * @var callacble
     */
    protected $fieldsCallback; 

    /**
     * List of events.
     * 
     * @var array
     */
    protected $events = [];

    /**
     * List of chain names.
     * 
     * @var array
     */
    private static $chains = [];

    /**
     * Create a new field.
     *
     * @param  callable  $callback
     * @param  string  $name
     * @return void
     */
    public function __construct(string $name, callable $callback)
    {   
    	parent::__construct($name);
    	
    	$this->fieldsCallback = $callback;
    } 

    /**
     * Create a stimulus chain.
     * 
     * @param  string   $name  
     * @param  callable $callback
     * @return $this          
     */
    public static function as(string $name, callable $callback)
    {
    	return new static($name, $callback);
    }

    /**
     * Create a listener chain instance.
     * 
     * @param  string|array   $chain   
     * @param  callable $callback 
     * @param  string $name 
     * @return $this           
     */
    public static function with($chain, callable $callback, string $name = null)
    {
    	return tap(new static($name ?? static::prepareName($chain), $callback), function($chainField) use ($chain) {
    		$chainField->listen($chain);
    	});
    }

    /**
     * Preapare a safe name for unnamed fields.
     * 
     * @param  string $chain 
     * @return string       
     */
    private static function prepareName($chain)
    {
        if(! isset(static::$chains[$name = implode('-', (array) $chain)])) {
            static::$chains[$name] = 0;
        } 

        return md5($name . ++static::$chains[$name]);
    }

    /**
     * Register the event names.
     * 
     * @param  string|array $name 
     * @return $this       
     */
    public function listen($name)
    {
    	$this->events = array_merge($this->events,  is_array($name) ? $name : func_get_args());

    	return $this;
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    { 
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  object  $model
     * @return mixed
     */
    public function fill(NovaRequest $request, $model)
    {
        $this->resolveFields($request)->each->fill($request, $model); 
    }

    /**
     * Resolves fields for the given request.
     * 
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request 
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function resolveFields(NovaRequest $request)
    {
        return new FieldCollection(call_user_func($this->fieldsCallback, $request)); 
    } 

    /**
     * Get the validation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getRules(NovaRequest $request)
    { 
        return $this->resolveFields($request)->flatMap->getRules($request)->merge(parent::getRules($request))->all();
    }

    /**
     * Get the creation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    { 
        $creationRules = parent::getCreationRules($request);

        return $this->resolveFields($request)->flatMap->getCreationRules($request)->merge($creationRules)->all();
    } 

    /**
     * Get the update rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        $updateRules = parent::getCreationRules($request);

        return $this->resolveFields($request)->flatMap->getUpdateRules($request)->merge($updateRules)->all();
    } 

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
    	return array_merge(parent::jsonSerialize(), [ 
    		'events' => $this->events,
    	]);
    }
}
