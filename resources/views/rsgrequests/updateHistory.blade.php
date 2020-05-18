<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4>Update History</h4>
</div>
<div class="modal-body">
@if(count($updateHistory) == 0)
    No update history.
@else
    @foreach($updateHistory as $k => $v)
        @foreach($v as $k2 => $v2)
            @if($k2 != 'updated_by' && $k2 != 'updated_at')
                    <div><span style="margin-right: 25px">{{array_get($v,'updated_by')}} updated the {{$k2}} to {{$v2}}</span><span>{{array_get($v,'updated_at')}}</span></div>
            @endif
        @endforeach
        <div>------</div>
    @endforeach
@endif
</div>