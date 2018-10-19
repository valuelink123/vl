@foreach($crumbs as $crumb)
    <li>
        @if(is_array($crumb))
            <a href="{!! $crumb[1] !!}">{!! $crumb[0] !!}</a>
        @else
            <span>{!! $crumb !!}</span>
        @endif
        @if(!$loop->last)
            <i class="fa fa-circle"></i>
        @endif
    </li>
@endforeach
