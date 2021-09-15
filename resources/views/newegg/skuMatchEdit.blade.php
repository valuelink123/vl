@extends('layouts.layout')
@section('content')

<form action="{{ url('/neweggOrderList/skuMatchUpdate') }}" id="skuMatch_form" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="sku_id" value="{{$data['id']}}">
    <div class="row">
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
                    <div class="portlet-body">
                        <div class="tabbable-line">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>平台SKU</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="text" class="form-control" name="newegg_sku" id="newegg_sku"
                                               value="{{$data['newegg_sku']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>平台SKU的单位数量</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="number" class="form-control" name="s_qty" id="s_qty" value="{{$data['s_qty']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>SAP SKU</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="text" class="form-control int_or_two_digits_input" name="sap_sku" id="sap_sku"
                                               value="{{$data['sap_sku']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>SAP SKU的数量</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="number" class="form-control int_or_two_digits_input" name="t_qty" id="t_qty"
                                               value="{{$data['t_qty']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>仓库</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="text" class="form-control" name="warehouse" id="warehouse"
                                               value="{{$data['warehouse']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>工厂</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="text" class="form-control" name="factory" id="factory"
                                               value="{{$data['factory']}}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>实际运输方式</label>
                                    <div class="input-group ">
                                        <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                        <input type="text" class="form-control" name="shipment_code" id="shipment_code"
                                               value="{{$data['shipment_code']}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                    <div class="form-actions">
                        <div class="row">
                            <div align="center">
                                <button align="center" type="submit" class="btn blue" align="center">Submit</button>
                            </div>
                        </div>
                    </div>
                    <div style="height:10px;"></div>
                </div>
            </div>
        </div>
</form>
<div style="clear:both;"></div>
@include('frank.common')
<script type="text/javascript">


</script>

@endsection

