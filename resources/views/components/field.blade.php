@props(['field' => 'id', 'OB'=>'', 'OD' => ''])
<th {{ $attributes }} @if($field!='id') wire:click.prevent="orderByDirection('{{$field}}')" @endif class="min-w-40 @if($field!='id') cursor-pointer @endif px-4 py-3 capitalize text-sm font-bold dark:text-gray-400">
  <div class="relative inline-flex items-center gap-2">
    {{$slot}}
    @if($field!='id')
      <div class="flex flex-col -space-y-1">
        <x-icon name="o-chevron-up" class="w-3 h-3 transition-opacity {{$OB==$field && $OD=='asc'?'opacity-100 text-base-content':'opacity-50 hover:opacity-60'}}" />
        <x-icon name="o-chevron-down" class="w-3 h-3 transition-opacity {{$OB==$field && $OD=='desc'?'opacity-100 text-base-content':'opacity-50 hover:opacity-60'}}" />
      </div>
    @endif
  </div>
</th>
