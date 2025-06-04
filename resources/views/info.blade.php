
<b>{{__('trans.info.data',[],$lang)}}:</b>

{{__('trans.info.name',[],$lang)}}:@if(isset($candidate->name)){{$candidate->name}} @else <i>Не указанно</i> @endif

{{__('trans.info.phone',[],$lang)}}:@if(isset($candidate->phone)){{$candidate->phone}} @else <i>Не указанно</i> @endif

{{__('trans.info.date',[],$lang)}}:@if(isset($candidate->date)){{$candidate->date}} @else <i>Не указанно</i>@endif

{{__('trans.info.idnp',[],$lang)}}:@if(isset($candidate->IDNP)){{$candidate->IDNP}} @else <i>Не указанно</i> @endif

{{__('trans.info.location',[],$lang)}}:@if(isset($candidate->location)){{$candidate->location}} @else <i>Не указанно</i>@endif


<b>{{__('trans.info.vacancies',[],$lang)}}:</b>
@if(count($candidate->vacancies) > 0)
@foreach($candidate->vacancies as $idx => $vacancy)
{{$idx+1}}.{{$vacancy['name_'.$lang]}}
@endforeach
@else
<i>Не указано</i>
@endif

@if(isset($candidate->name) && isset($candidate->phone) && isset($candidate->date) && isset($candidate->idnp) && count($candidate->vacancies) > 0)
Нажмите <b>ОТПРАВИТЬ</b> для отправки данных:
@endif
