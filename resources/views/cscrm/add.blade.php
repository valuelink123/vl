@extends('layouts.layout')
@section('label', 'CRM--CS Team')
@section('content')
    <style>
        .ui-autocomplete {
            max-height: 300px;
            z-index:9999;
            overflow-y: auto;
            /* 防止水平滚动条 */
            overflow-x: hidden;
        }
        .styleclass{
            margin-top:-34px;
            margin-left:100px;
        }
    </style>
    <script>
        $(function() {

            $("#rebindorder").click(function(){
                $.post("/saporder/get",
                    {
                        "_token":"{{csrf_token()}}",
                        "inboxid":0,
                        // "sellerid":$("#rebindordersellerid").val(),
                        "orderid":$("#amazon_order_id").val()
                    },
                    function(data,status){
                        if(status=='success'){
                            var redata = JSON.parse(data);
                            if(redata.result==1){
                                toastr.success(redata.message);
                                // if(redata.sellerid) $("select[name='rebindordersellerid']").val(redata.sellerid);
                                if(redata.buyeremail){
                                    $("input[name='email']").val(redata.buyeremail);
                                    $('.createEmail').attr('href','/send/create?to_address='+redata.buyeremail);
                                }

                                // if(redata.orderhtml) $("#tab_2").html(redata.orderhtml);
                                if(redata.productBasicInfo){
                                    $("#tab_1 input[name='sku']").val(redata.productBasicInfo.SellerSKU);
                                    $("#tab_1 input[name='asin']").val(redata.productBasicInfo.asin);
                                    $("#tab_1 input[name='item_no']").val(redata.productBasicInfo.item_no);
                                    rItemGroup();
                                }
                            }else{
                                toastr.error(redata.message);
                            }
                        }

                    });
            });


        });
    </script>


    <div class="row">
        <div class="col-md-12">
            <div class="portlet light portlet-fit bordered">

                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-microphone font-green"></i>
                        <span class="caption-subject bold font-green"> CRM--CS Team</span>
                    </div>

                </div>
                <div class="portlet-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <form id="phone_form" action="{{ url('/cscrm/update') }}" method="POST" >
                        <div class="">
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_1">
{{--                                    左边--}}
                                    <div class="col-md-6">
                                        {{ csrf_field() }}

                                        <div class="form-group">
                                            <label>Amazon Order ID</label>
                                            <div class="row" style="margin-bottom:50px;">
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <input id="amazon_order_id" class="form-control" type="text" name="amazon_order_id" placeholder="Amazon Order ID">
                                                        <span class="input-group-btn">
                                                            <button id="rebindorder" class="btn btn-success" type="button">
                                                                Get Order</button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Question Type</label>
                                            <div class="form-inline">
                                                <select id="linkage1" name="linkage1" class="form-control city-select" data-selected="" data-parent_id="28"></select>
                                                <select id="linkage2" name="linkage2" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                                <select id="linkage3" name="linkage3" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                                <select style="display:none;" id="linkage4" name="linkage4" class="form-control city-select" data-selected="" data-parent_id=""></select>
                                                <select style="display:none;" id="linkage5" name="linkage5" class="form-control city-select" data-selected="" data-parent_id=""></select>
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

                                                var sku = $('#sku').val();

                                                $.ajax({
                                                    url: "{{ url('inbox/getItem') }}",
                                                    method: 'POST',
                                                    cache: false,
                                                    dataType:'json',
                                                    data: {sku: sku},
                                                    success: function (data) {
                                                        if(data.code == 200){
                                                            $('#item_no').val(data.data[0].item_no);
                                                            $('#item_group').val(data.data[0].item_group);
                                                        }
                                                    }
                                                });
                                            });

                                            function rItemGroup(){
                                                var item_no = $('#item_no').val();

                                                $.ajax({
                                                    url: "{{ url('inbox/getItemGroup') }}",
                                                    method: 'POST',
                                                    cache: false,
                                                    dataType:'json',
                                                    data: {item_no: item_no},
                                                    success: function (data) {
                                                        if(data.code == 200){
                                                            $('#item_group').val(data.data[0].item_group);
                                                        }else{
                                                            $('#item_group').val('');
                                                        }
                                                    }
                                                });
                                            }
                                        </script>

                                        <div class="form-group col-xs-6">
                                            <label>Set Mark</label>
                                            <div class="input-group">
                                                <select class="form-control" name="status">
                                                    <option value="">Select...</option>
                                                    @foreach (getMarks() as $mark)
                                                        <option value="{{$mark}}">{{$mark}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>From</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <select class="form-control" name="from">
                                                    @foreach (getFrom() as $value)
                                                        <option value="{{$value}}">{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Buyer Email</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" name="email" id="email"  >
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Name</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" name="name" value="">
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>

{{--                                    右边--}}
                                    <div class="col-md-6">

                                        <div class="form-group col-xs-6">
                                            <label>Gender</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <select class="form-control" name="gender">
                                                    @foreach (getGender() as $value)
                                                        <option value="{{$value}}">{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Customer's FB Name</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="facebook_name" value="" >
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Country</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <select class="form-control" name="country">
                                                    <option value="">Select</option>
                                                    @foreach (getCountry() as $value)
                                                        <option value="{{$value}}">{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Phone number</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="phone" value="" >
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Review</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <select class="form-control" name="review">
                                                    @foreach (getReview() as $value)
                                                        <option value="{{$value}}">{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>Brand</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <select class="form-control" name="brand">
                                                    <option value="">Select</option>
                                                    @foreach (getBrand() as $value)
                                                        <option value="{{$value}}">{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-6">
                                            <label>ASIN</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="asin" id="asin" value="" >
                                            </div>
                                        </div>

                                        <div class="form-group col-xs-6">
                                            <label>SKU</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="sku" id="sku" value="" >
                                            </div>
                                        </div>

                                        <div class="form-group col-xs-6">
                                            <label>Item NO.</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" onchange="rItemGroup();" name="item_no" id="item_no" value="">
                                            </div>
                                        </div>

                                        <div class="form-group col-xs-6">
                                            <label>Item Group</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="item_group" id="item_group" value="" >
                                            </div>
                                        </div>




                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions" style="margin-top:50px;">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" id="other_form" class="btn blue btn1">Submit</button>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div></div>
    <script>
        $(function() {
            // TableDatatablesAjax.init();
            $('#other_form').click(function(){
                var linkage1 = $('#linkage1').val();
                var linkage2 = $('#linkage2').val();
                var linkage3 = $('#linkage3').val();
                console.log(linkage1+'__'+linkage2+'__'+linkage3);
                if(linkage1!='999999999'){
                    //当Question Type选的是质量问题的时候，问题类型的2级和3级都要选才可以提交
                    if(linkage1==114){
                        if(linkage2==999999999){
                            alert('Please select a secondary category');
                            return false;
                        }
                    }
                }else{
                    alert('Please select question type');
                    return false;
                }
            });

            $('#phone_form #other_form').click(function(){
                var email = $('#email').val();
                if(email==''){
                    alert('Please enter email');
                    return false;
                }
            })
        });


    </script>
@endsection