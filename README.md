# chain
Conditionally load a field with the value of the other fields.

## Installation

Via composer: `composer require armincms/chain`.

## Usage

The Chain field works as a `trigger` and `listener`. a `trigger` chain listens to the update events of entire fields and the `listener` chain listening to the `trigger` Chain events and its entire fields together.

# Make trigger field

The ` trigger` Chain initializes via the `as` method. the `as` method accepts two arguments. the first argument is the name of `event` and the second argument is a `fields` callback. it receives a request instance for resolving fields.

            Chain::as('name', function() {
                return [ 
                    Text::make('Name')
                        ->sortable()
                        ->rules('required', 'max:255'), 

                    Text::make('Username')
                        ->sortable()
                        ->rules('required', 'max:255'), 
                ];  
            }), 


# Make trigger field

A `listener` accept array of events as first argument and a callback for resolve fields. for listen to the other Chain you chould pass the name of it Chain as the event name. for listen to the fields of the other chain you should pass the Chain prefixed name of the field. for example you can listen to `name` field of the Chain named `test` by the evnet `test.name`.  


            
            Chain::with('name', function($request) {
                if($request->filled('username')){
                    return [ 
                        Text::make('Password')
                            ->sortable()
                            ->rules('required', 'max:255'), 
                            
                        Text::make('Confirmation')
                            ->sortable()
                            ->rules('required', 'max:255'), 
                    ];  
                }
                
                return [];
            }, 'second-chain'), 

            
            Chain::with('second-chain.confirmation', function($request) {
                return [ 
                    Boolean::make('Passed')
                      ->readonly()
                      ->withMeta(['value' => 1]), 
                ]; 
            }),
