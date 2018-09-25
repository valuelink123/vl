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

                    <div class="form-group">
                        <label>
                            Video Type
                            <select required autocomplete="off" class="form-control" name="type">
                                @foreach($types as $type)
                                    <option value="{!! $type !!}">{!! $type !!}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <br/>

                    <button type="submit" class="btn btn-primary">Submit</button>

                </div>

                <div class="col-lg-4">

                    <div class="form-group">
                        <label>
                            Video Description
                            <input required autocomplete="off" class="form-control" placeholder="Description" name="descr"/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Video Link
                            <input required autocomplete="off" class="form-control" placeholder="Link" name="link"/>
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
            <form>
                <div class="form-group">
                    <label>
                        Upload File
                        <input required autocomplete="off" type="file" style="margin-top: 5px;"/>
                    </label>
                    <p class="help-block">Only support CSV format, can not exceed 5M. <a href="#">template.xls</a></p>
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

        $(item_model).change(() => {
            if (item_group.value && $(`#list-${item_group.value} option[value="${item_model.value}"]`).length) return
            item_model.value = ''
        })
    </script>

@endsection