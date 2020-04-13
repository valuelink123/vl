@extends('layouts.layout')
@section('label', 'Add Activity')
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-microphone font-green"></i>
                        <span class="caption-subject bold font-green">Add Activity</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <form role="form" action="{{ url('/crm/trackLogStore') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="text" name="record_id" id="record_id" value="{{$record_id}}" hidden>
                        <input type="text" name="email" id="email" value="{{$email}}" hidden>
                        <div class="row form-group">
                            <div class="col-lg-6 col-md-6">
                                <label>Channel</label>
                                <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                    <select class="form-control" name="channel" id="channel">
                                        @foreach (getTrackLogChannel() as $key => $value)
                                            <option value="{{$key}}" >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{--<div class="col-lg-6 col-md-6">--}}
                                {{--<label>Subject Type</label>--}}
                                {{--<div class="input-group ">--}}
                                {{--<span class="input-group-addon">--}}
                                        {{--<i class="fa fa-bookmark"></i>--}}
                                {{--</span>--}}
                                    {{--<select class="form-control" name="subject_type" id="subject_type">--}}
                                        {{--@foreach ($subject_type as $key => $value)--}}
                                            {{--<option value="{{$key}}" >{{$value}}</option>--}}
                                        {{--@endforeach--}}
                                    {{--</select>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div style="clear:both;"></div>--}}
                        </div>

                        {{--<div class="form-group">--}}
                            {{--<label>Email</label>--}}
                            {{--<div class="input-group ">--}}
                                {{--<span class="input-group-addon">--}}
                                    {{--<i class="fa fa-bookmark"></i>--}}
                                {{--</span>--}}
                                {{--<input type="email" class="form-control" name="email" id="email" value="" required>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        <div class="form-group">
                            <label>Question Type</label>
                            <div class="form-inline">
                                <select id="linkage1" name="linkage1" class="form-control city-select" data-selected="" data-parent_id="28"></select>
                                <select id="linkage2" name="linkage2" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                <select id="linkage3" name="linkage3" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                <select id="linkage4" name="linkage4" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                <select id="linkage5" name="linkage5" class="form-control city-select" data-selected="" data-parent_id=""></select>
                            </div>
                        </div>
                        <script>
                            var city=[],cityName=[];
                            $.fn.city = function (opt) {
                                var $id = $(this),
                                    options = $.extend({
                                        url:"{{ url('inbox/getCategoryJson?parent_id=') }}",
                                        /*当前ID，设置选中状态*/
                                        selected: null,
                                        /*上级栏目ID*/
                                        parent_id: null,
                                        /*主键ID名称*/
                                        valueName: "id",
                                        /*名称*/
                                        textName: "category_name",
                                        /*默认名称*/
                                        defaultName: "None",
                                        /*下级对象ID*/
                                        nextID: null}, opt),selected,_tmp;
                                if(options.parent_id==null){
                                    _tmp=$id.data('parent_id');
                                    if(_tmp!==undefined){
                                        options.parent_id=_tmp;
                                    }
                                }
                                //初始化层
                                this.init = function () {
                                    if($.inArray($id.attr('id'),cityName)==-1){
                                        cityName.push($id.attr('id'));
                                    }
                                    if(!options.selected){
                                        options.selected=$id.data('selected');
                                    }
                                    $id.append(format(get(options.parent_id)));
                                };
                                function get(id) {
                                    if (id !== null && !city[id]) {
                                        getData(id);
                                        return city[id];
                                    }else if (id !== null && city[id]) {
                                        return city[id];
                                    }
                                    return [];
                                }

                                function getData(id) {
                                    $.ajax({
                                        url: options.url+ id,
                                        type: 'GET',
                                        async: false,
                                        dataType:'json',
                                        success: function (d) {
                                            if (d.status == 'y') {
                                                city[id] = d.data;
                                            }
                                        }
                                    });
                                }

                                function format(d) {
                                    var _arr = [], r, selected = '';
                                    if (options.defaultName !== null) _arr.push('<option value="999999999">' + options.defaultName + '</option>');
                                    if ($.isArray(d)) for (var v in d) {
                                        r = null;
                                        r = d[v];
                                        selected = '';
                                        if (options.selected && options.selected == (r[options.valueName])) {
                                            selected = 'selected';
                                        }
                                        _arr.push('<option value="' + r[options.valueName] + '" ' + selected + '>' + r[options.textName] + '</option>');
                                    }
                                    return _arr.join('');
                                }

                                this.each(function () {
                                    options.nextID && $id.on('change', function () {
                                        var $this = $('#' + options.nextID),id=$(this).attr('id'),i=$.inArray(id,cityName);
                                        $this.html(format(get($(this).val())));
                                        if ($.isArray(cityName)) for (var v in cityName) {
                                            if(v>(i+1)){
                                                $('#'+cityName[v]).html(format());
                                            }
                                        }
                                    });
                                });
                                this.init();
                            };
                            $(function() {

                                $('#linkage1').city({nextID:'linkage2'});
                                $('#linkage2').city({nextID:'linkage3'});
                                $('#linkage3').city({nextID:'linkage4'});
                                $('#linkage4').city({nextID:'linkage5'});
                                $('#linkage5').city();
                            });

                        </script>

                        <div class="form-group">
                            <label>Note</label>
                            @include('UEditor::head')
                            <!-- 加载编辑器的容器 -->
                            <script id="container" name="note" type="text/plain" required></script>
                            <!-- 实例化编辑器 -->
                            <script type="text/javascript">
                                var ue = UE.getEditor('container');
                                ue.ready(function() {
                                    ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                });
                            </script>
                            <div style="clear:both;"></div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" class="btn blue">Submit</button>
                                    <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
