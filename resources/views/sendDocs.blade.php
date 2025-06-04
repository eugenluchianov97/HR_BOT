Кандидат отправил необходимые документы!

<b>Указанное имя:</b>@if(isset($candidate->name)){{$candidate->name}} @else <i>Не указано</i> @endif

<b>Указанный телефон:</b>@if(isset($candidate->phone)){{$candidate->phone}} @else <i>Не указано</i> @endif

<b>Указанный дата рождения:</b>@if(isset($candidate->date)){{$candidate->date}} @else <i>Не указано</i> @endif

<b>Указанный IDNP:</b>@if(isset($candidate->IDNP)){{$candidate->IDNP}} @else <i>Не указано</i> @endif

<b>Вакансии:</b>
@foreach($candidate->vacancies as $idx => $vacancy)
    {{$idx+1}}.{{$vacancy['name_'.$lang]}}
@endforeach
