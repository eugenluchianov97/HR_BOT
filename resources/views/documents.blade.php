{{__('trans.needed_docs',[],$lang)}}:

@foreach($documents as $idx => $document)

{{$idx+1}}.{{$document['name_'.$lang]}} @if(isset($document->pivot->src)) ✅ @else ❌ @endif
@endforeach
