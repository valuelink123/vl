<?php
$active = (0 === strpos($_SERVER['REQUEST_URI'], $uri)) ? 'active' : '';
?>
<li class="nav-item {!! $active !!}">
    <a href="{!! $uri !!}" class="nav-link nav-toggle">
        <i class="fa fa-{!! $fa !!}"></i>
        <span class="title">{!! $text !!}</span>
        @if($active)
            <span class="selected"></span>
        @endif
    </a>
</li>