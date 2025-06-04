@if($document->pivot->src == null) {{__('trans.set_doc',[],$lang)}} "{{$document['name_'.$lang]}}" ✏@else {{__('trans.edit_doc',[],$lang)}} "{{$document['name_'.$lang]}}" 📝@endif
