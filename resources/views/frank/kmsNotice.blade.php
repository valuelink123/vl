@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Notice Center
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="row" style="margin: 2em 0 3em 1em;">
                <div class="list-group col-lg-9 col-md-12">
                    @foreach($rows as $row)
                        <div class="list-group-item">
                            <h4 class="list-group-item-heading">
                                <a href="#"><b>{!! $row['title'] !!}</b></a>
                                <a href="#" style="float:right">View all</a>
                                <b style="clear:both;"></b>
                            </h4>
                            <p class="list-group-item-text">{!! $row['content'] !!}</p>
                            <br/>
                        </div>
                    @endforeach
                    <div class="list-group-item">
                        <ul class="pagination">
                            <li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">«</span></a></li>
                            <li class="active"><a href="#">1 <span class="sr-only">(current)</span></a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">4</a></li>
                            <li><a href="#">5</a></li>
                            <li><a href="#" aria-label="Next"><span aria-hidden="true">»</span></a></li>
                        </ul>
                        <div style="float:right; padding-top:16px;">Showing 1 to 10 of 667 entries</div>
                        <b style="clear:both;"></b>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

    </script>

@endsection