@extends('layouts.layout')
@section('label', 'Create A New Seller Accounts Status Record')
@section('content')
    <style>
        table th{
            text-align:center;
        }
    </style>
    <form  action="/ccp/adMatchAsin/add" id="form" novalidate method="POST">
        {{ csrf_field() }}
        <div class="col-lg-9">
            <div class="col-md-12">
                <div class="portlet light portlet-fit bordered ">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-microphone font-green"></i>
                            <span class="caption-subject bold font-green">Create A Ad Match Asin</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="tabbable-line">
                            <div class="">
                                <div class="col-lg-8">
                                    <input type="hidden" class="form-control" name="campaign_id" id="campaign_id" value="{{$data['campaign_id']}}">
                                    <input type="hidden" class="form-control" name="marketplace_id" id="marketplace_id" value="{{$data['marketplace_id']}}">
                                    <input type="hidden" class="form-control" name="seller_id" id="seller_id" value="{{$data['seller_id']}}">
                                    <div class="form-group">
                                        <label>Site</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="domain" id="domain" value="{{$data['domain']}}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group" id="account-div">
                                        <label>Account</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="account_name" id="account_name" value="{{$data['account_name']}}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Campaign</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="campaign_name" id="campaign_name" value="{{$data['campaign_name']}}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Ad Group</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="group_name" id="group_name" value="{{$data['group_name']}}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Ad Type</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="ad_type" id="ad_type" value="{{$data['ad_type']}}" readonly>
                                        </div>
                                    </div>

{{--                                    有或者没有$params的公共部分--}}
                                    <div class="form-group">
                                        <label>Asin</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <input type="text" class="form-control" name="asin" id="asin" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Sku</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <select  style="width:100%;height:35px;"  id="sku" name="sku">

                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Seller</label>
                                        <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                            <select  style="width:100%;height:35px;"  id="sap_seller_id" name="sap_seller_id">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @include('frank.common')
    <script>
        $("#asin").blur(function(){
            var marketplace_id = $('#marketplace_id').val();
            var seller_id = $('#seller_id').val();
            var asin = $('#asin').val();
            $.ajax({
                type: 'post',
                url: '/ccp/asinMatchSkuDataByAsin',
                data: {marketplace_id:marketplace_id,seller_id:seller_id,asin:asin},
                dataType:'json',
                success: function(res) {
                    if(res.sku){
                        var html = '';
                        $.each(res.sku,function(i,item) {
                            html += '<option value="'+item+'">'+item+'</option>';
                        })
                        $('#sku').html(html);
                    }
                    if(res.sellers){
                        var html = '';
                        $.each(res.sellers,function(i,item) {
                            html += '<option value="'+i+'">'+item+'</option>';
                        })
                        $('#sap_seller_id').html(html);
                    }
                }
            });
        });

        // var action_type = $('#action_type').val();
        // action_type = action_type.replace(/(^\s*)|(\s*$)/g, "");
        // $(function(){
        //     if(action_type=='add'){
        //         getAccountBySite();
        //     }
        // })
        // $("#site").change(function(){
        //     getAccountBySite();
        // });
        // function getAccountBySite(){
        //     getRadioAccountBySelectedSite();
        //     $("#account").trigger("change");
        // }
        // $("#account").change(function(){
        //     getRadioCampaignBySelectedAccount();
        //     $("#campaign").trigger("change");
        // });
        //
        // $("#campaign").change(function(){
        //     getRadioGroupBySelectedCampaign();
        //     getDataBySelectedCampaign();
        // });

        $("#form").submit(function(){
            var asin = $('#asin').val();
            var sku = $('#sku').val();
            var sap_seller_id = $('#sap_seller_id').val();
            if(asin==''){
                alert('asin为必填!')
                return false;
            }
            if(sku==''){
                alert('sku为必填!')
                return false;
            }
            if(sap_seller_id==''){
                alert('seller为必填!')
                return false;
            }
        });
    </script>
@endsection