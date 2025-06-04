@if(count($vacancies) > 0)
<b>{{__('trans.our_vacancies',[],$lang) }}:</b>

@foreach($vacancies as $idx => $vacancy)
{{(($page -1) * $per_page + $idx)+1}}.{{$vacancy['name_'.$lang]}}({{$vacancy['location_'.$lang]}}) @if($candidate->hasVacancy($vacancy->id))✅️@endif

@endforeach
@else
{{__('trans.no_vacancies',[],$lang) }}
@endif

