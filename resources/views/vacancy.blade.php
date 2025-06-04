@if($has_vacancy)
<b>{{__('trans.vacancy_selected',[],$lang) }}</b> ✅
@endif

<i>{{__('trans.vacancy_detail',[],$lang) }}</i>

@if($vacancy['name_'.$lang])
<b>{{__('trans.name',[],$lang) }}:</b> {{$vacancy['name_'.$lang]}}
@endif
@if($vacancy['payment_min'])
<b>{{__('trans.payment',[],$lang) }}:</b>{{$vacancy['payment_min']}} - {{$vacancy['payment_max']}}
@endif
@if($vacancy['district_'.$lang])
<b>{{__('trans.address',[],$lang) }}:</b> {{$vacancy['district_'.$lang]}}
@endif

@if(count($vacancy->requirements) > 0)
<i>{{__('trans.requirements',[],$lang) }}:</i>
@foreach($vacancy->requirements as $index => $item)
{{$index +1}}.{{$item['name_'.$lang]}}  {{$item->pivot['additional_info']}}
@endforeach
@endif

@if(count($vacancy->days) > 0)
<i>{{__('trans.schedule',[],$lang) }}:</i>
@foreach($vacancy->days as $index => $item)
{{$item['name_'.$lang]}} с {{$item->pivot['from']}} до {{$item->pivot['to']}}
@endforeach
@endif


