@extends('layouts.layout')
@section('label', 'Crm Add')
@section('content')
    <style>
        .mt-repeater-add-son{
            margin-top:25px;
            margin-left:18px;
            font-size:12px;
            width:105px;
        }
        .mt-repeater-delete-son{
            margin-top:25px;
        }
    </style>
    <form  action="{{ url('/crm/update') }}" id="exception_form" novalidate method="POST">
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
                            <span class="caption-subject bold font-green">Crm Add</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="tabbable-line">
                            <div class="">

                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <label>ID</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="id" id="id" value="{{$id}}" required >
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Name</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="name" id="name" value="" required >
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Country</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <select class="form-control" name="country" id="country">
                                                    @foreach (getCrmCountry() as $value)
                                                        <option value="{{$value}}" >{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <select class="form-control" name="brand" id="brand">
                                                    @foreach (getCrmBrand() as $value)
                                                        <option value="{{$value}}" >{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>From</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <select class="form-control" name="from" id="from">
                                                    @foreach (getCrmFrom() as $value)
                                                        <option value="{{$value}}" >{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{--新添加的Customer's FB Name和FB Group--}}
                                        <div class="form-group">
                                            <label>
                                                Customer's FB Name
                                                <input autocomplete="off" class="xform-autotrim form-control" placeholder="Facebook Name" name="facebook_name"/>
                                            </label>
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                FB Group
                                                <input id="facebook_group" class="form-control xform-autotrim" name="facebook_group" list="list-facebook_group" placeholder="Facebook Group" autocomplete="off" />
                                                <datalist id="list-facebook_group">
                                                    @foreach(getFacebookGroup() as $id=>$name)
                                                        <option value="{!! $id !!} | {!! $name !!}"></option>
                                                    @endforeach
                                                </datalist>

                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <select class="form-control" name="type" id="type">
                                                    @foreach (getCrmClientType() as $value)
                                                        <option value="{{$value}}" >
                                                        @if($value =='0')
                                                            default
                                                        @else
                                                            blacklist
                                                        @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group mt-repeater frank">
                                            <div data-repeater-list="group-data" id="replacement-product-list">

                                                    <div data-repeater-item class="mt-repeater-item">
                                                        <div class="row mt-repeater-row">
                                                            <div class="col-lg-4 col-md-4">
                                                                <label class="control-label">Email</label>
                                                                <input type="text" class="form-control item_code" name="email" placeholder="Email" autocomplete="off" value="" required />
                                                            </div>
                                                            <div class="col-lg-2 col-md-2">
                                                                <label class="control-label">Phone</label>
                                                                <input type="text" class="form-control seller-sku-selector" name="phone" placeholder="Phone" autocomplete="off" value=""/>
                                                            </div>
                                                            <div class="col-lg-2 col-md-2">
                                                                <label class="control-label">Remark</label>
                                                                <input type="text" class="form-control seller-sku-selector" name="remark" placeholder="Remark" autocomplete="off" value=""/>
                                                            </div>
                                                            <div class="col-lg-1 col-md-1">
                                                                <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                                    <i class="fa fa-close"></i>
                                                                </a>
                                                            </div>
                                                            <div style="clear:both;"></div>
                                                            <div class = "inner-repeater">
                                                                <div data-repeater-list = "order-list">
                                                                    <div data-repeater-item >
                                                                        <div class="form-group">
                                                                            <div class="col-lg-3">
                                                                                <label class="control-label">Amazon Order Id</label>
                                                                            <input type="text" class="form-control"  name="amazon_order_id" pattern="\d{3}-\d{7}-\d{7}" value="" placeholder="Amazon_Order_Id"/>
                                                                            </div>
                                                                            <div class="col-lg-2">
                                                                            <label class="control-label">Order Type</label>
                                                                            <select class="form-control"  name="order_type" id="order_type">
                                                                                @foreach (getCrmOrderType() as $key=>$value)
                                                                                    <option value="{{$key}}" >{{$value}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                <label class="control-label">Amazon Profile Page</label>
                                                                                <input type="text" class="form-control"  name="amazon_profile_page" placeholder="Amazon Profile Page" value="" />
                                                                            </div>
                                                                        </div>
                                                                        <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete-son">
                                                                            <i class="fa fa-close"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add-son">
                                                                    <i class="fa fa-plus"></i> Add Order Id</a>
                                                            </div>
                                                        </div>
                                                    </div>

                                            </div>
                                            <a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
                                                <i class="fa fa-plus"></i> Add Product</a>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
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
    </form>
    @include('frank.common')
    <script>
        $(exception_form).submit(function (e) {

            //let type = $('#type').val()

            for (let input of $(this).find('[name]')) {

                let tabID = $(input).closest('.tab-pane').attr('id')

                if (tabID) {
                    //if (!(type & tabID.substr(-1))) continue
                    if (!(tabID.substr(-1))) continue
                }
                if (!input.reportValidity()) {
                    toastr.error('The form is not complete yet.')
                    return false
                }
            }
        });
        let $replacementProductList = $('#replacement-product-list')
        let replacementItemRepeater = $replacementProductList.parent().repeater({repeaters: [{selector: '.inner-repeater'}],defaultValues:{qty:1}})
    </script>
    <div style="clear:both;"></div>
@endsection