@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['finance dashboard']])
@endsection

@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;">
            <form id="search-form" >
                <div class="search portlet light">
{{--                    <div class="col-md-2">--}}
{{--                        <div class="input-group">--}}
{{--                            <span class="input-group-addon">Site</span>--}}
{{--                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">--}}
{{--                                @foreach($site as $value)--}}
{{--                                    <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="col-md-2">
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select style="width:100%;" class="btn btn-default" id="account" data-width="100%" name="account">
                            @foreach($account as $ka=>$va)
                                    <option value="{{$ka}}" @if($ka==$seller_account_id) selected  @endif>{{$va}}</option>
                            @endforeach
                            </select>
{{--                            <select class="btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">--}}

{{--                            </select>--}}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">Date From</span>
                            <input type="text" id="date_from" class="form-control form-filter input-sm"  name="date_from" value="{{$date_from}}">
                            <span class="input-group-btn">
                                <button class="btn btn-sm default" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">Date To</span>
                            <input type="text" id="date_to" class="form-control form-filter input-sm" name="date_to" value="{{$date_to}}">
                            <span class="input-group-btn">
                                <button class="btn btn-sm default" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_top" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

{{--            显示5个参数的值--}}
            <div class="col-md-12 portlet light">
               <div>
                   <span style="font-size: 28px;">Shipped Orders Data</span>
               </div>


                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">
                            <div class="number">
                                <small>other</small>
                                <h4 class="font-green-sharp">
                                    <span data-counter="counterup">{{$shipped_fees['other']}}</span>
                                    <small class="font-green-sharp"></small>
                                </h4>

                            </div>

                        </div>
                        <div class="progress-info">
                            <div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">

								</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">
                            <div class="number">
                                <small>income</small>
                                <h4 class="font-red-haze">
                                    <span data-counter="counterup">{{$shipped_fees['income']}}</span>
                                    <small class="font-red-haze"></small>
                                </h4>

                            </div>

                        </div>
                        <div class="progress-info">
                            <div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success red-haze">

								</span>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">

                            <div class="number">
                                <small>commission</small>
                                <h4 class="font-purple-soft">
                                    <span data-counter="counterup">{{$shipped_fees['commission']}}</span>
                                    <small class="font-purple-soft"></small>
                                </h4>

                            </div>

                        </div>
                        <div class="progress-info">
                            <div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success purple-soft">

								</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">
                            <div class="number">
                                <small>shipping</small>
                                <h4 class="font-blue-sharp">
                                    <span data-counter="counterup">{{$shipped_fees['shipping']}}</span>
                                    <small class="font-green-sharp"></small>
                                </h4>

                            </div>

                        </div>
                        <div class="progress-info">
                            <div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">

								</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">
                            <div class="number">
                                <small>promotion</small>
                                <h4 class="font-green-sharp">
									<span data-counter="counterup">
									{{$shipped_fees['shipping']}}</span>
                                    <small class="font-purple-soft"></small>
                                </h4>

                            </div>
                        </div>
                        <div class="progress-info">
                            <div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">

								</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                <span style="font-size: 28px;">Settlement Data</span>
                </div>
                @foreach($settlement_fees['total'] as $key=> $value)

                <div class="col-md-1" style="width:11%;">
                    <div class="dashboard-stat2 ">
                        <div class="display">
                            <div class="number">
                                <small>{{$key}}</small>
                                <h4 class="font-green-sharp" style='color:{{$total_color[$key]}} !important'>
                                    <span data-counter="counterup">{{$value}}</span>
                                    <small class="font-green-sharp"></small>
                                </h4>

                            </div>

                        </div>
                        <div class="progress-info">
                            <div class="progress">

								<span style='width: 100%;background-color:{{$total_color[$key]}}' class="progress-bar">

								</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>Transaction Type</th>
                        @foreach($table_th as $th_key=>$th_value)
                            <th>{{$th_value}}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($settlement_fees as $key=> $value)
                        @if($key!='total')
                            <tr>
                                <td>{{$key}}</td>
                                @foreach($table_th as $th_key=>$th_value)
                                    <td>@if($value[$th_key]==0) 0.00 @else  {{$value[$th_key]}}@endif</td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>



    <script>
        //日期控件初始化
        $('.date-picker').datepicker({
            rtl: App.isRTL(),
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            autoclose: true,
        });


        // function getAccountBySite(){
        //     var marketplaceid = $('#site option:selected').val();
        //     $.ajax({
        //         type: 'post',
        //         url: '/showAccountBySite',
        //         data: {marketplaceid:marketplaceid},
        //         dataType:'json',
        //         success: function(res) {
        //             if(res.status==1){
        //
        //                 var html = '';
        //                 $.each(res.data,function(i,item) {
        //                     html += '<option value="'+item.id+'">'+item.label+'</option>';
        //                 })
        //                 var str = '<span class="input-group-addon">Account</span>\n' +
        //                     '\t\t\t\t\t\t\t<select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">\n' +
        //                     '\n' +html+
        //                     '\t\t\t\t\t\t\t</select>';
        //                 $('#account-div').html(str);
        //                 ComponentsBootstrapMultiselect.init();//处理account的多选显示样式
        //             }else{
        //                 alert('请先选择站点');
        //             }
        //         }
        //     });
        //
        // }
        //
        // $(function(){
        //     getAccountBySite()//触发当前选的站点得到该站点所有的账号
        // })
    </script>

@endsection