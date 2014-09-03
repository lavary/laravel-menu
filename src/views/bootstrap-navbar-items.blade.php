@foreach($items as $item)
  <li@lm-attrs($item) @if($item->hasChildren())class ="dropdown"@endif @lm-endattrs>
    @if($item->link) <a@lm-attrs($item->link) @if($item->hasChildren()) class="dropdown-toggle" data-toggle="dropdown" @endif @lm-endattrs href="{{ $item->url() }}">
      {{ $item->title }}
      @if($item->hasChildren()) <b class="caret"></b> @endif
    </a>
    @else
      {{$item->title}}
    @endif
    @if($item->hasChildren())
      <ul class="dropdown-menu">
        @include('laravel-menu::bootstrap-navbar-items', 
          array('items' => $item->children()))
      </ul> 
    @endif
  </li>
  @if($item->divider)
  	<li{{\HTML::attributes($item->divider)}}></li>
  @endif
@endforeach
