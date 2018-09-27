@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    <style>
        .form-group label:only-child {
            width: 100%;
            margin-bottom: 0;
        }

        .form-group label .form-control {
            margin-top: 5px;
        }
    </style>

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Single Import
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form method="post" class="row">
                <div class="col-lg-4">

                    <div class="form-group">
                        <label>
                            Item Group
                            <input required autocomplete="off" class="form-control" placeholder="Item Group" name="item_group" list="list-item-group" id="item_group"/>
                            <datalist id="list-item-group">
                                @foreach($itemGroups as $itemGroup)
                                    <option value="{!! $itemGroup !!}"/>
                                @endforeach
                            </datalist>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Brand
                            <input required autocomplete="off" class="form-control" placeholder="Brand" name="brand" list="list-brand" id="brand"/>
                            <datalist id="list-brand">
                                @foreach($brands as $brand)
                                    <option value="{!! $brand !!}"/>
                                @endforeach
                            </datalist>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Item Model
                            <input required autocomplete="off" class="form-control" placeholder="Item Model" name="item_model" list="" id="item_model"/>
                            @foreach($itemGroupModels as $itemGroupModel)
                                <datalist id="list-{!! $itemGroupModel['item_group'] !!}">
                                    @foreach(explode(',', $itemGroupModel['item_models']) as $item_model)
                                        <option value="{!! $item_model !!}"/>
                                    @endforeach
                                </datalist>
                            @endforeach
                        </label>
                    </div>

                    <br/>

                    <button type="submit" class="btn btn-primary">Submit</button>

                </div>

                <div class="col-lg-4">

                    <div class="form-group">
                        <label>
                            Manual Link
                            <input required pattern=".*\S+.*" autocomplete="off" class="form-control" placeholder="Link" name="link" title="This field is required."/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Note
                            <input autocomplete="off" class="form-control" placeholder="Note" name="note"/>
                        </label>
                    </div>

                </div>

            </form>
        </div>
    </div>


    <h1 class="page-title font-red-intense"> Batch Import
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>
                        Import by Excel Format
                        <input required autocomplete="off" type="file" style="margin-top: 5px;" accept=".xls, .xlsx" name="excelfile"/>
                    </label>
                    <p class="help-block">Fill in the Excel <a href="/kms/user_manual_import.xlsx">template.xlsx</a> and upload it here.</p>
                </div>
                <br/>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>
        $(item_group).change(() => {

            item_model.value = ''
            item_model.setAttribute('list', '')

            if ($(`#list-item-group option[value="${item_group.value}"]`).length > 0) {
                item_model.setAttribute('list', `list-${item_group.value}`)
            } else {
                item_group.value = ''
            }
        })

        $(brand).change(() => {
            if ($(`#list-brand option[value="${brand.value}"]`).length) return
            brand.value = ''
        })

        $(item_model).change(() => {
            if (item_group.value && $(`#list-${item_group.value} option[value="${item_model.value}"]`).length) return
            item_model.value = ''
        })
    </script>

@endsection