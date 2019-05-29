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
                                                <input type="text" class="form-control" name="country" id="country" value="" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="brand" id="brand" value="" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>From</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="from" id="from" value="" required>
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
                                                            <div class="col-lg-1 col-md-1">
                                                                <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                                    <i class="fa fa-close"></i>
                                                                </a>
                                                            </div>
                                                            <div style="clear:both;"></div>
                                                            <div class = "inner-repeater">
                                                                <div data-repeater-list = "order-list">

                                                                        <div data-repeater-item>
                                                                            <div class="col-lg-2 col-md-2">
                                                                                <label class="control-label">Amazon_Order_Id</label>
                                                                                <input type="hidden" class="form-control"  name="cid" value=""/>
                                                                                <input type="text" class="form-control"  name="amazon_order_id" value="" placeholder="Amazon_Order_Id"/>
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

            let type = $('#type').val()

            for (let input of $(this).find('[name]')) {

                let tabID = $(input).closest('.tab-pane').attr('id')

                if (tabID) {
                    if (!(type & tabID.substr(-1))) continue
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