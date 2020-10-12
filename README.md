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


# Make listener field

The `listener` Chain initializes via the `with` method. the `with` method accepts `an array of events` as the first argument and a callback for the fields as second arguemnt.
for listening to a `trigger` Chain you should pass the `name` of the Chain as the `event` name. for listening to a field of a `trigger` Chain you can `prefix the field name by the Chain name` and listen to it. for example, you can listen to the `name` field of a Chain (Chain field named by the 'test'), with the `test.name` event.

            
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
