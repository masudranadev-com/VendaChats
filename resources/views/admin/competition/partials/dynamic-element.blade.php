@php
  $tag = $element['tag'] ?? 'div';
  $attrs = $element['attrs'] ?? [];
  $children = $element['children'] ?? [];
@endphp
<{{ $tag }}
  @foreach ($attrs as $key => $value)
    {{ $key }}="{{ $value }}"
  @endforeach
>
  @if (isset($element['text']))
    {{ $element['text'] }}
  @endif

  @foreach ($children as $child)
    @include('admin.competition.partials.dynamic-element', ['element' => $child])
  @endforeach
</{{ $tag }}>
