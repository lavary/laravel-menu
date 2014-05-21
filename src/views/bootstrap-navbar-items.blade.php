@foreach($items as $item)
  <li @if($item->hasChilderen()) class="dropdown" @endif>
      <a href="{{ $item->link->get_url() }}" @if($item->hasChilderen()) class="dropdown-toggle" data-toggle="dropdown" @endif>{{ $item->link->get_text() }} @if($item->hasChilderen()) <b class="caret"></b> @endif</a>
      @if($item->hasChilderen())
        <ul class="dropdown-menu">
              @include(Config::get('laravel-menu::bootstrap-navbar-items'), array('items' => $item->childeren()))
        </ul> 
      @endif
  </li>
@endforeach